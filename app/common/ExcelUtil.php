<?php

namespace app\common;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use support\Response;

class ExcelUtil
{
    const SEPARATOR = ',';

    const FORMULA_REGEX_STR = '=|-|\+|@';

    const FORMULA_STR = ['=', '-', '+', '@'];

    const TYPE_ALL = 0;
    const TYPE_EXPORT = 1;
    const TYPE_IMPORT = 2;

    private $clazz;
    private $fields = [];
    private $spreadsheet;
    private $sheet;
    private $sheetName;
    private $title;
    private $type;
    private $rownum = 0;
    private $list = [];

    public function __construct(string $clazz)
    {
        $this->clazz = $clazz;
    }

    public function exportExcel($list, string $sheetName, string $title = ''): Response
    {
        $this->list = $list ?? [];
        $this->sheetName = $sheetName;
        $this->title = $title;
        $this->type = self::TYPE_EXPORT;

        $this->createExcelField();
        $this->createWorkbook();
        $this->createTitle();
        $this->writeSheet();

        return $this->outputExcel($sheetName);
    }

    public function importTemplateExcel(string $sheetName, string $title = ''): Response
    {
        $this->list = [];
        $this->sheetName = $sheetName;
        $this->title = $title;
        $this->type = self::TYPE_IMPORT;

        $this->createExcelField();
        $this->createWorkbook();
        $this->createTitle();
        $this->writeSheet();

        return $this->outputExcel($sheetName);
    }

    public function importExcel($file): array
    {
        if (!$file || !$file->isValid()) {
            throw new \Exception('上传文件无效');
        }

        $extension = strtolower($file->getUploadExtension());
        if (!in_array($extension, ['xlsx', 'xls'])) {
            throw new \Exception('文件格式不正确，仅支持xlsx和xls格式');
        }

        $this->type = self::TYPE_IMPORT;
        $this->createExcelField();

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->getHighestRow();
        $columns = $sheet->getHighestColumn();

        $list = [];
        $cellMap = [];

        $headerRow = $sheet->getRowIterator(1)->current();
        foreach ($headerRow->getCellIterator() as $cell) {
            $value = $cell->getValue();
            if ($value !== null) {
                $cellMap[$value] = $cell->getColumn();
            }
        }

        $fieldsMap = [];
        foreach ($this->fields as $field) {
            $fieldName = $field['field'];
            $excelName = $field['name'];
            if (isset($cellMap[$excelName])) {
                $fieldsMap[$cellMap[$excelName]] = $field;
            }
        }

        for ($row = 2; $row <= $rows; $row++) {
            $entity = [];
            $isEmpty = true;

            foreach ($fieldsMap as $col => $field) {
                $cell = $sheet->getCell($col . $row);
                $value = $cell->getValue();

                if ($value !== null && $value !== '') {
                    $isEmpty = false;
                }

                $fieldName = $field['field'];
                $entity[$fieldName] = $this->convertValue($value, $field);
            }

            if (!$isEmpty) {
                $list[] = $entity;
            }
        }

        return $list;
    }

    private function convertValue($value, array $field)
    {
        if ($value === null) {
            return null;
        }

        if (!empty($field['readConverterExp'])) {
            $value = $this->reverseByExp($value, $field['readConverterExp']);
        }

        if (!empty($field['dictType'])) {
            $value = $this->reverseDictByExp($value, $field['dictType']);
        }

        if (!empty($field['dateFormat']) && $value !== null) {
            if (is_numeric($value)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                $value = $date->format('Y-m-d H:i:s');
            }
        }

        return $value;
    }

    private function createExcelField(): void
    {
        $this->fields = [];
        if (method_exists($this->clazz, 'getExcelFields')) {
            $fields = call_user_func([$this->clazz, 'getExcelFields']);
            foreach ($fields as $fieldName => $config) {
                $fieldType = $config['type'] ?? 'all';
                if ($this->type === self::TYPE_EXPORT) {
                    if ($fieldType === 'import') {
                        continue;
                    }
                } elseif ($this->type === self::TYPE_IMPORT) {
                    if ($fieldType === 'export') {
                        continue;
                    }
                }

                $this->fields[] = array_merge([
                    'field' => $fieldName,
                    'name' => $fieldName,
                    'width' => 16,
                    'height' => 14,
                    'dateFormat' => '',
                    'dictType' => '',
                    'readConverterExp' => '',
                    'cellType' => 'string',
                    'type' => 'all',
                    'prompt' => '',
                    'combo' => [],
                ], $config);
            }
        }

        usort($this->fields, function ($a, $b) {
            $sortA = $a['sort'] ?? PHP_INT_MAX;
            $sortB = $b['sort'] ?? PHP_INT_MAX;
            return $sortA <=> $sortB;
        });
    }

