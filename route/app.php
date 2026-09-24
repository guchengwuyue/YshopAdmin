<?php
use app\common\Entry;
use app\common\addon\AddonManager;
use think\facade\Route;

if (Entry::isAdmin()) {
// 管理网关 JSON API（Bearer adm_）。禁止 View::fetch。
Route::group('api', function () {
    Route::post('login', 'api.Auth/login');

    Route::group(function () {
        Route::post('logout', 'api.Auth/logout');
        Route::get('getInfo', 'api.Auth/getInfo');
        Route::get('getRouters', 'api.Router/getRouters');

        Route::get('system/user/list', 'api.system.User/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:list']);
        Route::get('system/user/add', 'api.system.User/addForm')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:add']);
        Route::get('system/user/edit/:id', 'api.system.User/editForm')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:edit']);
        Route::post('system/user/add', 'api.system.User/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:add', 'title' => '用户管理']);
        Route::post('system/user/edit', 'api.system.User/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:edit', 'title' => '用户管理']);
        Route::post('system/user/remove', 'api.system.User/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:remove', 'title' => '用户管理']);
        Route::post('system/user/resetPwd', 'api.system.User/resetPwd')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:resetPwd', 'title' => '重置密码']);
        Route::post('system/user/changeStatus', 'api.system.User/changeStatus')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:edit', 'title' => '用户状态']);
        Route::get('system/user/deptTree', 'api.system.User/deptTree')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:list']);

        Route::get('system/role/list', 'api.system.Role/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:role:list']);
        Route::get('system/role/get/:id', 'api.system.Role/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:role:edit']);
        Route::post('system/role/add', 'api.system.Role/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:add', 'title' => '角色管理']);
        Route::post('system/role/edit', 'api.system.Role/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:edit', 'title' => '角色管理']);
        Route::post('system/role/remove', 'api.system.Role/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:remove', 'title' => '角色管理']);
        Route::post('system/role/changeStatus', 'api.system.Role/changeStatus')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:edit', 'title' => '角色状态']);
        Route::post('system/role/authDataScope', 'api.system.Role/authDataScope')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:edit', 'title' => '角色数据权限']);
        Route::get('system/role/menuTree', 'api.system.Role/menuTree');
        Route::get('system/role/deptTree', 'api.system.Role/deptTree');

        Route::get('system/menu/list', 'api.system.Menu/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:menu:list']);
        Route::get('system/menu/tree', 'api.system.Menu/tree')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:menu:list']);
        Route::get('system/menu/get/:id', 'api.system.Menu/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:menu:edit']);
        Route::post('system/menu/add', 'api.system.Menu/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:menu:add', 'title' => '菜单管理']);
        Route::post('system/menu/edit', 'api.system.Menu/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:menu:edit', 'title' => '菜单管理']);
        Route::post('system/menu/remove', 'api.system.Menu/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:menu:remove', 'title' => '菜单管理']);

        Route::get('system/dept/list', 'api.system.Dept/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dept:list']);
        Route::get('system/dept/tree', 'api.system.Dept/tree');
        Route::get('system/dept/get/:id', 'api.system.Dept/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dept:edit']);
        Route::post('system/dept/add', 'api.system.Dept/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dept:add', 'title' => '部门管理']);
        Route::post('system/dept/edit', 'api.system.Dept/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dept:edit', 'title' => '部门管理']);
        Route::post('system/dept/remove', 'api.system.Dept/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dept:remove', 'title' => '部门管理']);

        Route::get('system/post/list', 'api.system.Post/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:post:list']);
        Route::get('system/post/get/:id', 'api.system.Post/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:post:edit']);
        Route::post('system/post/add', 'api.system.Post/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:post:add', 'title' => '岗位管理']);
        Route::post('system/post/edit', 'api.system.Post/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:post:edit', 'title' => '岗位管理']);
        Route::post('system/post/remove', 'api.system.Post/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:post:remove', 'title' => '岗位管理']);

        Route::get('system/notice/list', 'api.system.Notice/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:notice:list']);
        Route::get('system/notice/get/:id', 'api.system.Notice/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:notice:edit']);
        Route::post('system/notice/add', 'api.system.Notice/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:notice:add', 'title' => '通知公告']);
        Route::post('system/notice/edit', 'api.system.Notice/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:notice:edit', 'title' => '通知公告']);
        Route::post('system/notice/remove', 'api.system.Notice/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:notice:remove', 'title' => '通知公告']);

        Route::get('system/dict/list', 'api.system.Dict/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:list']);
        Route::get('system/dict/get/:id', 'api.system.Dict/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:edit']);
        Route::post('system/dict/add', 'api.system.Dict/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:add', 'title' => '字典管理']);
        Route::post('system/dict/edit', 'api.system.Dict/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:edit', 'title' => '字典管理']);
        Route::post('system/dict/remove', 'api.system.Dict/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:remove', 'title' => '字典管理']);
        Route::get('system/dict/data/list', 'api.system.DictData/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:list']);
        Route::get('system/dict/data/type/:dictType', 'api.system.DictData/type')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:list']);
        Route::get('system/dict/data/get/:id', 'api.system.DictData/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:edit']);
        Route::post('system/dict/data/add', 'api.system.DictData/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:add', 'title' => '字典数据']);
        Route::post('system/dict/data/edit', 'api.system.DictData/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:edit', 'title' => '字典数据']);
        Route::post('system/dict/data/remove', 'api.system.DictData/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:remove', 'title' => '字典数据']);

        Route::get('system/config/list', 'api.system.Config/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:config:list']);
        Route::get('system/config/get/:id', 'api.system.Config/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:config:edit']);
        Route::post('system/config/add', 'api.system.Config/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:config:add', 'title' => '参数设置']);
        Route::post('system/config/edit', 'api.system.Config/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:config:edit', 'title' => '参数设置']);
        Route::post('system/config/remove', 'api.system.Config/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:config:remove', 'title' => '参数设置']);

        Route::get('system/addon/list', 'api.system.Addon/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:addon:list']);
        Route::post('system/addon/install', 'api.system.Addon/install')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:install', 'title' => '插件安装']);
        Route::post('system/addon/uninstall', 'api.system.Addon/uninstall')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:uninstall', 'title' => '插件卸载']);
        Route::post('system/addon/enable', 'api.system.Addon/enable')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:edit', 'title' => '插件启用']);
        Route::post('system/addon/disable', 'api.system.Addon/disable')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:edit', 'title' => '插件禁用']);
        Route::post('system/addon/upload', 'api.system.Addon/upload')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:install', 'title' => '本地安装插件']);
        Route::get('system/addon/config/:name', 'api.system.Addon/config')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:addon:config']);
        Route::post('system/addon/config', 'api.system.Addon/saveConfig')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:config', 'title' => '插件配置']);

        Route::get('monitor/operlog/list', 'api.monitor.Operlog/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:operlog:list']);
        Route::get('monitor/operlog/get/:id', 'api.monitor.Operlog/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:operlog:detail']);
        Route::post('monitor/operlog/remove', 'api.monitor.Operlog/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:operlog:remove', 'title' => '操作日志']);
        Route::post('monitor/operlog/clean', 'api.monitor.Operlog/clean')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:operlog:remove', 'title' => '清空操作日志']);
        Route::get('monitor/logininfor/list', 'api.monitor.Logininfor/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:logininfor:list']);
        Route::post('monitor/logininfor/remove', 'api.monitor.Logininfor/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:logininfor:remove', 'title' => '登录日志']);
        Route::post('monitor/logininfor/clean', 'api.monitor.Logininfor/clean')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:logininfor:remove', 'title' => '清空登录日志']);
        Route::post('monitor/logininfor/unlock', 'api.monitor.Logininfor/unlock')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:logininfor:unlock', 'title' => '账户解锁']);
        Route::get('monitor/online/list', 'api.monitor.Online/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:online:list']);
        Route::post('monitor/online/batchForceLogout', 'api.monitor.Online/batchForceLogout')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:online:batchForceLogout', 'title' => '强退用户']);
        Route::get('monitor/server', 'api.monitor.Server/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:server:view']);
        Route::get('monitor/cache/names', 'api.monitor.Cache/names')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:cache:view']);
        Route::get('monitor/cache/keys', 'api.monitor.Cache/keys')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:cache:view']);
        Route::get('monitor/cache/value', 'api.monitor.Cache/value')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:cache:view']);
        Route::post('monitor/cache/clearName', 'api.monitor.Cache/clearName')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:cache:view', 'title' => '清理缓存']);
        Route::post('monitor/cache/clearKey', 'api.monitor.Cache/clearKey')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:cache:view', 'title' => '清理缓存键']);
        Route::post('monitor/cache/clearAll', 'api.monitor.Cache/clearAll')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:cache:view', 'title' => '清理全部缓存']);
        Route::get('monitor/job/list', 'api.monitor.Job/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:list']);
        Route::get('monitor/job/get/:id', 'api.monitor.Job/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:edit']);
        Route::post('monitor/job/add', 'api.monitor.Job/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:add', 'title' => '定时任务']);
        Route::post('monitor/job/edit', 'api.monitor.Job/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:edit', 'title' => '定时任务']);
        Route::post('monitor/job/remove', 'api.monitor.Job/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:remove', 'title' => '定时任务']);
        Route::post('monitor/job/changeStatus', 'api.monitor.Job/changeStatus')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:changeStatus', 'title' => '任务状态']);
        Route::post('monitor/job/run', 'api.monitor.Job/run')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:changeStatus', 'title' => '执行任务']);
        Route::get('monitor/jobLog/list', 'api.monitor.JobLog/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:detail']);
        Route::post('monitor/jobLog/remove', 'api.monitor.JobLog/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:remove', 'title' => '调度日志']);
        Route::post('monitor/jobLog/clean', 'api.monitor.JobLog/clean')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:remove', 'title' => '清空调度日志']);

        Route::get('tool/gen/list', 'api.tool.Gen/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:list']);
        Route::get('tool/gen/db/list', 'api.tool.Gen/dbList')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:list']);
        Route::get('tool/gen/get/:id', 'api.tool.Gen/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:edit']);
        Route::post('tool/gen/importTable', 'api.tool.Gen/importTable')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:gen:code', 'title' => '导入表']);
        Route::post('tool/gen/edit', 'api.tool.Gen/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:gen:edit', 'title' => '代码生成']);
        Route::post('tool/gen/remove', 'api.tool.Gen/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:gen:remove', 'title' => '代码生成']);
        Route::get('tool/gen/preview/:id', 'api.tool.Gen/preview')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:preview']);
        Route::get('tool/gen/download/:id', 'api.tool.Gen/download')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:code']);

        Route::get('tool/file/list', 'api.tool.File/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:file:list']);
        Route::post('tool/file/upload', 'api.tool.File/upload')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:upload', 'title' => '文件上传']);
        Route::post('tool/file/remove', 'api.tool.File/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:remove', 'title' => '文件删除']);
        Route::post('tool/file/move', 'api.tool.File/move')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:edit', 'title' => '文件移动']);
        Route::get('tool/fileGroup/tree', 'api.tool.FileGroup/tree')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:file:list']);
        Route::post('tool/fileGroup/add', 'api.tool.FileGroup/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:edit', 'title' => '文件分组']);
        Route::post('tool/fileGroup/edit', 'api.tool.FileGroup/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:edit', 'title' => '文件分组']);
        Route::post('tool/fileGroup/remove', 'api.tool.FileGroup/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:edit', 'title' => '文件分组']);
        Route::get('tool/fileConfig/list', 'api.tool.FileConfig/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:file:list']);
        Route::get('tool/fileConfig/get/:id', 'api.tool.FileConfig/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:file:edit']);
        Route::post('tool/fileConfig/add', 'api.tool.FileConfig/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:add', 'title' => '文件配置']);
        Route::post('tool/fileConfig/edit', 'api.tool.FileConfig/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:edit', 'title' => '文件配置']);
        Route::post('tool/fileConfig/remove', 'api.tool.FileConfig/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:remove', 'title' => '文件配置']);
        Route::post('tool/fileConfig/master', 'api.tool.FileConfig/master')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:file:edit', 'title' => '文件主配置']);
        Route::post('tool/fileConfig/test', 'api.tool.FileConfig/test')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:file:edit']);

        Route::get('tool/addondev/list', 'api.tool.AddonDev/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:addondev:list']);
        Route::get('tool/addondev/get/:name', 'api.tool.AddonDev/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:addondev:edit']);
        Route::post('tool/addondev/add', 'api.tool.AddonDev/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:addondev:add', 'title' => '插件开发']);
        Route::post('tool/addondev/edit', 'api.tool.AddonDev/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:addondev:edit', 'title' => '插件开发']);
        Route::post('tool/addondev/package', 'api.tool.AddonDev/package')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:addondev:package', 'title' => '插件打包']);
        Route::get('tool/addondev/menu', 'api.tool.AddonDev/menu')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:addondev:list']);
        Route::get('tool/addondev/menu/get/:id', 'api.tool.AddonDev/menuGet')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:addondev:edit']);
        Route::get('tool/addondev/menu/tree', 'api.tool.AddonDev/menuTree')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:addondev:list']);
        Route::post('tool/addondev/menu/add', 'api.tool.AddonDev/menuAdd')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:addondev:edit', 'title' => '插件菜单']);
        Route::post('tool/addondev/menu/edit', 'api.tool.AddonDev/menuEdit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:addondev:edit', 'title' => '插件菜单']);
        Route::post('tool/addondev/menu/remove', 'api.tool.AddonDev/menuRemove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:addondev:edit', 'title' => '插件菜单']);
        Route::post('tool/addondev/sync', 'api.tool.AddonDev/sync')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:addondev:edit', 'title' => '同步插件菜单']);

        Route::get('pay/config', 'api.pay.Config/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'pay:config:view']);
        Route::post('pay/config', 'api.pay.Config/save')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'pay:config:edit', 'title' => '支付配置']);
        Route::post('pay/config/upload', 'api.pay.Config/upload')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'pay:config:edit', 'title' => '支付证书']);
        Route::post('pay/test', 'api.pay.Test/pay')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'pay:test:pay', 'title' => '支付测试']);

        Route::get('wechat/setting', 'api.wechat.Setting/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'wechat:setting:view']);
        Route::post('wechat/setting', 'api.wechat.Setting/save')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:setting:edit', 'title' => '微信设置']);
        Route::get('wechat/config/list', 'api.wechat.Config/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'wechat:config:list']);
        Route::get('wechat/config/get/:id', 'api.wechat.Config/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'wechat:config:edit']);
        Route::post('wechat/config/add', 'api.wechat.Config/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:config:add', 'title' => '微信配置']);
        Route::post('wechat/config/edit', 'api.wechat.Config/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:config:edit', 'title' => '微信配置']);
        Route::post('wechat/config/remove', 'api.wechat.Config/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:config:remove', 'title' => '微信配置']);
        Route::get('wechat/menu', 'api.wechat.Menu/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'wechat:menu:view']);
        Route::post('wechat/menu/save', 'api.wechat.Menu/save')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:menu:edit', 'title' => '微信菜单']);
        Route::post('wechat/menu/pull', 'api.wechat.Menu/pull')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:menu:remote', 'title' => '拉取微信菜单']);
        Route::post('wechat/menu/sync', 'api.wechat.Menu/sync')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:menu:sync', 'title' => '同步微信菜单']);
        Route::get('wechat/autoreply/list', 'api.wechat.Autoreply/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'wechat:autoreply:list']);
        Route::get('wechat/autoreply/get/:id', 'api.wechat.Autoreply/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'wechat:autoreply:edit']);
        Route::post('wechat/autoreply/add', 'api.wechat.Autoreply/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:autoreply:add', 'title' => '自动回复']);
        Route::post('wechat/autoreply/edit', 'api.wechat.Autoreply/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:autoreply:edit', 'title' => '自动回复']);
        Route::post('wechat/autoreply/remove', 'api.wechat.Autoreply/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:autoreply:remove', 'title' => '自动回复']);
        Route::get('wechat/response/list', 'api.wechat.Response/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'wechat:response:list']);
        Route::get('wechat/response/get/:id', 'api.wechat.Response/get')->middleware(\app\middleware\Permission::class)->option(['perms' => 'wechat:response:edit']);
        Route::post('wechat/response/add', 'api.wechat.Response/add')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:response:add', 'title' => '微信资源']);
        Route::post('wechat/response/edit', 'api.wechat.Response/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:response:edit', 'title' => '微信资源']);
        Route::post('wechat/response/remove', 'api.wechat.Response/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'wechat:response:remove', 'title' => '微信资源']);
    })->middleware([
        \app\middleware\AdminToken::class,
        \app\middleware\DemoMode::class,
    ]);
})->middleware(\app\middleware\JsonOnly::class);

