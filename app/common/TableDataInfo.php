<?php

namespace app\common;

class TableDataInfo
{
    public static function result($list, $total = null, $code = 200, $msg = '查询成功')
    {
        if ($total === null) {
            $total = is_array($list) ? count($list) : $list->count();
        }

        $list = self::convertToCamelCase($list);

        return json([
            'total' => $total,
            'rows' => $list,
            'code' => $code,
            'msg' => $msg,
        ]);
    }

    private static function convertToCamelCase($data)
    {
        if (!is_array($data) && !is_object($data)) {
            return $data;
        }

        if (is_object($data)) {
            $data = $data->toArray();
        }

        $result = [];
        foreach ($data as $key => $value) {
            $newKey = self::toCamelCase($key);
            if (is_array($value)) {
                $result[$newKey] = self::convertToCamelCase($value);
            } elseif (is_object($value)) {
                $result[$newKey] = self::convertToCamelCase($value->toArray());
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    private static function toCamelCase($key)
    {
        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }
}
