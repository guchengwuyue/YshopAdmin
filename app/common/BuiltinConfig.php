<?php
declare(strict_types=1);

namespace app\common;

/**
 * 系统内置模块配置（支付/微信等，不走插件目录）
 */
class BuiltinConfig
{
    /**
     * 读取 schema（config/builtin/{name}.php）
     */
    public static function schema(string $name): array
    {
        $file = root_path() . 'config' . DIRECTORY_SEPARATOR . 'builtin' . DIRECTORY_SEPARATOR . $name . '.php';
        if (!is_file($file)) {
            return [];
        }
        $schema = include $file;
        return is_array($schema) ? $schema : [];
    }

    /**
     * 扁平配置；优先 data/builtin/{name}/config.php，否则 schema 默认值
     */
    public static function get(string $name): array
    {
        $dataFile = self::dataFile($name);
        if (is_file($dataFile)) {
            $values = include $dataFile;
            if (is_array($values)) {
                return $values;
            }
        }
        return self::schemaToFlat(self::schema($name));
    }

    /**
     * 写入 data/builtin/{name}/config.php
     */
    public static function set(string $name, array $config): bool
    {
        $dir = dirname(self::dataFile($name));
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $content = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($config, true) . ";\n";
        return file_put_contents(self::dataFile($name), $content) !== false;
    }

    /**
     * 支付证书目录（末尾含分隔符）
     */
    public static function certsDir(): string
    {
        $dir = root_path() . 'data' . DIRECTORY_SEPARATOR . 'certs' . DIRECTORY_SEPARATOR . 'pay' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * schema 转扁平 name => value
     */
    public static function schemaToFlat(array $schema): array
    {
        $flat = [];
        if ($schema === []) {
            return $flat;
        }
        if (array_is_list($schema) === false && !isset($schema[0]['name'])) {
            return $schema;
        }
        foreach ($schema as $item) {
            if (!is_array($item) || !isset($item['name'])) {
                continue;
            }
            $flat[$item['name']] = $item['value'] ?? '';
        }
        return $flat;
    }

    private static function dataFile(string $name): string
    {
        return root_path() . 'data' . DIRECTORY_SEPARATOR . 'builtin' . DIRECTORY_SEPARATOR
            . $name . DIRECTORY_SEPARATOR . 'config.php';
    }
}