// P6: Think SSR views removed; legacy MPA entry redirects to admin-ui SPA
Route::get('/', function () {
    return redirect('/admin/', 302);
});
Route::get('login', function () {
    return redirect('/admin/login', 302);
});
Route::get('logout', function () {
    return redirect('/admin/login', 302);
});
Route::get('unauth', function () {
    return redirect('/admin/login', 302);
});
Route::get('index', function () {
    return redirect('/admin/', 302);
});
Route::get('captcha/captchaImage', 'Captcha/captchaImage');

// Addon auth JSON（yscms 等）：与 /api 一致走 Bearer adm_，避免 Session 过期后 Token 仍有效却 403
Route::group(function () {
    AddonManager::instance()->mergeAuthRoutes();
})->middleware([
    \app\middleware\AdminToken::class,
    \app\middleware\DemoMode::class,
]);

// Legacy export/download（非 SPA JSON；仍用 Session）
Route::group(function () {
    Route::get('common/download', 'Common/download');
    Route::post('system/user/export', 'system.User/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:export']);
    Route::get('system/user/importTemplate', 'system.User/importTemplate')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:import']);
    Route::post('system/user/importData', 'system.User/importData')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:import', 'title' => '用户导入']);
    Route::post('system/role/export', 'system.Role/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:role:export']);
    Route::post('system/post/export', 'system.Post/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:post:export']);
    Route::post('system/dict/export', 'system.Dict/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:export']);
    Route::post('system/dict/data/export', 'system.DictData/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:export']);
    Route::post('system/config/export', 'system.Config/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:config:export']);
})->middleware([
    \app\middleware\AuthCheck::class,
    \app\middleware\DemoMode::class,
]);

} else {
// Pay/wechat 机器回调 — 禁止 JsonOnly（notify 返回网关原始 XML/JSON）
Route::any('api/pay/notifyx/:type', 'api.pay.Front/notifyx');
Route::any('api/pay/returnx/:type', 'api.pay.Front/returnx');
Route::any('api/wechat/server', 'api.wechat.Front/server');
Route::any('api/wechat/callback', 'api.wechat.Front/callback');

// P6 兼容：旧插件公开路径 → 系统 API（临时 302，父任务可再删）
Route::any('addons/yspay/api/notifyx/:type', function (string $type) {
    return redirect('/api/pay/notifyx/' . $type, 302);
});
Route::any('addons/yspay/api/returnx/:type', function (string $type) {
    return redirect('/api/pay/returnx/' . $type, 302);
});
Route::any('addons/yspay/api/submit', function () {
    return redirect('/api/pay/submit', 302);
});
Route::any('addons/yspay/api/wechat', function () {
    return redirect('/api/pay/wechat', 302);
});
Route::any('addons/yspay/api/alipay', function () {
    return redirect('/api/pay/alipay', 302);
});
Route::any('addons/yswechat/api/server', function () {
    return redirect('/api/wechat/server', 302);
});
Route::any('addons/yswechat/api/callback', function () {
    return redirect('/api/wechat/callback', 302);
});
Route::any('addons/yswechat/captcha/send', function () {
    return redirect('/api/wechat/captcha/send', 302);
});
Route::any('addons/yswechat/captcha/check', function () {
    return redirect('/api/wechat/captcha/check', 302);
});

// 站点网关 JSON API（Bearer mem_）。禁止 View::fetch。
Route::group('api', function () {
    Route::post('member/login', 'api.Member/login');
    Route::post('member/register', 'api.Member/register');
    Route::post('member/logout', 'api.Member/logout')->middleware(\app\middleware\MemberToken::class);
    Route::get('member/getInfo', 'api.Member/getInfo')->middleware(\app\middleware\MemberToken::class);
    Route::post('member/profile', 'api.Member/profile')->middleware(\app\middleware\MemberToken::class);
    Route::post('member/changepwd', 'api.Member/changepwd')->middleware(\app\middleware\MemberToken::class);

    Route::any('pay/submit', 'api.pay.Front/submit');
    Route::any('pay/wechat', 'api.pay.Front/wechat');
    Route::any('pay/alipay', 'api.pay.Front/alipay');
    Route::post('pay/query', 'api.pay.Front/query');
    Route::post('wechat/captcha/send', 'api.wechat.Front/captchaSend');
    Route::post('wechat/captcha/check', 'api.wechat.Front/captchaCheck');
})->middleware(\app\middleware\JsonOnly::class);

// 前台：验证码（会员注册/自定义表单等）+ 插件公开路由 + 静态资源
Route::get('captcha/captchaImage', 'Captcha/captchaImage');
// 误入前台的后台登录地址 → 后台入口（含 .html 后缀被剥离后的 login）
Route::get('login', function () {
    return redirect('/admin/login', 302);
});
Route::post('login', function () {
    return redirect('/admin/login', 302);
});
AddonManager::instance()->mergePublicRoutes();
}

