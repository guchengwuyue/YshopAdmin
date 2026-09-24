<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 代码生成（导入表元数据、预览 / 打包 PHP CRUD 骨架）
 * 产出对齐当前架构：api Controller + Service + admin-ui Vue/API + 路由片段
 * java_type / java_field 列存 PHP 类型 / 驼峰字段（表结构不变）
 */
class GenService
{
    /** 超级列：不参与业务表单 */
    private const SUPER_COLUMNS = ['create_by', 'create_time', 'update_by', 'update_time'];

    /**
     * 查询已导入生成表列表
     * @param array $params 筛选条件（tableName / tableComment）
     */
    public function selectList(array $params = []): array
    {
        PageHelper::startPage();
        $query = Db::name('gen_table');
        $tableName = $params['tableName'] ?? $params['table_name'] ?? '';
        if ($tableName !== '') {
            $query->whereLike('table_name', '%' . $tableName . '%');
        }
        $tableComment = $params['tableComment'] ?? $params['table_comment'] ?? '';
        if ($tableComment !== '') {
            $query->whereLike('table_comment', '%' . $tableComment . '%');
        }
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 查询数据库待导入表列表
     * @param array $params 筛选条件（tableName / tableComment）
     */
    public function selectDbTableList(array $params = []): array
    {
        PageHelper::startPage();
        $dbName = (string) Db::query('select database() as db')[0]['db'];
        $query = Db::table('information_schema.tables')
            ->where('table_schema', $dbName)
            ->whereNotLike('table_name', 'qrtz_%')
            ->whereNotLike('table_name', 'gen_%');
        $imported = Db::name('gen_table')->column('table_name');
        if ($imported) {
            $query->whereNotIn('table_name', $imported);
        }
        $tableName = $params['tableName'] ?? $params['table_name'] ?? '';
        if ($tableName !== '') {
            $query->whereLike('table_name', '%' . $tableName . '%');
        }
        $tableComment = $params['tableComment'] ?? $params['table_comment'] ?? '';
        if ($tableComment !== '') {
            $query->whereLike('table_comment', '%' . $tableComment . '%');
        }
        $query->field('table_name as table_name, table_comment as table_comment, create_time, update_time')
            ->order('create_time', 'desc');
        [$rows, $total] = PageHelper::paginate($query);
        return [PageHelper::camelRows($rows), $total];
    }

    /**
     * 根据表 ID 查询详情（含列配置）
     */
    public function get(int $tableId): ?array
    {
        $row = Db::name('gen_table')->where('table_id', $tableId)->find();
        if (!$row) {
            return null;
        }
        $table = PageHelper::camelKeys($row);
        $cols = Db::name('gen_table_column')->where('table_id', $tableId)->order('sort')->select()->toArray();
        $table['columns'] = PageHelper::camelRows($cols);
        return $table;
    }

    /**
     * 导入表结构
     * @param string $tables 表名，逗号分隔
     */
    public function importTable(string $tables): int
    {
        $names = array_filter(array_map('trim', explode(',', $tables)));
        $count = 0;
        foreach ($names as $name) {
            if ($this->importOne($name)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 导入单张表元数据
     */
    protected function importOne(string $tableName): bool
    {
        $dbName = (string) Db::query('select database() as db')[0]['db'];
        $meta = Db::table('information_schema.tables')
            ->where('table_schema', $dbName)
            ->where('table_name', $tableName)
            ->field('table_name, table_comment')
            ->find();
        if (!$meta) {
            return false;
        }
        $business = $this->toBusinessName($tableName);
        $className = $this->toClassName($tableName);
        $tableId = (int) Db::name('gen_table')->insertGetId([
            'table_name'      => $tableName,
            'table_comment'   => $meta['table_comment'] ?: $tableName,
            'class_name'      => $className,
            'tpl_category'    => 'crud',
            'package_name'    => 'app',
            'module_name'     => 'system',
            'business_name'   => $business,
            'function_name'   => $meta['table_comment'] ?: $business,
            'function_author' => Auth::getLoginName() ?: 'yshop',
            'gen_type'        => '0',
            'gen_path'        => '/',
            'create_by'       => Auth::getLoginName(),
            'create_time'     => date('Y-m-d H:i:s'),
        ]);

        $columns = Db::table('information_schema.columns')
            ->where('table_schema', $dbName)
            ->where('table_name', $tableName)
            ->order('ordinal_position')
            ->select()
            ->toArray();

        $sort = 1;
        foreach ($columns as $col) {
            $columnName = $col['COLUMN_NAME'] ?? $col['column_name'];
            $columnType = $col['COLUMN_TYPE'] ?? $col['column_type'];
            $comment = $col['COLUMN_COMMENT'] ?? $col['column_comment'] ?? '';
            $isPk = strtoupper((string) ($col['COLUMN_KEY'] ?? $col['column_key'] ?? '')) === 'PRI' ? '1' : '0';
            $isInc = str_contains(strtolower((string) ($col['EXTRA'] ?? $col['extra'] ?? '')), 'auto_increment') ? '1' : '0';
            $nullable = strtoupper((string) ($col['IS_NULLABLE'] ?? $col['is_nullable'] ?? 'YES')) === 'NO' ? '1' : '0';
            $phpType = $this->mapPhpType((string) $columnType);
            $javaField = PageHelper::toCamel((string) $columnName);
            $super = in_array($columnName, self::SUPER_COLUMNS, true);
            $isDelFlag = $columnName === 'del_flag';
            $htmlType = $this->mapHtmlType((string) $columnType, (string) $columnName);
            $queryType = $this->defaultQueryType((string) $columnName, $htmlType);
            $isQuery = $this->defaultIsQuery((string) $columnName, $isPk === '1', $super || $isDelFlag) ? '1' : '0';

            Db::name('gen_table_column')->insert([
                'table_id'       => $tableId,
                'column_name'    => $columnName,
                'column_comment' => $comment,
                'column_type'    => $columnType,
                'java_type'      => $phpType,
                'java_field'     => $javaField,
                'is_pk'          => $isPk,
                'is_increment'   => $isInc,
                'is_required'    => $isPk === '1' || $isDelFlag ? '0' : $nullable,
                'is_insert'      => ($isPk === '1' && $isInc === '1') || $super || $isDelFlag ? '0' : '1',
                'is_edit'        => $isPk === '1' || $super || $isDelFlag ? '0' : '1',
                'is_list'        => ($super && $columnName !== 'remark') || $isDelFlag ? '0' : '1',
                'is_query'       => $isQuery,
                'query_type'     => $queryType,
                'html_type'      => $htmlType,
                'dict_type'      => '',
                'sort'           => $sort++,
                'create_by'      => Auth::getLoginName(),
                'create_time'    => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    }

    /**
     * 更新生成配置与列设置
     */
    public function update(array $data): int
    {
        $data = PageHelper::snakeParams($data);
        $tableId = (int) ($data['table_id'] ?? 0);
        $n = Db::name('gen_table')->where('table_id', $tableId)->update([
            'table_comment'   => $data['table_comment'] ?? '',
            'class_name'      => $data['class_name'] ?? '',
            'package_name'    => $data['package_name'] ?? 'app',
            'module_name'     => $data['module_name'] ?? 'system',
            'business_name'   => $data['business_name'] ?? '',
            'function_name'   => $data['function_name'] ?? '',
            'function_author' => $data['function_author'] ?? '',
            'gen_type'        => $data['gen_type'] ?? '0',
            'gen_path'        => $data['gen_path'] ?? '/',
            'remark'          => $data['remark'] ?? '',
            'update_by'       => Auth::getLoginName(),
            'update_time'     => date('Y-m-d H:i:s'),
        ]);
        $columns = $data['columns'] ?? [];
        if (is_string($columns)) {
            $columns = json_decode($columns, true) ?: [];
        }
        foreach ($columns as $col) {
            $col = PageHelper::snakeParams($col);
            $cid = (int) ($col['column_id'] ?? 0);
            if ($cid <= 0) {
                continue;
            }
            Db::name('gen_table_column')->where('column_id', $cid)->update([
                'column_comment' => $col['column_comment'] ?? '',
                'java_type'      => $col['java_type'] ?? 'string',
                'java_field'     => $col['java_field'] ?? '',
                'is_insert'      => $col['is_insert'] ?? '0',
                'is_edit'        => $col['is_edit'] ?? '0',
                'is_list'        => $col['is_list'] ?? '0',
                'is_query'       => $col['is_query'] ?? '0',
                'is_required'    => $col['is_required'] ?? '0',
                'query_type'     => $col['query_type'] ?? 'EQ',
                'html_type'      => $col['html_type'] ?? 'input',
                'dict_type'      => $col['dict_type'] ?? '',
            ]);
        }
        return $n;
    }

    /**
     * 删除已导入表（含列）
     * @param string $ids 表 ID，逗号分隔
     */
    public function delete(string $ids): int
    {
        $idArr = array_filter(array_map('intval', explode(',', $ids)));
        if (!$idArr) {
            return 0;
        }
        Db::name('gen_table_column')->whereIn('table_id', $idArr)->delete();
        return Db::name('gen_table')->whereIn('table_id', $idArr)->delete();
    }

    /**
     * 预览生成代码
     * @return array<string, string> 路径 => 内容
     */
    public function preview(int $tableId): array
    {
        $table = $this->get($tableId);
        if (!$table) {
            throw new \RuntimeException('table not found');
        }
        return $this->renderFiles($table);
    }

    /**
     * 打包下载生成代码
     * @param string $tableIds 表 ID，逗号分隔
     * @return string 压缩文件名
     */
    public function downloadZip(string $tableIds): string
    {
        $ids = array_filter(array_map('intval', explode(',', $tableIds)));
        $zipPath = runtime_path() . 'download' . DIRECTORY_SEPARATOR;
        if (!is_dir($zipPath)) {
            mkdir($zipPath, 0755, true);
        }
        $fileName = 'gen_' . date('YmdHis') . '_' . bin2hex(random_bytes(3)) . '.zip';
        $full = $zipPath . $fileName;
        $zip = new \ZipArchive();
        if ($zip->open($full, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('cannot create zip');
        }
        foreach ($ids as $id) {
            $table = $this->get($id);
            if (!$table) {
                continue;
            }
            foreach ($this->renderFiles($table) as $path => $content) {
                $zip->addFromString($path, $content);
            }
        }
        $zip->close();
        return $fileName;
    }

    /**
     * 渲染单表全部生成文件（对齐 api + admin-ui 架构）
     * @param array $table 表配置（含 columns）
     */
    protected function renderFiles(array $table): array
    {
        $module = $table['moduleName'] ?? 'system';
        $business = $table['businessName'] ?? 'demo';
        $className = $table['className'] ?? 'Demo';
        $functionName = $table['functionName'] ?? $business;
        $columns = $table['columns'] ?? [];
        $pk = 'id';
        $pkField = 'id';
        $hasDelFlag = false;
        $hasCreateBy = false;
        $hasRemark = false;
        foreach ($columns as $c) {
            $cn = $c['columnName'] ?? '';
            if (($c['isPk'] ?? '') === '1') {
                $pk = $cn;
                $pkField = $c['javaField'] ?? PageHelper::toCamel($cn);
            }
            if ($cn === 'del_flag') {
                $hasDelFlag = true;
            }
            if ($cn === 'create_by') {
                $hasCreateBy = true;
            }
            if ($cn === 'remark') {
                $hasRemark = true;
            }
        }
        $listCols = array_values(array_filter($columns, static fn($c) => ($c['isList'] ?? '') === '1'));
        $queryCols = array_values(array_filter($columns, static fn($c) => ($c['isQuery'] ?? '') === '1'));
        $insertCols = array_values(array_filter($columns, static fn($c) => ($c['isInsert'] ?? '') === '1'));
        $editCols = array_values(array_filter($columns, static fn($c) => ($c['isEdit'] ?? '') === '1'));
        $formCols = [];
        $seen = [];
        foreach (array_merge($insertCols, $editCols) as $col) {
            $f = $col['javaField'] ?? '';
            if ($f === '' || isset($seen[$f])) {
                continue;
            }
            $seen[$f] = true;
            $formCols[] = $col;
        }

        $vars = compact(
            'module', 'business', 'className', 'functionName', 'columns',
            'pk', 'pkField', 'listCols', 'queryCols', 'insertCols', 'editCols', 'formCols',
            'table', 'hasDelFlag', 'hasCreateBy', 'hasRemark'
        );
        $vars['serviceVar'] = lcfirst($className) . 'Service';
        $vars['perm'] = "{$module}:{$business}";

        return [
            "app/controller/api/{$module}/{$className}.php"           => $this->tplController($vars),
            "app/service/{$className}Service.php"                     => $this->tplService($vars),
            "admin-ui/src/api/{$module}/{$business}.js"                => $this->tplApiJs($vars),
            "admin-ui/src/views/{$module}/{$business}/index.vue"       => $this->tplVue($vars),
            "docs/gen/{$module}_{$business}_route.php.txt"            => $this->tplRoute($vars),
            "docs/gen/{$module}_{$business}_menu.sql.txt"             => $this->tplMenuSql($vars),
        ];
    }

    /**
     * 生成 api Controller
     */
    protected function tplController(array $v): string
    {
        $m = $v['module'];
        $c = $v['className'];
        $fn = $v['functionName'];
        $sv = $v['serviceVar'];
        return <<<PHP
<?php
declare(strict_types=1);

namespace app\\controller\\api\\{$m};

use app\\controller\\BaseController;
use app\\service\\{$c}Service;

/**
 * {$fn} JSON API
 */
class {$c} extends BaseController
{
    protected {$c}Service \${$sv};

    protected function initialize()
    {
        \$this->{$sv} = new {$c}Service();
    }

    public function list()
    {
        [\$rows, \$total] = \$this->{$sv}->selectList(\$this->request->param());
        return \$this->getDataTable(\$rows, \$total);
    }

    public function get(\$id = 0)
    {
        \$row = \$this->{$sv}->get((int) (\$id ?: \$this->request->param('id', 0)));
        if (!\$row) {
            return \$this->error('{$fn}不存在');
        }
        return \$this->success('success', \$row);
    }

    public function add()
    {
        try {
            \$id = \$this->{$sv}->insert(\$this->request->param());
            return \$this->toAjax(\$id > 0 ? 1 : 0);
        } catch (\\Throwable \$e) {
            return \$this->error(\$e->getMessage());
        }
    }

    public function edit()
    {
        try {
            return \$this->toAjax(\$this->{$sv}->update(\$this->request->param()));
        } catch (\\Throwable \$e) {
            return \$this->error(\$e->getMessage());
        }
    }

    public function remove()
    {
        try {
            return \$this->toAjax(\$this->{$sv}->delete((string) \$this->request->param('ids', '')));
        } catch (\\Throwable \$e) {
            return \$this->error(\$e->getMessage());
        }
    }
}
PHP;
    }

    /**
     * 生成 Service
     */
    protected function tplService(array $v): string
    {
        $c = $v['className'];
        $fn = $v['functionName'];
        $tableName = $v['table']['tableName'];
        $pk = $v['pk'];
        $hasDelFlag = $v['hasDelFlag'];
        $hasCreateBy = $v['hasCreateBy'];

        $queryBlocks = '';
        if ($hasDelFlag) {
            $queryBlocks .= "        \$query = Db::name('{$tableName}')->where('del_flag', '0');\n";
        } else {
            $queryBlocks .= "        \$query = Db::name('{$tableName}');\n";
        }
        foreach ($v['queryCols'] as $col) {
            $field = $col['javaField'];
            $colName = $col['columnName'];
            $qt = strtoupper((string) ($col['queryType'] ?? 'EQ'));
            $queryBlocks .= "        \${$field} = \$params['{$field}'] ?? \$params['{$colName}'] ?? '';\n";
            if ($qt === 'LIKE') {
                $queryBlocks .= "        if (\${$field} !== '' && \${$field} !== null) {\n            \$query->whereLike('{$colName}', '%' . \${$field} . '%');\n        }\n";
            } else {
                $queryBlocks .= "        if (\${$field} !== '' && \${$field} !== null) {\n            \$query->where('{$colName}', \${$field});\n        }\n";
            }
        }

        $insertFields = '';
        foreach ($v['insertCols'] as $col) {
            $cn = $col['columnName'];
            $default = $this->phpDefaultExpr($col);
            $insertFields .= "            '{$cn}' => \$data['{$cn}'] ?? {$default},\n";
        }
        if ($hasDelFlag) {
            $insertFields .= "            'del_flag'    => '0',\n";
        }
        if ($hasCreateBy) {
            $insertFields .= "            'create_by'   => Auth::getLoginName(),\n";
            $insertFields .= "            'create_time' => date('Y-m-d H:i:s'),\n";
        }

        $updateFields = '';
        foreach ($v['editCols'] as $col) {
            $cn = $col['columnName'];
            $default = $this->phpDefaultExpr($col);
            $updateFields .= "            '{$cn}' => \$data['{$cn}'] ?? {$default},\n";
        }
        if ($hasCreateBy) {
            $updateFields .= "            'update_by'   => Auth::getLoginName(),\n";
            $updateFields .= "            'update_time' => date('Y-m-d H:i:s'),\n";
        }

        if ($hasDelFlag) {
            $softFields = "            'del_flag' => '2',\n";
            if ($hasCreateBy) {
                $softFields .= "            'update_by'   => Auth::getLoginName(),\n";
                $softFields .= "            'update_time' => date('Y-m-d H:i:s'),\n";
            }
            $deleteBody = <<<PHP
        \$idArr = array_filter(array_map('intval', explode(',', \$ids)));
        if (!\$idArr) {
            return 0;
        }
        return Db::name('{$tableName}')->whereIn('{$pk}', \$idArr)->where('del_flag', '0')->update([
{$softFields}        ]);
PHP;
            $getWhere = "->where('{$pk}', \$id)->where('del_flag', '0')";
        } else {
            $deleteBody = <<<PHP
        \$idArr = array_filter(array_map('intval', explode(',', \$ids)));
        if (!\$idArr) {
            return 0;
        }
        return Db::name('{$tableName}')->whereIn('{$pk}', \$idArr)->delete();
PHP;
            $getWhere = "->where('{$pk}', \$id)";
        }

        $authUse = ($hasCreateBy || $hasDelFlag) ? "use app\\common\\Auth;\n" : '';

        return <<<PHP
<?php
declare(strict_types=1);

namespace app\\service;

{$authUse}use app\\common\\PageHelper;
use think\\facade\\Db;

/**
 * {$fn}
 */
class {$c}Service
{
    /**
     * 查询列表
     * @param array \$params 筛选条件
     */
    public function selectList(array \$params = []): array
    {
        PageHelper::startPage();
{$queryBlocks}        [\$rows, \$total] = PageHelper::paginate(\$query);
        return [PageHelper::camelRows(\$rows), \$total];
    }

    /**
     * 根据主键查询详情
     */
    public function get(int \$id): ?array
    {
        \$row = Db::name('{$tableName}'){$getWhere}->find();
        return \$row ? PageHelper::camelKeys(\$row) : null;
    }

    /**
     * 新增
     */
    public function insert(array \$data): int
    {
        \$data = PageHelper::snakeParams(\$data);
        \$row = [
{$insertFields}        ];
        return (int) Db::name('{$tableName}')->insertGetId(\$row);
    }

    /**
     * 修改
     */
    public function update(array \$data): int
    {
        \$data = PageHelper::snakeParams(\$data);
        \$id = (int) (\$data['{$pk}'] ?? 0);
        return Db::name('{$tableName}')->where('{$pk}', \$id)->update([
{$updateFields}        ]);
    }

    /**
     * 删除
     * @param string \$ids 主键，逗号分隔
     */
    public function delete(string \$ids): int
    {
{$deleteBody}
    }
}
PHP;
    }

    /**
     * 生成前端 API 模块
     */
    protected function tplApiJs(array $v): string
    {
        $m = $v['module'];
        $b = $v['business'];
        $c = $v['className'];
        return <<<JS
import request from '@/utils/request'

export function list{$c}(params) {
  return request({ url: '/api/{$m}/{$b}/list', method: 'get', params })
}

export function get{$c}(id) {
  return request({ url: '/api/{$m}/{$b}/get/' + id, method: 'get' })
}

export function add{$c}(data) {
  return request({ url: '/api/{$m}/{$b}/add', method: 'post', data })
}

export function update{$c}(data) {
  return request({ url: '/api/{$m}/{$b}/edit', method: 'post', data })
}

export function remove{$c}(ids) {
  return request({ url: '/api/{$m}/{$b}/remove', method: 'post', data: { ids } })
}
JS;
    }

    /**
     * 生成 admin-ui Vue 页面
     */
    protected function tplVue(array $v): string
    {
        $m = $v['module'];
        $b = $v['business'];
        $c = $v['className'];
        $fn = $v['functionName'];
        $pkField = $v['pkField'];
        $perm = $v['perm'];

        $searchItems = '';
        $queryDefaults = "pageNum: 1, pageSize: 10";
        $resetLines = '';
        foreach ($v['queryCols'] as $col) {
            $label = $this->esc((string) ($col['columnComment'] ?: $col['javaField']));
            $field = $col['javaField'];
            $htmlType = $col['htmlType'] ?? 'input';
            $queryDefaults .= ", {$field}: " . ($htmlType === 'select' || $htmlType === 'radio' ? 'undefined' : "''");
            $resetLines .= "  query.{$field} = " . ($htmlType === 'select' || $htmlType === 'radio' ? 'undefined' : "''") . "\n";
            if ($htmlType === 'select' || $htmlType === 'radio') {
                $searchItems .= "      <a-form-item label=\"{$label}\">\n        <a-select v-model:value=\"query.{$field}\" allow-clear style=\"width: 120px\" :options=\"[]\" placeholder=\"请选择\" />\n      </a-form-item>\n";
            } else {
                $searchItems .= "      <a-form-item label=\"{$label}\">\n        <a-input v-model:value=\"query.{$field}\" allow-clear />\n      </a-form-item>\n";
            }
        }

        $columnsJs = '';
        foreach ($v['listCols'] as $col) {
            $title = $this->esc((string) ($col['columnComment'] ?: $col['javaField']));
            $field = $col['javaField'];
            $width = '';
            if (($col['isPk'] ?? '') === '1' || in_array($field, ['status', 'sort', 'orderNum'], true)) {
                $width = ', width: 100';
            } elseif ($field === 'createTime' || $field === 'updateTime') {
                $width = ', width: 170';
            }
            $columnsJs .= "  { title: '{$title}', dataIndex: '{$field}'{$width} },\n";
        }
        $columnsJs .= "  { title: '操作', key: 'action', width: 140 },\n";

        $formItems = '';
        $emptyFields = "{$pkField}: undefined";
        $requiredChecks = [];
        foreach ($v['formCols'] as $col) {
            $label = $this->esc((string) ($col['columnComment'] ?: $col['javaField']));
            $field = $col['javaField'];
            $htmlType = $col['htmlType'] ?? 'input';
            $required = ($col['isRequired'] ?? '') === '1';
            $reqAttr = $required ? ' required' : '';
            $emptyFields .= ', ' . $field . ': ' . $this->jsDefaultExpr($col);
            if ($required) {
                $requiredChecks[] = "!form.{$field}";
            }
            $formItems .= match ($htmlType) {
                'textarea' => "        <a-form-item label=\"{$label}\"{$reqAttr}><a-textarea v-model:value=\"form.{$field}\" :rows=\"2\" /></a-form-item>\n",
                'select' => "        <a-form-item label=\"{$label}\"{$reqAttr}><a-select v-model:value=\"form.{$field}\" allow-clear :options=\"[]\" placeholder=\"请选择\" /></a-form-item>\n",
                'radio' => "        <a-form-item label=\"{$label}\"{$reqAttr}><a-radio-group v-model:value=\"form.{$field}\" :options=\"[]\" /></a-form-item>\n",
                'datetime' => "        <a-form-item label=\"{$label}\"{$reqAttr}><a-input v-model:value=\"form.{$field}\" placeholder=\"YYYY-MM-DD HH:mm:ss\" /></a-form-item>\n",
                default => (($col['javaType'] ?? '') === 'int' || ($col['javaType'] ?? '') === 'float')
                    ? "        <a-form-item label=\"{$label}\"{$reqAttr}><a-input-number v-model:value=\"form.{$field}\" style=\"width: 100%\" /></a-form-item>\n"
                    : "        <a-form-item label=\"{$label}\"{$reqAttr}><a-input v-model:value=\"form.{$field}\" /></a-form-item>\n",
            };
        }

        $requiredJs = $requiredChecks
            ? "  if (" . implode(' || ', $requiredChecks) . ") {\n    message.warning('请填写必填项')\n    return\n  }\n"
            : '';

        $assignEdit = '';
        foreach ($v['formCols'] as $col) {
            $field = $col['javaField'];
            $jt = $col['javaType'] ?? 'string';
            if (($col['isPk'] ?? '') === '1') {
                continue;
            }
            if ($jt === 'int' || $jt === 'float') {
                $assignEdit .= "    {$field}: Number(row.{$field} || 0),\n";
            } else {
                $assignEdit .= "    {$field}: row.{$field} ?? '',\n";
            }
        }

        return <<<VUE
<template>
  <div class="page-stack">
    <div class="page-card">
    <a-form layout="inline" :model="query" class="search">
{$searchItems}      <a-form-item>
        <a-button type="primary" @click="search">搜索</a-button>
        <a-button style="margin-left: 8px" @click="resetQuery">重置</a-button>
      </a-form-item>
    </a-form>
    </div>
    <div class="page-card">
    <div class="toolbar">
      <a-space>
        <a-button v-if="can('{$perm}:add')" type="primary" @click="openAdd">新增</a-button>
        <a-button v-if="can('{$perm}:remove')" danger :disabled="!selected.length" @click="onRemove()">删除</a-button>
      </a-space>
    </div>
    <a-table
      row-key="{$pkField}"
      :columns="columns"
      :data-source="rows"
      :loading="loading"
      :pagination="pagination"
      :row-selection="{ selectedRowKeys: selected, onChange: (keys) => (selected = keys) }"
      @change="onTableChange"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'action'">
          <a-space>
            <a v-if="can('{$perm}:edit')" @click="openEdit(record)">编辑</a>
            <a v-if="can('{$perm}:remove')" @click="onRemove(record)">删除</a>
          </a-space>
        </template>
      </template>
    </a-table>

    <a-modal v-model:open="open" :title="form.{$pkField} ? '编辑{$fn}' : '新增{$fn}'" :confirm-loading="saving" @ok="submit">
      <a-form :model="form" :label-col="{ span: 6 }" :wrapper-col="{ span: 16 }">
{$formItems}      </a-form>
    </a-modal>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { Modal, message } from 'ant-design-vue'
import { useUserStore } from '@/store/user'
import { add{$c}, get{$c}, list{$c}, remove{$c}, update{$c} } from '@/api/{$m}/{$b}'
import { num } from '@/utils/tree'

const userStore = useUserStore()
const can = (perm) => userStore.hasPerm(perm)
const loading = ref(false)
const saving = ref(false)
const rows = ref([])
const selected = ref([])
const open = ref(false)
const query = reactive({ {$queryDefaults} })
const pagination = reactive({ current: 1, pageSize: 10, total: 0, showSizeChanger: true, showTotal: (total) => `共 \${total} 条` })
const form = reactive(emptyForm())
const columns = [
{$columnsJs}]

function emptyForm() {
  return { {$emptyFields} }
}

async function load() {
  loading.value = true
  try {
    const res = await list{$c}(query)
    rows.value = res.rows || []
    pagination.total = res.total || 0
    pagination.current = query.pageNum
    pagination.pageSize = query.pageSize
  } finally {
    loading.value = false
  }
}

function search() {
  query.pageNum = 1
  load()
}

function resetQuery() {
{$resetLines}  search()
}

function onTableChange(page) {
  query.pageNum = page.current
  query.pageSize = page.pageSize
  load()
}

function openAdd() {
  Object.assign(form, emptyForm())
  open.value = true
}

async function openEdit(record) {
  const res = await get{$c}(record.{$pkField})
  const row = res.data || {}
  Object.assign(form, emptyForm(), {
    {$pkField}: num(row.{$pkField}),
{$assignEdit}  })
  open.value = true
}

async function submit() {
{$requiredJs}  saving.value = true
  try {
    if (form.{$pkField}) {
      await update{$c}(form)
    } else {
      await add{$c}(form)
    }
    message.success('保存成功')
    open.value = false
    load()
  } finally {
    saving.value = false
  }
}

function onRemove(record) {
  const ids = record ? String(record.{$pkField}) : selected.value.join(',')
  Modal.confirm({
    title: '确认删除所选{$fn}？',
    onOk: async () => {
      await remove{$c}(ids)
      message.success('删除成功')
      selected.value = []
      load()
    },
  })
}

onMounted(load)
</script>
VUE;
    }

    /**
     * 生成路由片段（需手工合并到 route/app.php）
     */
    protected function tplRoute(array $v): string
    {
        $m = $v['module'];
        $b = $v['business'];
        $c = $v['className'];
        $fn = $v['functionName'];
        $perm = $v['perm'];
        return <<<PHP
// ---- {$fn}（粘贴到 route/app.php 的 /api 分组内）----
Route::get('{$m}/{$b}/list', 'api.{$m}.{$c}/list')->middleware(\\app\\middleware\\Permission::class)->option(['perms' => '{$perm}:list']);
Route::get('{$m}/{$b}/get/:id', 'api.{$m}.{$c}/get')->middleware(\\app\\middleware\\Permission::class)->option(['perms' => '{$perm}:edit']);
Route::post('{$m}/{$b}/add', 'api.{$m}.{$c}/add')->middleware([\\app\\middleware\\Permission::class, \\app\\middleware\\OperLog::class])->option(['perms' => '{$perm}:add', 'title' => '{$fn}']);
Route::post('{$m}/{$b}/edit', 'api.{$m}.{$c}/edit')->middleware([\\app\\middleware\\Permission::class, \\app\\middleware\\OperLog::class])->option(['perms' => '{$perm}:edit', 'title' => '{$fn}']);
Route::post('{$m}/{$b}/remove', 'api.{$m}.{$c}/remove')->middleware([\\app\\middleware\\Permission::class, \\app\\middleware\\OperLog::class])->option(['perms' => '{$perm}:remove', 'title' => '{$fn}']);
PHP;
    }

    /**
     * 生成菜单 SQL 片段（需按实际 parent_id 调整）
     */
    protected function tplMenuSql(array $v): string
    {
        $m = $v['module'];
        $b = $v['business'];
        $fn = $v['functionName'];
        $perm = $v['perm'];
        $path = "/{$m}/{$b}";
        $component = "{$m}/{$b}/index";
        return <<<SQL
-- {$fn} 菜单（请按实际 parent_id / menu_id 调整后执行）
-- 目录/菜单
INSERT INTO sys_menu (menu_name, parent_id, order_num, path, component, is_frame, is_cache, menu_type, visible, status, perms, icon, create_by, create_time, remark)
VALUES ('{$fn}', 1, 10, '{$b}', '{$component}', 1, 0, 'C', '0', '0', '{$perm}:list', 'form', 'admin', NOW(), '');
-- 按钮（parent_id 改为上一条菜单 ID）
-- INSERT INTO sys_menu (menu_name, parent_id, order_num, path, component, is_frame, is_cache, menu_type, visible, status, perms, icon, create_by, create_time)
-- VALUES ('{$fn}查询', @pid, 1, '', '', 1, 0, 'F', '0', '0', '{$perm}:list', '#', 'admin', NOW()),
--        ('{$fn}新增', @pid, 2, '', '', 1, 0, 'F', '0', '0', '{$perm}:add', '#', 'admin', NOW()),
--        ('{$fn}修改', @pid, 3, '', '', 1, 0, 'F', '0', '0', '{$perm}:edit', '#', 'admin', NOW()),
--        ('{$fn}删除', @pid, 4, '', '', 1, 0, 'F', '0', '0', '{$perm}:remove', '#', 'admin', NOW());
-- 前端路由 path 参考: {$path}  component: {$component}
SQL;
    }

    /**
     * 表名转业务名（小驼峰，去掉常见前缀）
     */
    protected function toBusinessName(string $table): string
    {
        $name = preg_replace('/^(sys_|gen_)/', '', $table) ?? $table;
        return PageHelper::toCamel($name);
    }

    /**
     * 表名转类名
     */
    protected function toClassName(string $table): string
    {
        $name = preg_replace('/^(sys_|gen_)/', '', $table) ?? $table;
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    /**
     * 列类型映射为 PHP 类型
     */
    protected function mapPhpType(string $columnType): string
    {
        $t = strtolower($columnType);
        if (str_contains($t, 'bigint') || str_contains($t, 'int')) {
            return 'int';
        }
        if (str_contains($t, 'decimal') || str_contains($t, 'double') || str_contains($t, 'float')) {
            return 'float';
        }
        return 'string';
    }

    /**
     * 列类型映射为表单控件类型
     */
    protected function mapHtmlType(string $columnType, string $columnName): string
    {
        $t = strtolower($columnType);
        $n = strtolower($columnName);
        if (str_contains($t, 'text')) {
            return 'textarea';
        }
        if ($n === 'status' || str_ends_with($n, '_status') || $n === 'sex' || $n === 'type' || str_ends_with($n, '_type')) {
            return 'select';
        }
        if (str_contains($t, 'datetime') || str_contains($t, 'timestamp') || str_contains($t, 'date')) {
            return 'datetime';
        }
        return 'input';
    }

    /**
     * 导入时默认是否作为查询列
     */
    protected function defaultIsQuery(string $columnName, bool $isPk, bool $skip): bool
    {
        if ($isPk || $skip) {
            return false;
        }
        $n = strtolower($columnName);
        foreach (['name', 'title', 'status', 'type', 'code', 'phone', 'mobile', 'email'] as $kw) {
            if ($n === $kw || str_ends_with($n, '_' . $kw) || str_contains($n, $kw)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 导入时默认查询方式
     */
    protected function defaultQueryType(string $columnName, string $htmlType): string
    {
        if ($htmlType === 'select' || $htmlType === 'radio') {
            return 'EQ';
        }
        $n = strtolower($columnName);
        if (str_contains($n, 'status') || str_contains($n, 'type') || str_ends_with($n, '_id')) {
            return 'EQ';
        }
        return 'LIKE';
    }

    /**
     * PHP 默认值表达式
     */
    protected function phpDefaultExpr(array $col): string
    {
        $jt = $col['javaType'] ?? 'string';
        if ($jt === 'int') {
            return '0';
        }
        if ($jt === 'float') {
            return '0';
        }
        $cn = $col['columnName'] ?? '';
        if ($cn === 'status') {
            return "'0'";
        }
        return "''";
    }

    /**
     * JS 表单默认值表达式
     */
    protected function jsDefaultExpr(array $col): string
    {
        $jt = $col['javaType'] ?? 'string';
        if ($jt === 'int' || $jt === 'float') {
            return '0';
        }
        $cn = $col['columnName'] ?? '';
        if ($cn === 'status') {
            return "'0'";
        }
        return "''";
    }

    /**
     * 转义单引号（模板字符串安全）
     */
    protected function esc(string $s): string
    {
        return str_replace(["\\", "'"], ["\\\\", "\\'"], $s);
    }
}
