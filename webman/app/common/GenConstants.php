<?php

namespace app\common;

class GenConstants
{
    const TPL_CRUD = 'crud';
    const TPL_TREE = 'tree';
    const TPL_SUB = 'sub';

    const TYPE_STRING = 'String';
    const TYPE_INTEGER = 'Integer';
    const TYPE_LONG = 'Long';
    const TYPE_DOUBLE = 'Double';
    const TYPE_BIGDECIMAL = 'BigDecimal';
    const TYPE_DATE = 'Date';

    const QUERY_EQ = 'EQ';
    const QUERY_NE = 'NE';
    const QUERY_GT = 'GT';
    const QUERY_GTE = 'GTE';
    const QUERY_LT = 'LT';
    const QUERY_LTE = 'LTE';
    const QUERY_LIKE = 'LIKE';
    const QUERY_BETWEEN = 'BETWEEN';

    const HTML_INPUT = 'input';
    const HTML_TEXTAREA = 'textarea';
    const HTML_SELECT = 'select';
    const HTML_RADIO = 'radio';
    const HTML_CHECKBOX = 'checkbox';
    const HTML_DATETIME = 'datetime';
    const HTML_IMAGE_UPLOAD = 'imageUpload';
    const HTML_FILE_UPLOAD = 'fileUpload';
    const HTML_EDITOR = 'editor';

    const REQUIRE = '1';

    const COLUMNTYPE_STR = ['char', 'varchar', 'nvarchar', 'varchar2', 'tinytext'];
    const COLUMNTYPE_TEXT = ['tinytext', 'text', 'mediumtext', 'longtext'];
    const COLUMNTYPE_TIME = ['datetime', 'time', 'date', 'timestamp'];
    const COLUMNTYPE_NUMBER = ['tinyint', 'smallint', 'mediumint', 'int', 'number', 'integer', 'bigint', 'float', 'double', 'decimal'];

    const COLUMNNAME_NOT_EDIT = ['id', 'create_by', 'create_time', 'del_flag'];
    const COLUMNNAME_NOT_LIST = ['id', 'create_by', 'create_time', 'update_by', 'update_time', 'del_flag', 'remark'];
    const COLUMNNAME_NOT_QUERY = ['id', 'create_by', 'create_time', 'update_by', 'update_time', 'del_flag', 'remark'];

    const BASE_ENTITY_FIELDS = ['create_by', 'create_time', 'update_by', 'update_time', 'remark'];

    public static function arraysContains(array $arr, string $targetValue): bool
    {
        return in_array($targetValue, $arr);
    }
}
