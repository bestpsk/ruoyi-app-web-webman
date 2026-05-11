<?php

namespace app\service;

use app\model\BizAttendanceRecord;
use app\model\BizAttendanceRule;
use app\model\SysUser;

class BizAttendanceRecordService
{
    public function selectRecordList($params = [])
    {
        $query = BizAttendanceRecord::query();

        if (!empty($params['user_id'])) {
            $query->where('user_id', $params['user_id']);
        }
        if (!empty($params['user_name'])) {
            $query->where('user_name', 'like', '%' . $params['user_name'] . '%');
        }
        if (!empty($params['attendance_date'])) {
            $query->where('attendance_date', $params['attendance_date']);
        }
        if (!empty($params['start_date']) && !empty($params['end_date'])) {
            $query->whereBetween('attendance_date', [$params['start_date'], $params['end_date']]);
        }
        if (isset($params['attendance_status']) && $params['attendance_status'] !== '') {
            $query->where('attendance_status', $params['attendance_status']);
        }

        $pageNum = intval($params['pageNum'] ?? 1);
        $pageSize = intval($params['pageSize'] ?? 10);
        return $query->orderBy('attendance_date', 'desc')->orderBy('record_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectRecordById($recordId)
    {
        return BizAttendanceRecord::find($recordId);
    }

    public function getTodayRecord($userId)
    {
        $today = date('Y-m-d');
        return BizAttendanceRecord::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();
    }

    public function clock($data)
    {
        $userId = $data['user_id'];
        $userName = $data['user_name'] ?? '';
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $clockType = $data['clock_type'] ?? '0';
        $outsideReason = $data['outside_reason'] ?? '';

        if ($clockType === '1' && empty($outsideReason)) {
            return ['error' => '外勤打卡请填写外勤事由'];
        }

        $configService = new BizAttendanceConfigService();
        $rule = $configService->getUserRule($userId);

        if ($clockType === '0' && $rule) {
            if ($rule->work_latitude && $rule->work_longitude && !empty($data['latitude']) && !empty($data['longitude'])) {
                $distance = $this->calculateDistance(
                    $data['latitude'], $data['longitude'],
                    $rule->work_latitude, $rule->work_longitude
                );
                \support\Log::info('考勤距离校验(clock)', [
                    'user_id' => $userId,
                    'user_location' => [$data['latitude'], $data['longitude']],
                    'rule_location' => [$rule->work_latitude, $rule->work_longitude],
                    'distance' => $distance,
                    'allowed_distance' => $rule->allowed_distance,
                    'is_in_range' => $distance <= $rule->allowed_distance
                ]);
                if ($distance > $rule->allowed_distance) {
                    return ['error' => '不在考勤范围内，距离考勤点' . intval($distance) . '米'];
                }
            }
        } else if ($clockType === '1') {
            \support\Log::info('外勤打卡-跳过距离校验', [
                'user_id' => $userId,
                'clockType' => $clockType
            ]);
        }

        $record = BizAttendanceRecord::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if (!$record) {
            $record = BizAttendanceRecord::create([
                'user_id' => $userId,
                'user_name' => $userName,
                'attendance_date' => $today,
                'clock_count' => 0,
                'attendance_status' => '0',
                'create_by' => $userName,
                'create_time' => $now,
            ]);
        }

        $clockData = [
            'record_id' => $record->record_id,
            'user_id' => $userId,
            'user_name' => $userName,
            'clock_time' => $now,
            'clock_type' => $this->determineClockType($record),
            'work_type' => $clockType,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'address' => $data['address'] ?? '',
            'photo' => $data['photo'] ?? '',
            'outside_reason' => $outsideReason,
        ];

        \app\model\BizAttendanceClock::create($clockData);

        $this->updateRecordSummary($record);

        return BizAttendanceRecord::find($record->record_id);
    }

    private function determineClockType($record)
    {
        return $record->clock_count == 0 ? '0' : '1';
    }

    private function updateRecordSummary($record)
    {
        $clockCount = \app\model\BizAttendanceClock::where('record_id', $record->record_id)->count();
        $firstClock = \app\model\BizAttendanceClock::where('record_id', $record->record_id)
            ->orderBy('clock_time', 'asc')
            ->first();
        $lastClock = \app\model\BizAttendanceClock::where('record_id', $record->record_id)
            ->orderBy('clock_time', 'desc')
            ->first();

        $attendanceStatus = $this->calculateAttendanceStatus($firstClock, $lastClock);

        $record->update([
            'clock_count' => $clockCount,
            'first_clock_time' => $firstClock ? $firstClock->clock_time : null,
            'last_clock_time' => $lastClock ? $lastClock->clock_time : null,
            'clock_in_time' => $firstClock ? $firstClock->clock_time : null,
            'clock_out_time' => $lastClock ? $lastClock->clock_time : null,
            'clock_in_latitude' => $firstClock ? $firstClock->latitude : null,
            'clock_in_longitude' => $firstClock ? $firstClock->longitude : null,
            'clock_in_address' => $firstClock ? $firstClock->address : '',
            'clock_in_photo' => $firstClock ? $firstClock->photo : '',
            'clock_out_latitude' => $lastClock ? $lastClock->latitude : null,
            'clock_out_longitude' => $lastClock ? $lastClock->longitude : null,
            'clock_out_address' => $lastClock ? $lastClock->address : '',
            'clock_out_photo' => $lastClock ? $lastClock->photo : '',
            'attendance_status' => $attendanceStatus,
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    private function calculateAttendanceStatus($firstClock, $lastClock)
    {
        $ruleService = new BizAttendanceRuleService();
        $configService = new \app\service\BizAttendanceConfigService();
        $rule = $configService->getUserRule($userId);

        if (!$rule) {
            return '0';
        }

        $isLate = false;
        $isEarly = false;

        if ($firstClock) {
            $firstTime = date('H:i:s', strtotime($firstClock->clock_time));
            $workStartTime = $rule->work_start_time;
            $lateThreshold = $rule->late_threshold;
            $lateTime = date('H:i:s', strtotime("$workStartTime + $lateThreshold minutes"));
            if ($firstTime > $lateTime) {
                $isLate = true;
            }
        }

        if ($lastClock && $firstClock && $lastClock->clock_id !== $firstClock->clock_id) {
            $lastTime = date('H:i:s', strtotime($lastClock->clock_time));
            $workEndTime = $rule->work_end_time;
            $earlyThreshold = $rule->early_leave_threshold;
            $earlyTime = date('H:i:s', strtotime("$workEndTime - $earlyThreshold minutes"));
            if ($lastTime < $earlyTime) {
                $isEarly = true;
            }
        }

        if ($isLate && $isEarly) return '3';
        if ($isLate) return '1';
        if ($isEarly) return '2';
        return '0';
    }

    public function getTodayClockList($userId)
    {
        $today = date('Y-m-d');
        $record = BizAttendanceRecord::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if (!$record) {
            return [];
        }

        return \app\model\BizAttendanceClock::where('record_id', $record->record_id)
            ->orderBy('clock_time', 'asc')
            ->get();
    }

    public function clockIn($data)
    {
        $userId = $data['user_id'];
        $userName = $data['user_name'] ?? '';
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');
        $clockType = $data['clock_type'] ?? '0';
        $outsideReason = $data['outside_reason'] ?? '';

        if ($clockType === '1' && empty($outsideReason)) {
            return ['error' => '外勤打卡请填写外勤事由'];
        }

        $record = BizAttendanceRecord::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if ($record && $record->clock_in_time) {
            return ['error' => '已打过上班卡'];
        }

        $configService = new BizAttendanceConfigService();
        $rule = $configService->getUserRule($userId);

        $attendanceStatus = '0';

        if ($rule) {
            if ($clockType === '0') {
                if ($rule->work_latitude && $rule->work_longitude && !empty($data['latitude']) && !empty($data['longitude'])) {
                    $distance = $this->calculateDistance(
                        $data['latitude'], $data['longitude'],
                        $rule->work_latitude, $rule->work_longitude
                    );
                    \support\Log::info('考勤距离校验', [
                        'user_id' => $userId,
                        'user_location' => [$data['latitude'], $data['longitude']],
                        'rule_location' => [$rule->work_latitude, $rule->work_longitude],
                        'distance' => $distance,
                        'allowed_distance' => $rule->allowed_distance,
                        'is_in_range' => $distance <= $rule->allowed_distance
                    ]);
                    if ($distance > $rule->allowed_distance) {
                        return ['error' => '不在考勤范围内，距离考勤点' . intval($distance) . '米'];
                    }
                }
            } else {
                \support\Log::info('外勤打卡-跳过距离校验', [
                    'user_id' => $userId,
                    'clockType' => $clockType
                ]);
            }

            $currentTime = date('H:i:s');
            $workStartTime = $rule->work_start_time;
            $lateThreshold = $rule->late_threshold;
            $lateTime = date('H:i:s', strtotime("$workStartTime + $lateThreshold minutes"));
            if ($currentTime > $lateTime) {
                $attendanceStatus = '1';
            }
        }

        $recordData = [
            'user_id' => $userId,
            'user_name' => $userName,
            'attendance_date' => $today,
            'clock_in_time' => $now,
            'clock_in_latitude' => $data['latitude'] ?? null,
            'clock_in_longitude' => $data['longitude'] ?? null,
            'clock_in_address' => $data['address'] ?? '',
            'clock_in_photo' => $data['photo'] ?? '',
            'attendance_status' => $attendanceStatus,
            'clock_type' => $clockType,
            'outside_reason' => $outsideReason,
            'rule_id' => $rule ? $rule->rule_id : null,
            'create_by' => $userName,
            'create_time' => $now,
        ];

        if ($record) {
            $record->update([
                'clock_in_time' => $now,
                'clock_in_latitude' => $data['latitude'] ?? null,
                'clock_in_longitude' => $data['longitude'] ?? null,
                'clock_in_address' => $data['address'] ?? '',
                'clock_in_photo' => $data['photo'] ?? '',
                'attendance_status' => $attendanceStatus,
                'clock_type' => $clockType,
                'outside_reason' => $outsideReason,
                'rule_id' => $rule ? $rule->rule_id : null,
                'update_by' => $userName,
                'update_time' => $now,
            ]);
            return $record;
        }

        return BizAttendanceRecord::create($recordData);
    }

    public function clockOut($data)
    {
        $userId = $data['user_id'];
        $userName = $data['user_name'] ?? '';
        $now = date('Y-m-d H:i:s');
        $today = date('Y-m-d');

        $record = BizAttendanceRecord::where('user_id', $userId)
            ->where('attendance_date', $today)
            ->first();

        if (!$record || !$record->clock_in_time) {
            return ['error' => '请先打上班卡'];
        }

        if ($record->clock_out_time) {
            return ['error' => '已打过下班卡'];
        }

        $configService = new BizAttendanceConfigService();
        $rule = $configService->getUserRule($userId);

        if ($rule) {
            $clockType = $record->clock_type ?? '0';
            if ($clockType === '0') {
                if ($rule->work_latitude && $rule->work_longitude && !empty($data['latitude']) && !empty($data['longitude'])) {
                    $distance = $this->calculateDistance(
                        $data['latitude'], $data['longitude'],
                        $rule->work_latitude, $rule->work_longitude
                    );
                    \support\Log::info('考勤距离校验(clockOut)', [
                        'user_id' => $userId,
                        'user_location' => [$data['latitude'], $data['longitude']],
                        'rule_location' => [$rule->work_latitude, $rule->work_longitude],
                        'distance' => $distance,
                        'allowed_distance' => $rule->allowed_distance,
                        'is_in_range' => $distance <= $rule->allowed_distance
                    ]);
                    if ($distance > $rule->allowed_distance) {
                        return ['error' => '不在考勤范围内，距离考勤点' . intval($distance) . '米'];
                    }
                }
            }
        }

        $isEarly = false;

        if ($rule) {
            $currentTime = date('H:i:s');
            $workEndTime = $rule->work_end_time;
            $earlyThreshold = $rule->early_leave_threshold;
            $earlyTime = date('H:i:s', strtotime("$workEndTime - $earlyThreshold minutes"));
            if ($currentTime < $earlyTime) {
                $isEarly = true;
            }
        }

        $currentStatus = $record->attendance_status;
        if ($isEarly) {
            if ($currentStatus === '1') {
                $newStatus = '3';
            } else {
                $newStatus = '2';
            }
        } else {
            $newStatus = $currentStatus;
        }

        $record->update([
            'clock_out_time' => $now,
            'clock_out_latitude' => $data['latitude'] ?? null,
            'clock_out_longitude' => $data['longitude'] ?? null,
            'clock_out_address' => $data['address'] ?? '',
            'clock_out_photo' => $data['photo'] ?? '',
            'attendance_status' => $newStatus,
            'update_by' => $userName,
            'update_time' => $now,
        ]);

        return $record;
    }

    public function getMonthStats($userId, $month)
    {
        $startDate = $month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $records = BizAttendanceRecord::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $stats = [
            'normal' => 0,
            'late' => 0,
            'early' => 0,
            'late_and_early' => 0,
            'absent' => 0,
            'total' => $records->count()
        ];

        foreach ($records as $record) {
            switch ($record->attendance_status) {
                case '0': $stats['normal']++; break;
                case '1': $stats['late']++; break;
                case '2': $stats['early']++; break;
                case '3': $stats['late_and_early']++; break;
                case '4': $stats['absent']++; break;
            }
        }

        return $stats;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLon / 2) * sin($deltaLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
