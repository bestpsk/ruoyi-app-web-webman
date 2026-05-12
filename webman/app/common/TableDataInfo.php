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
        if ($data === null) {
            return null;
        }

        if ($data instanceof \Illuminate\Support\Collection) {
            return $data->map(function ($item) {
                return self::convertToCamelCase($item);
            })->toArray();
        }

        if (is_object($data)) {
            $data = method_exists($data, 'toArray') ? $data->toArray() : (array) $data;
        }

        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            $newKey = is_string($key) ? self::toCamelCase($key) : $key;
            if (is_array($value) || is_object($value)) {
                $result[$newKey] = self::convertToCamelCase($value);
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