    private function createWorkbook(): void
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
        $this->sheet->setTitle($this->sheetName);
        $this->rownum = 0;
    }

    private function createTitle(): void
    {
        if (!empty($this->title)) {
            $row = $this->sheet->getRowDimension(1);
            $row->setRowHeight(30);

            $cell = $this->sheet->getCell('A1');
            $cell->setValue($this->title);
            $cell->getStyle()->getFont()->setBold(true)->setSize(16);
            $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $lastCol = count($this->fields) - 1;
            if ($lastCol > 0) {
                $this->sheet->mergeCells('A1:' . $this->getColumnLetter($lastCol) . '1');
            }
            $this->rownum = 1;
        }
    }

    private function writeSheet(): void
    {
        $headerRow = $this->rownum + 1;
        $colIndex = 0;

        foreach ($this->fields as $field) {
            $colLetter = $this->getColumnLetter($colIndex);
            $cell = $this->sheet->getCell($colLetter . $headerRow);
            $cell->setValue($field['name']);

            $style = $cell->getStyle();
            $style->getFont()->setBold(true);
            $style->getFill()->setFillType(Fill::FILL_SOLID);
            $style->getFill()->getStartColor()->setRGB('4F81BD');
            $style->getFont()->getColor()->setRGB('FFFFFF');
            $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $this->sheet->getColumnDimension($colLetter)->setWidth($field['width'] ?? 16);

            $colIndex++;
        }

        $this->rownum = $headerRow;

        if ($this->type === self::TYPE_EXPORT && !empty($this->list)) {
            $this->fillExcelData();
        }
    }

    private function fillExcelData(): void
    {
        foreach ($this->list as $item) {
            $this->rownum++;
            $colIndex = 0;

            $itemArray = is_object($item) ? $item->toArray() : $item;

            foreach ($this->fields as $field) {
                $colLetter = $this->getColumnLetter($colIndex);
                $cell = $this->sheet->getCell($colLetter . $this->rownum);

                $fieldName = $field['field'];
                $value = $itemArray[$fieldName] ?? null;

                if ($value !== null) {
                    if (!empty($field['dateFormat']) && $value !== null) {
                        if ($value instanceof \DateTime) {
                            $value = $value->format($field['dateFormat']);
                        } elseif (is_string($value)) {
                            $date = strtotime($value);
                            if ($date !== false) {
                                $value = date($field['dateFormat'], $date);
                            }
                        }
                    }

                    if (!empty($field['readConverterExp'])) {
                        $value = $this->convertByExp($value, $field['readConverterExp']);
                    }

                    if (!empty($field['dictType'])) {
                        $value = $this->convertDictByExp($value, $field['dictType']);
                    }

                    $cellValue = (string)$value;
                    if (preg_match('/^' . self::FORMULA_REGEX_STR . '/', $cellValue)) {
                        $cellValue = "\t" . $cellValue;
                    }

                    $cell->setValue($cellValue);
                }

                $style = $cell->getStyle();
                $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                $colIndex++;
            }
        }
    }

    private function outputExcel(string $filename): Response
    {
        $writer = new Xlsx($this->spreadsheet);

        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);

        $downloadFilename = $this->encodingFilename($filename);

        $response = new Response(200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="' . $downloadFilename . '"',
            'Cache-Control' => 'max-age=0',
        ], file_get_contents($tempFile));

        unlink($tempFile);

        return $response;
    }

    private function encodingFilename(string $filename): string
    {
        return uniqid() . '_' . $filename . '.xlsx';
    }

    private function getColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = intval($index / 26) - 1;
        }
        return $letter;
    }

    public static function convertByExp($propertyValue, string $converterExp, string $separator = ','): string
    {
        $propertyString = '';
        $convertSource = explode($separator, $converterExp);

        foreach ($convertSource as $item) {
            $itemArray = explode('=', $item, 2);
            if (count($itemArray) !== 2) {
                continue;
            }

            if (strpos((string)$propertyValue, $separator) !== false) {
                foreach (explode($separator, (string)$propertyValue) as $value) {
                    if ($itemArray[0] === $value) {
                        $propertyString .= $itemArray[1] . $separator;
                        break;
                    }
                }
            } else {
                if ($itemArray[0] === (string)$propertyValue) {
                    return $itemArray[1];
                }
            }
        }

        return rtrim($propertyString, $separator);
    }

    public static function reverseByExp($propertyValue, string $converterExp, string $separator = ','): string
    {
        $propertyString = '';
        $convertSource = explode($separator, $converterExp);

        foreach ($convertSource as $item) {
            $itemArray = explode('=', $item, 2);
            if (count($itemArray) !== 2) {
                continue;
            }

            if (strpos((string)$propertyValue, $separator) !== false) {
                foreach (explode($separator, (string)$propertyValue) as $value) {
                    if ($itemArray[1] === $value) {
                        $propertyString .= $itemArray[0] . $separator;
                        break;
                    }
                }
            } else {
                if ($itemArray[1] === (string)$propertyValue) {
                    return $itemArray[0];
                }
            }
        }

        return rtrim($propertyString, $separator);
    }

    public static function convertDictByExp($dictValue, string $dictType, string $separator = ','): string
    {
        return SysDictTypeService::getDictLabel($dictType, (string)$dictValue, $separator);
    }

    public static function reverseDictByExp($dictLabel, string $dictType, string $separator = ','): string
    {
        return SysDictTypeService::getDictValue($dictType, (string)$dictLabel, $separator);
    }
}
