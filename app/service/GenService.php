<?php
declare(strict_types=1);

namespace app\service;

use app\common\Auth;
use app\common\PageHelper;
use think\facade\Db;

/**
 * 代码生成（导入表元数据、预览 / 打包 PHP CRUD 骨架）
 * java_type / java_field 列存 PHP 类型 / 驼峰字段（表结构不变）
 */
class GenService
{
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
            $super = in_array($columnName, ['create_by', 'create_time', 'update_by', 'update_time', 'remark'], true);

            Db::name('gen_table_column')->insert([
                'table_id'       => $tableId,
                'column_name'    => $columnName,
                'column_comment' => $comment,
                'column_type'    => $columnType,
                'java_type'      => $phpType,
                'java_field'     => $javaField,
                'is_pk'          => $isPk,
                'is_increment'   => $isInc,
                'is_required'    => $isPk === '1' ? '0' : $nullable,
                'is_insert'      => ($isPk === '1' && $isInc === '1') || $super ? '0' : '1',
                'is_edit'        => $isPk === '1' || $super ? '0' : '1',
                'is_list'        => $super && $columnName !== 'remark' ? '0' : '1',
                'is_query'       => '0',
                'query_type'     => 'EQ',
                'html_type'      => $this->mapHtmlType((string) $columnType, (string) $columnName),
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
     * @return string 临时文件名
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
     * 渲染单表全部生成文件
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
        foreach ($columns as $c) {
            if (($c['isPk'] ?? '') === '1') {
                $pk = $c['columnName'];
                $pkField = $c['javaField'];
                break;
            }
        }
        $listCols = array_filter($columns, static fn($c) => ($c['isList'] ?? '') === '1');
        $queryCols = array_filter($columns, static fn($c) => ($c['isQuery'] ?? '') === '1');
        $insertCols = array_filter($columns, static fn($c) => ($c['isInsert'] ?? '') === '1');
        $editCols = array_filter($columns, static fn($c) => ($c['isEdit'] ?? '') === '1');

        $vars = compact('module', 'business', 'className', 'functionName', 'columns', 'pk', 'pkField', 'listCols', 'queryCols', 'insertCols', 'editCols', 'table');

        return [
            "app/controller/{$module}/{$className}.php" => $this->tplController($vars),
            "app/service/{$className}Service.php"       => $this->tplService($vars),
            "view/{$module}/{$business}/{$business}.html" => $this->tplList($vars),
            "view/{$module}/{$business}/add.html"       => $this->tplForm($vars, false),
            "view/{$module}/{$business}/edit.html"      => $this->tplForm($vars, true),
        ];
    }

    /**
     * 生成控制器模板
     */
    protected function tplController(array $v): string
    {
        $m = $v['module'];
        $c = $v['className'];
        $b = $v['business'];
        $pk = $v['pkField'];
        return <<<PHP
<?php
declare(strict_types=1);

namespace app\\controller\\{$m};

use app\\controller\\BaseController;
use app\\service\\{$c}Service;
use think\\facade\\View;

class {$c} extends BaseController
{
    protected {$c}Service \$service;

    protected function initialize()
    {
        \$this->service = new {$c}Service();
    }

    public function index()
    {
        return View::fetch('{$b}/{$b}');
    }

    public function list()
    {
        [\$rows, \$total] = \$this->service->selectList(\$this->request->param());
        return \$this->getDataTable(\$rows, \$total);
    }

    public function add()
    {
        if (\$this->request->isPost()) {
            return \$this->toAjax(\$this->service->insert(\$this->request->post()) > 0 ? 1 : 0);
        }
        return View::fetch('{$b}/add');
    }

    public function edit(\$id = 0)
    {
        if (\$this->request->isPost()) {
            return \$this->toAjax(\$this->service->update(\$this->request->post()));
        }
        return View::fetch('{$b}/edit', ['row' => \$this->service->get((int) \$id)]);
    }

    public function remove()
    {
        return \$this->toAjax(\$this->service->delete((string) \$this->request->post('ids', '')));
    }
}
PHP;
    }

    /**
     * 生成 Service 模板
     */
    protected function tplService(array $v): string
    {
        $c = $v['className'];
        $tableName = $v['table']['tableName'];
        $pk = $v['pk'];
        $queryBlocks = '';
        foreach ($v['queryCols'] as $col) {
            $field = $col['javaField'];
            $colName = $col['columnName'];
            $queryBlocks .= "        \${$field} = \$params['{$field}'] ?? \$params['{$colName}'] ?? '';\n";
            $queryBlocks .= "        if (\${$field} !== '') {\n            \$query->whereLike('{$colName}', '%' . \${$field} . '%');\n        }\n";
        }
        $insertFields = '';
        foreach ($v['insertCols'] as $col) {
            $cn = $col['columnName'];
            $insertFields .= "            '{$cn}' => \$data['{$cn}'] ?? '',\n";
        }
        $updateFields = '';
        foreach ($v['editCols'] as $col) {
            $cn = $col['columnName'];
            $updateFields .= "            '{$cn}' => \$data['{$cn}'] ?? '',\n";
        }
        return <<<PHP
<?php
declare(strict_types=1);

namespace app\\service;

use app\\common\\Auth;
use app\\common\\PageHelper;
use think\\facade\\Db;

class {$c}Service
{
    public function selectList(array \$params = []): array
    {
        PageHelper::startPage();
        \$query = Db::name('{$tableName}');
{$queryBlocks}        [\$rows, \$total] = PageHelper::paginate(\$query);
        return [PageHelper::camelRows(\$rows), \$total];
    }

    public function get(int \$id): ?array
    {
        \$row = Db::name('{$tableName}')->where('{$pk}', \$id)->find();
        return \$row ? PageHelper::camelKeys(\$row) : null;
    }

    public function insert(array \$data): int
    {
        \$data = PageHelper::snakeParams(\$data);
        \$row = [
{$insertFields}            'create_by'   => Auth::getLoginName(),
            'create_time' => date('Y-m-d H:i:s'),
        ];
        return (int) Db::name('{$tableName}')->insertGetId(\$row);
    }

    public function update(array \$data): int
    {
        \$data = PageHelper::snakeParams(\$data);
        \$id = (int) (\$data['{$pk}'] ?? 0);
        return Db::name('{$tableName}')->where('{$pk}', \$id)->update([
{$updateFields}            'update_by'   => Auth::getLoginName(),
            'update_time' => date('Y-m-d H:i:s'),
        ]);
    }

    public function delete(string \$ids): int
    {
        \$idArr = array_filter(array_map('intval', explode(',', \$ids)));
        if (!\$idArr) {
            return 0;
        }
        return Db::name('{$tableName}')->whereIn('{$pk}', \$idArr)->delete();
    }
}
PHP;
    }

    /**
     * 生成列表页模板
     */
    protected function tplList(array $v): string
    {
        $b = $v['business'];
        $m = $v['module'];
        $fn = $v['functionName'];
        $pkField = $v['pkField'];
        $search = '';
        foreach ($v['queryCols'] as $col) {
            $search .= '<li>' . htmlspecialchars((string) ($col['columnComment'] ?: $col['javaField'])) . '??<input type="text" name="' . $col['javaField'] . '"/></li>' . "\n";
        }
        $colsJs = '';
        foreach ($v['listCols'] as $col) {
            $colsJs .= "      {field:'{$col['javaField']}', title:'" . addslashes((string) ($col['columnComment'] ?: $col['javaField'])) . "'},\n";
        }
        return <<<HTML
<!DOCTYPE html>
<html lang="zh">
<head>{include file="/common/header" title="{$fn}" /}</head>
<body class="gray-bg">
<div class="container-div"><div class="row">
<div class="col-sm-12 search-collapse">
<form id="formId"><div class="select-list"><ul>
{$search}<li><a class="btn btn-primary btn-rounded btn-sm" onclick="\$.table.search()"><i class="fa fa-search"></i>&nbsp;????</a>
<a class="btn btn-warning btn-rounded btn-sm" onclick="\$.form.reset()"><i class="fa fa-refresh"></i>&nbsp;????</a></li>
</ul></div></form>
</div>
<div class="btn-group-sm" id="toolbar">
<a class="btn btn-success" onclick="\$.operate.add()"><i class="fa fa-plus"></i> ????</a>
<a class="btn btn-primary single disabled" onclick="\$.operate.edit()"><i class="fa fa-edit"></i> ???</a>
<a class="btn btn-danger multiple disabled" onclick="\$.operate.removeAll()"><i class="fa fa-remove"></i> ???</a>
</div>
<div class="col-sm-12 select-table table-striped"><table id="bootstrap-table"></table></div>
</div></div>
{include file="/common/footer" /}
<script>
var prefix = ctx + "{$m}/{$b}";
\$(function(){
  \$.table.init({
    uniqueId: "{$pkField}",
    url: prefix + "/list",
    createUrl: prefix + "/add",
    updateUrl: prefix + "/edit/{id}",
    removeUrl: prefix + "/remove",
    modalName: "{$fn}",
    columns: [
      {checkbox:true},
{$colsJs}      {title:'????', formatter:function(v,row){
        return '<a class="btn btn-success btn-xs" onclick="\$.operate.edit(\\''+row.{$pkField}+'\\')"><i class="fa fa-edit"></i>??</a> '+
               '<a class="btn btn-danger btn-xs" onclick="\$.operate.remove(\\''+row.{$pkField}+'\\')"><i class="fa fa-remove"></i>???</a>';
      }}
    ]
  });
});
</script>
</body></html>
HTML;
    }

    /**
     * 生成新增/编辑表单模板
     * @param bool $edit 是否编辑页
     */
    protected function tplForm(array $v, bool $edit): string
    {
        $cols = $edit ? $v['editCols'] : $v['insertCols'];
        $fn = $v['functionName'];
        $pkField = $v['pkField'];
        $biz = $v['business'];
        $mod = $v['module'];
        $action = $edit ? 'edit' : 'add';
        $fields = '';
        if ($edit) {
            $fields = '<input type="hidden" name="' . $pkField . '" value="{$row.' . $pkField . '|default=\'\'}">' . "\n";
        }
        foreach ($cols as $col) {
            $label = htmlspecialchars((string) ($col['columnComment'] ?: $col['javaField']));
            $name = $col['javaField'];
            $val = $edit ? '{$row.' . $name . '|default=\'\'}' : '';
            $fields .= "<div class=\"form-group\">\n"
                . "  <label class=\"col-sm-3 control-label\">{$label}：</label>\n"
                . "  <div class=\"col-sm-8\">\n"
                . "    <input class=\"form-control\" name=\"{$name}\" value=\"{$val}\">\n"
                . "  </div>\n"
                . "</div>\n";
        }
        $title = ($edit ? '修改' : '新增') . $fn;
        return <<<HTML
<!DOCTYPE html>
<html lang="zh">
<head>{include file="/common/header" title="{$title}" /}</head>
<body class="white-bg">
<div class="wrapper wrapper-content animated fadeInRight ibox-content">
<form class="form-horizontal m" id="form-{$biz}-{$action}">
{$fields}</form>
</div>
{include file="/common/footer" /}
<script>
var prefix = ctx + "{$mod}/{$biz}";
\$("#form-{$biz}-{$action}").validate({focusCleanup:true});
function submitHandler(){
  if(\$.validate.form()){ \$.operate.save(prefix+"/{$action}", \$('#form-{$biz}-{$action}').serialize()); }
}
</script>
</body></html>
HTML;
    }

    /**
     * 表名转业务名
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
        if (str_contains($t, 'datetime') || str_contains($t, 'timestamp') || str_contains($t, 'date')) {
            return 'string';
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
        if (str_contains($n, 'status') || str_contains($n, 'type') || str_contains($n, 'sex')) {
            return 'select';
        }
        if (str_contains($t, 'datetime') || str_contains($t, 'date')) {
            return 'datetime';
        }
        return 'input';
    }
}
