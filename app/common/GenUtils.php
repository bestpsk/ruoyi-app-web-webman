<?php

namespace app\common;

use app\model\GenTable;
use app\model\GenTableColumn;

class GenUtils
{
    public static function initTable(GenTable $genTable, string $operName = ''): void
    {
        $genTable->class_name = self::convertClassName($genTable->table_name);
        $genTable->package_name = 'com.ruoyi.project.system';
        $genTable->module_name = self::getModuleName($genTable->package_name);
        $genTable->business_name = self::getBusinessName($genTable->table_name);
        $genTable->function_name = self::replaceText($genTable->table_comment ?: '');
        $genTable->function_author = 'ruoyi';
    }

    public static function initColumnField(GenTableColumn $column, GenTable $table): void
    {
        $dataType = self::getDbType($column->column_type);
        $columnName = $column->column_name;

        $column->table_id = $table->table_id;
        $column->java_field = self::toCamelCase($columnName);
        $column->java_type = GenConstants::TYPE_STRING;
        $column->query_type = GenConstants::QUERY_EQ;

        if (GenConstants::arraysContains(GenConstants::COLUMNTYPE_STR, $dataType) || GenConstants::arraysContains(GenConstants::COLUMNTYPE_TEXT, $dataType)) {
            $columnLength = self::getColumnLength($column->column_type);
            $htmlType = ($columnLength >= 500 || GenConstants::arraysContains(GenConstants::COLUMNTYPE_TEXT, $dataType))
                ? GenConstants::HTML_TEXTAREA : GenConstants::HTML_INPUT;
            $column->html_type = $htmlType;
        } elseif (GenConstants::arraysContains(GenConstants::COLUMNTYPE_TIME, $dataType)) {
            $column->java_type = GenConstants::TYPE_DATE;
            $column->html_type = GenConstants::HTML_DATETIME;
        } elseif (GenConstants::arraysContains(GenConstants::COLUMNTYPE_NUMBER, $dataType)) {
            $column->html_type = GenConstants::HTML_INPUT;
            $parenStr = self::getParenContent($column->column_type);
            if ($parenStr !== null) {
                $str = explode(',', $parenStr);
                if (count($str) == 2 && intval($str[1]) > 0) {
                    $column->java_type = GenConstants::TYPE_BIGDECIMAL;
                } elseif (count($str) == 1 && intval($str[0]) <= 10) {
                    $column->java_type = GenConstants::TYPE_INTEGER;
                } else {
                    $column->java_type = GenConstants::TYPE_LONG;
                }
            } else {
                $column->java_type = GenConstants::TYPE_LONG;
            }
        }

        $column->is_insert = GenConstants::REQUIRE;

        if (!GenConstants::arraysContains(GenConstants::COLUMNNAME_NOT_EDIT, $columnName) && !$column->is_pk) {
            $column->is_edit = GenConstants::REQUIRE;
        }
        if (!GenConstants::arraysContains(GenConstants::COLUMNNAME_NOT_LIST, $columnName) && !$column->is_pk) {
            $column->is_list = GenConstants::REQUIRE;
        }
        if (!GenConstants::arraysContains(GenConstants::COLUMNNAME_NOT_QUERY, $columnName) && !$column->is_pk) {
            $column->is_query = GenConstants::REQUIRE;
        }

        if (self::endsWithIgnoreCase($columnName, 'name')) {
            $column->query_type = GenConstants::QUERY_LIKE;
        }
        if (self::endsWithIgnoreCase($columnName, 'status')) {
            $column->html_type = GenConstants::HTML_RADIO;
        } elseif (self::endsWithIgnoreCase($columnName, 'type') || self::endsWithIgnoreCase($columnName, 'sex')) {
            $column->html_type = GenConstants::HTML_SELECT;
        } elseif (self::endsWithIgnoreCase($columnName, 'image')) {
            $column->html_type = GenConstants::HTML_IMAGE_UPLOAD;
        } elseif (self::endsWithIgnoreCase($columnName, 'file')) {
            $column->html_type = GenConstants::HTML_FILE_UPLOAD;
        } elseif (self::endsWithIgnoreCase($columnName, 'content')) {
            $column->html_type = GenConstants::HTML_EDITOR;
        }
    }

    public static function getModuleName(string $packageName): string
    {
        $lastIndex = strrpos($packageName, '.');
        return $lastIndex !== false ? substr($packageName, $lastIndex + 1) : $packageName;
    }

    public static function getBusinessName(string $tableName): string
    {
        $lastIndex = strrpos($tableName, '_');
        return $lastIndex !== false ? substr($tableName, $lastIndex + 1) : $tableName;
    }

    public static function convertClassName(string $tableName): string
    {
        return self::convertToCamelCase($tableName);
    }

    public static function replaceText(string $text): string
    {
        return preg_replace('/(?:表|若依)/', '', $text);
    }

    public static function getDbType(string $columnType): string
    {
        $pos = strpos($columnType, '(');
        return $pos > 0 ? substr($columnType, 0, $pos) : $columnType;
    }

    public static function getColumnLength(string $columnType): int
    {
        $parenStr = self::getParenContent($columnType);
        if ($parenStr !== null && is_numeric($parenStr)) {
            return intval($parenStr);
        }
        return 0;
    }

    private static function getParenContent(string $columnType): ?string
    {
        if (preg_match('/\(([^)]+)\)/', $columnType, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public static function toCamelCase(string $str): string
    {
        $parts = explode('_', $str);
        $result = array_shift($parts);
        foreach ($parts as $part) {
            $result .= ucfirst($part);
        }
        return $result;
    }

    public static function convertToCamelCase(string $str): string
    {
        $parts = explode('_', $str);
        $result = '';
        foreach ($parts as $part) {
            $result .= ucfirst($part);
        }
        return $result;
    }

    public static function toSnakeCase(string $str): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
    }

    public static function endsWithIgnoreCase(string $str, string $suffix): bool
    {
        return str_ends_with(strtolower($str), strtolower($suffix));
    }

    public static function isSuperColumn(string $javaField): bool
    {
        return in_array($javaField, GenConstants::BASE_ENTITY_FIELDS);
    }
}
