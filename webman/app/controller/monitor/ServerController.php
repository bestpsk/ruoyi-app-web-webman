<?php

namespace app\controller\monitor;

use support\Request;
use app\common\AjaxResult;

class ServerController
{
    private static $startTime = null;

    public static function setStartTime($time)
    {
        self::$startTime = $time;
    }

    public function getInfo(Request $request)
    {
        return AjaxResult::success('', [
            'data' => [
                'cpu' => $this->getCpuInfo(),
                'mem' => $this->getMemInfo(),
                'webman' => $this->getWebmanInfo(),
                'sys' => $this->getSysInfo(),
                'sysFiles' => $this->getSysFiles(),
            ]
        ]);
    }

    private function getCpuInfo()
    {
        $cpuNum = function_exists('swoole_cpu_num') ? swoole_cpu_num() : 1;
        
        if (PHP_OS_FAMILY === 'Linux') {
            $stat1 = $this->getCpuStat();
            usleep(100000);
            $stat2 = $this->getCpuStat();
            
            if ($stat1 && $stat2) {
                $total = $stat2['total'] - $stat1['total'];
                $idle = $stat2['idle'] - $stat1['idle'];
                $user = $stat2['user'] - $stat1['user'];
                $sys = $stat2['sys'] - $stat1['sys'];
                
                if ($total > 0) {
                    return [
                        'cpuNum' => $cpuNum,
                        'used' => round(($user / $total) * 100, 2),
                        'sys' => round(($sys / $total) * 100, 2),
                        'free' => round(($idle / $total) * 100, 2),
                    ];
                }
            }
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $cpuUsage = $this->getWindowsCpuUsage();
            return [
                'cpuNum' => $cpuNum,
                'used' => round($cpuUsage, 2),
                'sys' => 0,
                'free' => round(100 - $cpuUsage, 2),
            ];
        }

        return [
            'cpuNum' => $cpuNum,
            'used' => 0,
            'sys' => 0,
            'free' => 100,
        ];
    }

    private function getCpuStat()
    {
        $stat = @file_get_contents('/proc/stat');
        if (!$stat) {
            return null;
        }
        
        $lines = explode("\n", $stat);
        $cpu = preg_split('/\s+/', $lines[0]);
        
        return [
            'user' => (int)$cpu[1],
            'nice' => (int)$cpu[2],
            'sys' => (int)$cpu[3],
            'idle' => (int)$cpu[4],
            'iowait' => isset($cpu[5]) ? (int)$cpu[5] : 0,
            'irq' => isset($cpu[6]) ? (int)$cpu[6] : 0,
            'softirq' => isset($cpu[7]) ? (int)$cpu[7] : 0,
            'total' => (int)$cpu[1] + (int)$cpu[2] + (int)$cpu[3] + (int)$cpu[4] + 
                       (isset($cpu[5]) ? (int)$cpu[5] : 0) + 
                       (isset($cpu[6]) ? (int)$cpu[6] : 0) + 
                       (isset($cpu[7]) ? (int)$cpu[7] : 0),
        ];
    }

    private function getWindowsCpuUsage()
    {
        $output = [];
        exec('wmic cpu get loadpercentage', $output);
        foreach ($output as $line) {
            if (is_numeric(trim($line))) {
                return (float)trim($line);
            }
        }
        return 0;
    }

    private function getMemInfo()
    {
        $memInfo = [];
        
        if (PHP_OS_FAMILY === 'Linux') {
            $mem = @file_get_contents('/proc/meminfo');
            if ($mem) {
                preg_match('/MemTotal:\s+(\d+)/', $mem, $total);
                preg_match('/MemAvailable:\s+(\d+)/', $mem, $avail);
                
                $totalMB = isset($total[1]) ? round($total[1] / 1024, 2) : 0;
                $freeMB = isset($avail[1]) ? round($avail[1] / 1024, 2) : 0;
                $usedMB = $totalMB - $freeMB;
                $usage = $totalMB > 0 ? round(($usedMB / $totalMB) * 100, 2) : 0;
                
                $memInfo = [
                    'total' => round($totalMB / 1024, 2),
                    'used' => round($usedMB / 1024, 2),
                    'free' => round($freeMB / 1024, 2),
                    'usage' => $usage,
                ];
            }
        } elseif (PHP_OS_FAMILY === 'Windows') {
            $output = [];
            exec('wmic OS get TotalVisibleMemorySize,FreePhysicalMemory /value', $output);
            
            $totalKB = 0;
            $freeKB = 0;
            
            foreach ($output as $line) {
                if (strpos($line, 'TotalVisibleMemorySize=') === 0) {
                    $totalKB = (int)substr($line, 23);
                } elseif (strpos($line, 'FreePhysicalMemory=') === 0) {
                    $freeKB = (int)substr($line, 19);
                }
            }
            
            $totalGB = round($totalKB / 1024 / 1024, 2);
            $freeGB = round($freeKB / 1024 / 1024, 2);
            $usedGB = $totalGB - $freeGB;
            $usage = $totalGB > 0 ? round(($usedGB / $totalGB) * 100, 2) : 0;
            
            $memInfo = [
                'total' => $totalGB,
                'used' => $usedGB,
                'free' => $freeGB,
                'usage' => $usage,
            ];
        } else {
            $memInfo = [
                'total' => 0,
                'used' => 0,
                'free' => 0,
                'usage' => 0,
            ];
        }
        
        return $memInfo;
    }

