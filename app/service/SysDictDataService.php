<?php

namespace app\service;

use app\model\SysDictData;

class SysDictDataService
{
    public function selectDictDataList($params = [])
    {
        $query = SysDictData::query();

        if (!empty($params['dict_type'])) {
            $query->where('dict_type', $params['dict_type']);
        }
        if (!empty($params['dict_label'])) {
            $query->where('dict_label', 'like', '%' . $params['dict_label'] . '%');
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        return $query->orderBy('dict_sort', 'asc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectDictDataById($dictCode)
    {
        return SysDictData::find($dictCode);
    }

    public function insertDictData($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        $result = SysDictData::create($data);
        SysDictTypeService::resetDictCache();
        return $result;
    }

    public function updateDictData($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        $result = SysDictData::where('dict_code', $data['dict_code'])->update($data);
        SysDictTypeService::resetDictCache();
        return $result;
    }

    public function deleteDictDataByIds($dictCodes)
    {
        $result = SysDictData::whereIn('dict_code', $dictCodes)->delete();
        SysDictTypeService::resetDictCache();
        return $result;
    }
}
