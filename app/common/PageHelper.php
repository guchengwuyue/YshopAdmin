<?php
declare(strict_types=1);

namespace app\common;

/**
 * 分页与命名风格转换
 */
class PageHelper
{
    protected static int $pageNum = 1;
    protected static int $pageSize = 10;
    protected static string $orderBy = '';

    /**
     * 从请求参数初始化分页与排序
     */
    public static function startPage(): void
    {
        $pageNum  = (int) \think\facade\Request::param('pageNum', 1);
        $pageSize = (int) \think\facade\Request::param('pageSize', 10);
        $orderByColumn = (string) \think\facade\Request::param('orderByColumn', '');
        $isAsc = (string) \think\facade\Request::param('isAsc', 'asc');

        self::$pageNum  = max(1, $pageNum);
        self::$pageSize = max(1, min(1000, $pageSize));

        if ($orderByColumn !== '') {
            $column = self::toUnderline($orderByColumn);
            $dir = 'asc';
            if (stripos($isAsc, 'desc') !== false) {
                $dir = 'desc';
            }
            self::$orderBy = $column . ' ' . $dir;
        } else {
            self::$orderBy = '';
        }
    }

    /**
     * 导出用大分页（单次最多 10 万行）
     */
    public static function startExport(): void
    {
        self::startPage();
        self::$pageNum = 1;
        self::$pageSize = 100000;
    }

    /**
     * 对查询应用排序
     */
    public static function apply(\think\db\BaseQuery $query): \think\db\BaseQuery
    {
        if (self::$orderBy !== '') {
            $query->orderRaw(self::$orderBy);
        }
        return $query;
    }

    /**
     * 分页查询，返回 [行数据, 总数]
     */
    public static function paginate(\think\db\BaseQuery $query): array
    {
        self::apply($query);
        $total = (clone $query)->count();
        $rows = $query->page(self::$pageNum, self::$pageSize)->select()->toArray();
        return [$rows, (int) $total];
    }

    /**
     * 当前页码
     */
    public static function getPageNum(): int
    {
        return self::$pageNum;
    }

    /**
     * 每页条数
     */
    public static function getPageSize(): int
    {
        return self::$pageSize;
    }

    /**
     * 驼峰转下划线
     */
    public static function toUnderline(string $name): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name) ?? $name);
    }

    /**
     * 下划线转驼峰
     */
    public static function toCamel(string $name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
    }

    /**
     * 数组键名转为驼峰
     */
    public static function camelKeys(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $camel = is_string($key) ? self::toCamel($key) : $key;
            if (is_array($value)) {
                $result[$camel] = self::camelKeys($value);
            } else {
                $result[$camel] = $value;
            }
        }
        return $result;
    }

    /**
     * 多行数据键名转为驼峰
     */
    public static function camelRows(array $rows): array
    {
        return array_map(static fn($row) => is_array($row) ? self::camelKeys($row) : $row, $rows);
    }

    /**
     * 请求参数键名转为下划线
     */
    public static function snakeParams(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_string($key)) {
                $result[self::toUnderline($key)] = $value;
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