    private function getWebmanInfo()
    {
        $composerPath = base_path() . '/composer.json';
        $webmanVersion = 'unknown';
        
        if (file_exists($composerPath)) {
            $composer = json_decode(file_get_contents($composerPath), true);
            $webmanVersion = $composer['require']['workerman/webman-framework'] ?? 'unknown';
        }

        $startTime = self::$startTime ?? (defined('WEBMAN_START_TIME') ? WEBMAN_START_TIME : time());
        $runTime = time() - $startTime;
        
        $total = memory_get_peak_usage(true);
        $used = memory_get_usage(true);
        $free = $total - $used;
        
        return [
            'name' => 'Webman',
            'version' => $webmanVersion,
            'phpVersion' => PHP_VERSION,
            'startTime' => date('Y-m-d H:i:s', $startTime),
            'runTime' => $this->formatRunTime($runTime),
            'home' => PHP_BINARY,
            'userDir' => base_path(),
            'inputArgs' => implode(' ', $_SERVER['argv'] ?? []),
            'total' => round($total / 1024 / 1024, 2),
            'used' => round($used / 1024 / 1024, 2),
            'free' => round($free / 1024 / 1024, 2),
            'max' => ini_get('memory_limit'),
            'usage' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    private function formatRunTime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        $result = [];
        if ($days > 0) {
            $result[] = $days . '天';
        }
        if ($hours > 0) {
            $result[] = $hours . '小时';
        }
        if ($minutes > 0) {
            $result[] = $minutes . '分钟';
        }
        $result[] = $secs . '秒';
        
        return implode('', $result);
    }

    private function getSysInfo()
    {
        return [
            'computerName' => gethostname(),
            'osName' => PHP_OS,
            'osArch' => php_uname('m'),
            'computerIp' => $this->getServerIp(),
            'userDir' => base_path(),
        ];
    }

    private function getServerIp()
    {
        $ip = gethostbyname(gethostname());
        
        if ($ip === '127.0.0.1' || $ip === '0.0.0.0' || $ip === false) {
            if (PHP_OS_FAMILY === 'Linux') {
                $output = [];
                exec("ip addr | grep 'inet ' | grep -v '127.0.0.1' | awk '{print $2}' | cut -d'/' -f1 | head -n 1", $output);
                if (!empty($output)) {
                    return trim($output[0]);
                }
            } elseif (PHP_OS_FAMILY === 'Windows') {
                $output = [];
                exec('ipconfig', $output);
                foreach ($output as $line) {
                    if (strpos($line, 'IPv4') !== false) {
                        preg_match('/(\d+\.\d+\.\d+\.\d+)/', $line, $matches);
                        if (!empty($matches[1]) && $matches[1] !== '127.0.0.1') {
                            return $matches[1];
                        }
                    }
                }
            }
        }
        
        return $ip ?: '127.0.0.1';
    }

    private function getSysFiles()
    {
        $sysFiles = [];
        
        if (PHP_OS_FAMILY === 'Linux') {
            $mounts = @file_get_contents('/proc/mounts');
            if ($mounts) {
                $lines = explode("\n", $mounts);
                foreach ($lines as $line) {
                    $parts = preg_split('/\s+/', $line);
                    if (count($parts) >= 2 && strpos($parts[1], '/') === 0 && strpos($parts[1], '/proc') !== 0 && strpos($parts[1], '/sys') !== 0) {
                        $mountPoint = $parts[1];
                        $total = disk_total_space($mountPoint);
                        $free = disk_free_space($mountPoint);
                        
                        if ($total > 0) {
                            $used = $total - $free;
                            $sysFiles[] = [
                                'dirName' => $mountPoint,
                                'sysTypeName' => $parts[2] ?? 'unknown',
                                'typeName' => $parts[0] ?? 'unknown',
                                'total' => $this->formatFileSize($total),
                                'free' => $this->formatFileSize($free),
                                'used' => $this->formatFileSize($used),
                                'usage' => round(($used / $total) * 100, 2),
                            ];
                        }
                    }
                }
            }
        } elseif (PHP_OS_FAMILY === 'Windows') {
            for ($i = ord('C'); $i <= ord('Z'); $i++) {
                $drive = chr($i) . ':\\';
                if (is_dir($drive)) {
                    $total = disk_total_space($drive);
                    $free = disk_free_space($drive);
                    $used = $total - $free;
                    
                    $sysFiles[] = [
                        'dirName' => $drive,
                        'sysTypeName' => 'NTFS',
                        'typeName' => 'local',
                        'total' => $this->formatFileSize($total),
                        'free' => $this->formatFileSize($free),
                        'used' => $this->formatFileSize($used),
                        'usage' => round(($used / $total) * 100, 2),
                    ];
                }
            }
        }
        
        if (empty($sysFiles)) {
            $total = disk_total_space('.');
            $free = disk_free_space('.');
            $used = $total - $free;
            
            $sysFiles[] = [
                'dirName' => dirname(__DIR__, 3),
                'sysTypeName' => PHP_OS_FAMILY,
                'typeName' => PHP_OS,
                'total' => $this->formatFileSize($total),
                'free' => $this->formatFileSize($free),
                'used' => $this->formatFileSize($used),
                'usage' => round(($used / $total) * 100, 2),
            ];
        }
        
        return $sysFiles;
    }

    private function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unit = 0;
        
        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }
        
        return round($size, 2) . ' ' . $units[$unit];
    }
}
