<?php
declare(strict_types=1);

namespace app\common;

use think\Response;

/**
 * 表格分页数据响应
 */
class TableDataInfo
{
    /**
     * 构建分页 JSON 响应
     * @param array $rows 当前页数据
     * @param int $total 总记录数
     */
    public static function build(array $rows, int $total, string $msg = 'query ok'): Response
    {
        $rows = self::utf8ize($rows);
        return json([
            'code'  => 0,
            'msg'   => $msg,
            'rows'  => $rows,
            'total' => $total,
        ]);
    }

    /**
     * 构建分页结果数组
     * @param array $rows 当前页数据
     * @param int $total 总记录数
     */
    public static function toArray(array $rows, int $total, string $msg = 'query ok'): array
    {
        return [
            'code'  => 0,
            'msg'   => $msg,
            'rows'  => self::utf8ize($rows),
            'total' => $total,
        ];
    }

    /**
     * 递归校正字符串为 UTF-8
     */
    protected static function utf8ize($data)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $data[$k] = self::utf8ize($v);
            }
            return $data;
        }
        if (is_string($data) && !mb_check_encoding($data, 'UTF-8')) {
            return mb_convert_encoding($data, 'UTF-8', 'UTF-8,GBK,GB2312,ISO-8859-1');
        }
        return $data;
    }
}
