<?php

namespace app\service;

use app\common\GenConstants;
use app\common\GenTemplateEngine;
use app\common\GenUtils;
use app\model\GenTable;
use app\model\GenTableColumn;
use support\Db;
use ZipArchive;

class GenTableService
{
    public function selectGenTableList($params = [])
    {
        $query = GenTable::query();

        if (!empty($params['table_name'])) {
            $query->where('table_name', 'like', '%' . $params['table_name'] . '%');
        }
        if (!empty($params['table_comment'])) {
            $query->where('table_comment', 'like', '%' . $params['table_comment'] . '%');
        }

        $pageNum = intval($params['page_num'] ?? $params['pageNum'] ?? 1);
        $pageSize = intval($params['page_size'] ?? $params['pageSize'] ?? 10);
        return $query->orderBy('table_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);
    }

    public function selectGenTableById($tableId)
    {
        return GenTable::with('columns')->find($tableId);
    }

    public function selectDbTableList($params = [])
    {
        $tableName = $params['table_name'] ?? '';
        $tableComment = $params['table_comment'] ?? '';

        $excludeTables = GenTable::pluck('table_name')->toArray();

        $query = Db::select("SELECT table_name, table_comment, create_time, update_time
            FROM information_schema.TABLES
            WHERE table_schema = (SELECT DATABASE())
            AND table_name NOT LIKE 'qrtz_%'
            AND table_name NOT LIKE 'gen_%'
            AND table_type = 'BASE TABLE'");

        $result = [];
        foreach ($query as $row) {
            $row = (array)$row;
            if (!empty($excludeTables) && in_array($row['TABLE_NAME'] ?? $row['table_name'] ?? '', $excludeTables)) {
                continue;
            }
            $name = $row['TABLE_NAME'] ?? $row['table_name'] ?? '';
            $comment = $row['TABLE_COMMENT'] ?? $row['table_comment'] ?? '';
            if ($tableName && strpos($name, $tableName) === false) continue;
            if ($tableComment && strpos($comment, $tableComment) === false) continue;
            $result[] = [
                'table_name' => $name,
                'table_comment' => $comment,
                'create_time' => $row['CREATE_TIME'] ?? $row['create_time'] ?? '',
                'update_time' => $row['UPDATE_TIME'] ?? $row['update_time'] ?? '',
            ];
        }

        $pageNum = intval($params['page_num'] ?? $params['pageNum'] ?? 1);
        $pageSize = intval($params['page_size'] ?? $params['pageSize'] ?? 10);
        $total = count($result);
        $rows = array_slice($result, ($pageNum - 1) * $pageSize, $pageSize);

        return ['total' => $total, 'rows' => $rows];
    }

    public function importGenTable($tableNames)
    {
        foreach ($tableNames as $tableName) {
            $tableInfo = Db::selectOne("SELECT table_name, table_comment FROM information_schema.TABLES WHERE table_schema = (SELECT DATABASE()) AND table_name = ?", [$tableName]);
            if (!$tableInfo) continue;

            $genTable = new GenTable();
            $genTable->table_name = $tableName;
            $genTable->table_comment = $tableInfo->table_comment ?? '';
            GenUtils::initTable($genTable);
            $genTable->tpl_category = 'crud';
            $genTable->create_time = date('Y-m-d H:i:s');
            $genTable->save();

            $columns = Db::select("SELECT column_name, column_comment, column_type, column_key, is_nullable, extra
                FROM information_schema.COLUMNS
                WHERE table_schema = (SELECT DATABASE()) AND table_name = ?
                ORDER BY ordinal_position", [$tableName]);

            $sort = 0;
            foreach ($columns as $col) {
                $col = (array)$col;
                $genColumn = new GenTableColumn();
                $genColumn->table_id = $genTable->table_id;
                $genColumn->column_name = $col['COLUMN_NAME'] ?? $col['column_name'] ?? '';
                $genColumn->column_comment = $col['COLUMN_COMMENT'] ?? $col['column_comment'] ?? '';
                $genColumn->column_type = $col['COLUMN_TYPE'] ?? $col['column_type'] ?? '';
                $genColumn->is_pk = ($col['COLUMN_KEY'] ?? $col['column_key'] ?? '') === 'PRI' ? '1' : '0';
                $genColumn->is_increment = ($col['EXTRA'] ?? $col['extra'] ?? '') === 'auto_increment' ? '1' : '0';
                $genColumn->is_required = ($col['IS_NULLABLE'] ?? $col['is_nullable'] ?? '') === 'NO' ? '1' : '0';
                $genColumn->sort = $sort++;
                $genColumn->create_time = date('Y-m-d H:i:s');
                GenUtils::initColumnField($genColumn, $genTable);
                $genColumn->save();
            }
        }
        return true;
    }

    public function deleteGenTableByIds($tableIds)
    {
        GenTableColumn::whereIn('table_id', $tableIds)->delete();
        return GenTable::whereIn('table_id', $tableIds)->delete();
    }

    public function updateGenTable($data)
    {
        $tableId = $data['table_id'];
        $tableData = [
            'table_name' => $data['table_name'] ?? '',
            'table_comment' => $data['table_comment'] ?? '',
            'class_name' => $data['class_name'] ?? '',
            'function_name' => $data['function_name'] ?? '',
            'function_author' => $data['function_author'] ?? '',
            'tpl_category' => $data['tpl_category'] ?? 'crud',
            'package_name' => $data['package_name'] ?? '',
            'module_name' => $data['module_name'] ?? '',
            'business_name' => $data['business_name'] ?? '',
            'gen_type' => $data['gen_type'] ?? '0',
            'gen_path' => $data['gen_path'] ?? '/',
            'options' => $data['options'] ?? '',
            'update_time' => date('Y-m-d H:i:s'),
            'remark' => $data['remark'] ?? '',
        ];
        GenTable::where('table_id', $tableId)->update($tableData);

        if (!empty($data['columns']) && is_array($data['columns'])) {
            foreach ($data['columns'] as $colData) {
                $columnId = $colData['column_id'] ?? null;
                if (!$columnId) continue;
                $updateData = [];
                $allowedFields = [
                    'column_comment', 'java_type', 'java_field', 'is_insert', 'is_edit',
                    'is_list', 'is_query', 'query_type', 'html_type', 'dict_type', 'sort'
                ];
                foreach ($allowedFields as $field) {
                    if (isset($colData[$field])) {
                        $updateData[$field] = $colData[$field];
                    }
                }
                if (!empty($updateData)) {
                    $updateData['update_time'] = date('Y-m-d H:i:s');
                    GenTableColumn::where('column_id', $columnId)->update($updateData);
                }
            }
        }
        return true;
    }

    public function synchDb($tableName)
    {
        $genTable = GenTable::where('table_name', $tableName)->first();
        if (!$genTable) return false;

        GenTableColumn::where('table_id', $genTable->table_id)->delete();

        $columns = Db::select("SELECT column_name, column_comment, column_type, column_key, is_nullable, extra
            FROM information_schema.COLUMNS
            WHERE table_schema = (SELECT DATABASE()) AND table_name = ?
            ORDER BY ordinal_position", [$tableName]);

        $sort = 0;
        foreach ($columns as $col) {
            $col = (array)$col;
            $genColumn = new GenTableColumn();
            $genColumn->table_id = $genTable->table_id;
            $genColumn->column_name = $col['COLUMN_NAME'] ?? $col['column_name'] ?? '';
            $genColumn->column_comment = $col['COLUMN_COMMENT'] ?? $col['column_comment'] ?? '';
            $genColumn->column_type = $col['COLUMN_TYPE'] ?? $col['column_type'] ?? '';
            $genColumn->is_pk = ($col['COLUMN_KEY'] ?? $col['column_key'] ?? '') === 'PRI' ? '1' : '0';
            $genColumn->is_increment = ($col['EXTRA'] ?? $col['extra'] ?? '') === 'auto_increment' ? '1' : '0';
            $genColumn->is_required = ($col['IS_NULLABLE'] ?? $col['is_nullable'] ?? '') === 'NO' ? '1' : '0';
            $genColumn->sort = $sort++;
            $genColumn->create_time = date('Y-m-d H:i:s');
            GenUtils::initColumnField($genColumn, $genTable);
            $genColumn->save();
        }
        return true;
    }

    public function previewCode($tableId)
    {
        $table = GenTable::with('columns')->find($tableId);
        if (!$table) return [];

        $context = $this->prepareContext($table);
        $templates = $this->getTemplateList($table->tpl_category);

        $result = [];
        $templateDir = resource_path() . '/template';
        foreach ($templates as $templateName => $fileName) {
            $templatePath = $templateDir . '/' . $templateName;
            if (!file_exists($templatePath)) continue;
            $code = GenTemplateEngine::renderFile($templatePath, $context);
            $result[] = [
                'templateName' => $templateName,
                'fileName' => $this->getFileName($templateName, $table, $context),
                'code' => $code,
            ];
        }
        return $result;
    }

    public function generateCode($tableName)
    {
        $table = GenTable::with('columns')->where('table_name', $tableName)->first();
        if (!$table) return [];

        $context = $this->prepareContext($table);
        $templates = $this->getTemplateList($table->tpl_category);

        $result = [];
        $templateDir = resource_path() . '/template';
        foreach ($templates as $templateName => $fileName) {
            $templatePath = $templateDir . '/' . $templateName;
            if (!file_exists($templatePath)) continue;
            $code = GenTemplateEngine::renderFile($templatePath, $context);
            $result[$this->getFileName($templateName, $table, $context)] = $code;
        }
        return $result;
    }

    public function downloadCode($tableNames)
    {
        $zip = new ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'gen_');
        $zip->open($tempFile, ZipArchive::CREATE);

        foreach ($tableNames as $tableName) {
            $data = $this->generateCode($tableName);
            foreach ($data as $fileName => $code) {
                $zip->addFromString($tableName . '/' . $fileName, $code);
            }
        }

        $zip->close();
        return $tempFile;
    }

    private function prepareContext(GenTable $table): array
    {
        $columns = $table->columns->toArray();
        $pkColumn = null;
        foreach ($columns as $col) {
            if ($col['is_pk'] === '1') {
                $pkColumn = $col;
                break;
            }
        }

        $importList = [];
        $dicts = [];
        foreach ($columns as $col) {
            if (!empty($col['dict_type']) && !in_array($col['dict_type'], $dicts)) {
                $dicts[] = $col['dict_type'];
            }
        }

        $moduleName = $table->module_name;
        $businessName = $table->business_name;
        $className = $table->class_name;
        $classNameFirstLower = lcfirst($className);

        $permissionPrefix = $moduleName . ':' . $businessName;

        return [
            'table' => $table->toArray(),
            'columns' => $columns,
            'pkColumn' => $pkColumn,
            'importList' => $importList,
            'dicts' => $dicts,
            'moduleName' => $moduleName,
            'businessName' => $businessName,
            'className' => $className,
            'classNameFirstLower' => $classNameFirstLower,
            'functionName' => $table->function_name,
            'functionAuthor' => $table->function_author,
            'tableName' => $table->table_name,
            'tableComment' => $table->table_comment,
            'permissionPrefix' => $permissionPrefix,
            'packageName' => $table->package_name,
            'tplCategory' => $table->tpl_category,
            'genPath' => $table->gen_path,
            'options' => $table->options ? json_decode($table->options, true) : [],
            'tool' => [
                'firstLowerCase' => function ($str) { return lcfirst($str); },
                'snakeCase' => function ($str) { return GenUtils::toSnakeCase($str); },
            ],
        ];
    }

    private function getTemplateList(string $tplCategory): array
    {
        $templates = [
            'controller.php.vm' => 'controller',
            'service.php.vm' => 'service',
            'model.php.vm' => 'model',
            'route.php.vm' => 'route',
            'api.js.vm' => 'api',
            'index.vue.vm' => 'vue',
            'menu.sql.vm' => 'sql',
        ];

        return $templates;
    }

    private function getFileName(string $templateName, GenTable $table, array $context): string
    {
        $className = $table->class_name;
        $moduleName = $table->module_name;
        $businessName = $table->business_name;

        return match ($templateName) {
            'controller.php.vm' => $className . 'Controller.php',
            'service.php.vm' => $className . 'Service.php',
            'model.php.vm' => $className . '.php',
            'route.php.vm' => $businessName . '.php',
            'api.js.vm' => $moduleName . '/' . $businessName . '.js',
            'index.vue.vm' => $moduleName . '/' . $businessName . '/index.vue',
            'menu.sql.vm' => $businessName . 'Menu.sql',
            default => $templateName,
        };
    }
}
