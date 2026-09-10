<?php
declare(strict_types=1);

namespace app\common;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Excel 导入导出（PhpSpreadsheet）
 */
class ExcelUtil
{
    /**
     * 导出 Excel，返回临时文件名供下载
     * @param array $rows 数据行
     * @param array $headers 字段 => 列标题
     */
    public static function export(array $rows, array $headers, string $sheetName = 'Sheet1'): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($sheetName, 0, 31));

        $col = 1;
        foreach ($headers as $title) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '1', $title);
            $col++;
        }

        $rowNum = 2;
        foreach ($rows as $row) {
            $col = 1;
            foreach (array_keys($headers) as $field) {
                $val = self::resolveField($row, $field);
                if (is_array($val) || is_object($val)) {
                    $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                }
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . $rowNum, $val ?? '');
                $col++;
            }
            $rowNum++;
        }

        $dir = runtime_path() . 'download' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fileName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.xlsx';
        $path = $dir . $fileName;
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $fileName;
    }

    /**
     * 下载仅含表头的导入模板
     * @param array $headers 字段 => 列标题
     */
    public static function template(array $headers, string $sheetName = 'Sheet1'): string
    {
        return self::export([], $headers, $sheetName);
    }

    /**
     * 从 Excel 导入数据行
     * @param array $headers 字段 => 列标题（按字段顺序读列）
     * @return array 数据行列表
     */
    public static function import(string $filePath, array $headers): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = (int) $sheet->getHighestRow();
        $fields = array_keys($headers);
        $result = [];
        for ($r = 2; $r <= $highestRow; $r++) {
            $row = [];
            $empty = true;
            foreach ($fields as $i => $field) {
                $val = $sheet->getCell(Coordinate::stringFromColumnIndex($i + 1) . $r)->getFormattedValue();
                $row[$field] = is_string($val) ? trim($val) : $val;
                if ($row[$field] !== null && $row[$field] !== '') {
                    $empty = false;
                }
            }
            if (!$empty) {
                $result[] = $row;
            }
        }
        $spreadsheet->disconnectWorksheets();
        return $result;
    }

    /**
     * 按字段名解析单元格值（支持点路径与下划线键）
     */
    protected static function resolveField(array $row, string $field)
    {
        if (array_key_exists($field, $row)) {
            return $row[$field];
        }
        if (str_contains($field, '.')) {
            $parts = explode('.', $field);
            $cur = $row;
            foreach ($parts as $p) {
                if (!is_array($cur) || !array_key_exists($p, $cur)) {
                    return null;
                }
                $cur = $cur[$p];
            }
            return $cur;
        }
        $snake = PageHelper::toUnderline($field);
        return $row[$snake] ?? null;
    }

    /**
     * 导出文件存放目录
     */
    public static function downloadDir(): string
    {
        $dir = runtime_path() . 'download' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }
}
