<?php
use app\common\addon\AddonManager;
use think\facade\Route;

// 公开路由
Route::get('/', 'Index/home');
Route::get('login', 'Login/index');
Route::post('login', 'Login/ajaxLogin');
Route::get('logout', 'Login/logout');
Route::get('unauth', 'Login/unauth');
Route::get('captcha/captchaImage', 'Captcha/captchaImage');

// 需登录
Route::group(function () {
    Route::get('index', 'Index/index');
    Route::get('system/main', 'Index/main');

    // 用户
    Route::get('system/user', 'system.User/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:view']);
    Route::post('system/user/list', 'system.User/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:list']);
    Route::rule('system/user/add', 'system.User/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:add', 'title' => '用户管理']);
    Route::get('system/user/edit/:id', 'system.User/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:edit']);
    Route::post('system/user/edit', 'system.User/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:edit', 'title' => '用户管理']);
    Route::post('system/user/remove', 'system.User/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:remove', 'title' => '用户管理']);
    Route::rule('system/user/resetPwd/:id', 'system.User/resetPwd', 'GET|POST')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:user:resetPwd']);
    Route::post('system/user/resetPwd', 'system.User/resetPwd')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:resetPwd', 'title' => '重置密码']);
    Route::post('system/user/checkLoginNameUnique', 'system.User/checkLoginNameUnique');
    Route::post('system/user/changeStatus', 'system.User/changeStatus')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:user:edit', 'title' => '用户状态']);
    Route::get('system/user/deptTreeData', 'system.User/deptTreeData');
    Route::get('system/user/selectDeptTree/:deptId', 'system.User/selectDeptTree');

    // 个人中心
    Route::get('system/user/profile', 'system.Profile/index');
    Route::post('system/user/profile/update', 'system.Profile/update')->middleware(\app\middleware\OperLog::class)->option(['title' => '个人中心']);
    Route::rule('system/user/profile/resetPwd', 'system.Profile/resetPwd', 'GET|POST')->middleware(\app\middleware\OperLog::class)->option(['title' => '修改密码']);

    // 角色
    Route::get('system/role', 'system.Role/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:role:view']);
    Route::post('system/role/list', 'system.Role/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:role:list']);
    Route::rule('system/role/add', 'system.Role/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:add', 'title' => '角色管理']);
    Route::get('system/role/edit/:id', 'system.Role/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:role:edit']);
    Route::post('system/role/edit', 'system.Role/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:edit', 'title' => '角色管理']);
    Route::post('system/role/remove', 'system.Role/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:remove', 'title' => '角色管理']);
    Route::rule('system/role/authDataScope/:id', 'system.Role/authDataScope', 'GET|POST')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:role:edit']);
    Route::post('system/role/authDataScope', 'system.Role/authDataScope')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:edit', 'title' => '角色数据权限']);
    Route::post('system/role/changeStatus', 'system.Role/changeStatus')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:role:edit', 'title' => '角色状态']);
    Route::post('system/role/checkRoleNameUnique', 'system.Role/checkRoleNameUnique');
    Route::post('system/role/checkRoleKeyUnique', 'system.Role/checkRoleKeyUnique');
    Route::get('system/role/menuTreeData', 'system.Role/menuTreeData');
    Route::get('system/role/deptTreeData', 'system.Role/deptTreeData');

    // 菜单
    Route::get('system/menu', 'system.Menu/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:menu:view']);
    Route::post('system/menu/list', 'system.Menu/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:menu:list']);
    Route::rule('system/menu/add/:parentId', 'system.Menu/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:menu:add', 'title' => '菜单管理']);
    Route::rule('system/menu/add', 'system.Menu/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:menu:add', 'title' => '菜单管理']);
    Route::get('system/menu/edit/:id', 'system.Menu/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:menu:edit']);
    Route::post('system/menu/edit', 'system.Menu/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:menu:edit', 'title' => '菜单管理']);
    Route::post('system/menu/remove/:id', 'system.Menu/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:menu:remove', 'title' => '菜单管理']);
    Route::get('system/menu/treeData', 'system.Menu/treeData');
    Route::get('system/menu/treeData/:excludeId', 'system.Menu/treeData');
    Route::get('system/menu/selectMenuTree/:menuId/:excludeId', 'system.Menu/selectMenuTree');
    Route::get('system/menu/selectMenuTree/:menuId', 'system.Menu/selectMenuTree');

    // 部门
    Route::get('system/dept', 'system.Dept/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dept:view']);
    Route::post('system/dept/list', 'system.Dept/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dept:list']);
    Route::rule('system/dept/add/:parentId', 'system.Dept/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dept:add', 'title' => '部门管理']);
    Route::rule('system/dept/add', 'system.Dept/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dept:add', 'title' => '部门管理']);
    Route::get('system/dept/edit/:id', 'system.Dept/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dept:edit']);
    Route::post('system/dept/edit', 'system.Dept/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dept:edit', 'title' => '部门管理']);
    Route::post('system/dept/remove/:id', 'system.Dept/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dept:remove', 'title' => '部门管理']);
    Route::get('system/dept/treeData', 'system.Dept/treeData');
    Route::get('system/dept/treeData/:excludeId', 'system.Dept/treeData');
    Route::get('system/dept/selectDeptTree/:deptId/:excludeId', 'system.Dept/selectDeptTree');
    Route::get('system/dept/selectDeptTree/:deptId', 'system.Dept/selectDeptTree');

    // 岗位
    Route::get('system/post', 'system.Post/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:post:view']);
    Route::post('system/post/list', 'system.Post/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:post:list']);
    Route::rule('system/post/add', 'system.Post/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:post:add', 'title' => '岗位管理']);
    Route::get('system/post/edit/:id', 'system.Post/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:post:edit']);
    Route::post('system/post/edit', 'system.Post/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:post:edit', 'title' => '岗位管理']);
    Route::post('system/post/remove', 'system.Post/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:post:remove', 'title' => '岗位管理']);

    // 插件管理
    Route::get('system/addon', 'system.Addon/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:addon:view']);
    Route::post('system/addon/list', 'system.Addon/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:addon:list']);
    Route::post('system/addon/install', 'system.Addon/install')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:install', 'title' => '插件安装']);
    Route::post('system/addon/uninstall', 'system.Addon/uninstall')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:uninstall', 'title' => '插件卸载']);
    Route::post('system/addon/enable', 'system.Addon/enable')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:edit', 'title' => '插件启用']);
    Route::post('system/addon/disable', 'system.Addon/disable')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:edit', 'title' => '插件禁用']);
    Route::post('system/addon/upload', 'system.Addon/upload')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:install', 'title' => '本地安装插件']);
    Route::rule('system/addon/config/:name', 'system.Addon/config', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:addon:config', 'title' => '插件配置']);

    // 已启用插件需登录路由
    AddonManager::instance()->mergeAuthRoutes();

    // 字典类型
    Route::get('system/dict', 'system.Dict/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:view']);
    Route::post('system/dict/list', 'system.Dict/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:list']);
    Route::rule('system/dict/add', 'system.Dict/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:add', 'title' => '字典管理']);
    Route::get('system/dict/edit/:id', 'system.Dict/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:edit']);
    Route::post('system/dict/edit', 'system.Dict/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:edit', 'title' => '字典管理']);
    Route::post('system/dict/remove', 'system.Dict/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:remove', 'title' => '字典管理']);
    Route::post('system/dict/checkDictTypeUnique', 'system.Dict/checkDictTypeUnique');

    // 字典数据
    Route::get('system/dict/data', 'system.DictData/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:view']);
    Route::get('system/dict/data/:dictId', 'system.DictData/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:view']);
    Route::post('system/dict/data/list', 'system.DictData/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:list']);
    Route::rule('system/dict/data/add', 'system.DictData/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:add', 'title' => '字典数据']);
    Route::get('system/dict/data/edit/:id', 'system.DictData/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:dict:edit']);
    Route::post('system/dict/data/edit', 'system.DictData/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:edit', 'title' => '字典数据']);
    Route::post('system/dict/data/remove', 'system.DictData/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:dict:remove', 'title' => '字典数据']);

    // 参数
    Route::get('system/config', 'system.Config/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:config:view']);
    Route::post('system/config/list', 'system.Config/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:config:list']);
    Route::rule('system/config/add', 'system.Config/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:config:add', 'title' => '参数设置']);
    Route::get('system/config/edit/:id', 'system.Config/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:config:edit']);
    Route::post('system/config/edit', 'system.Config/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:config:edit', 'title' => '参数设置']);
    Route::post('system/config/remove', 'system.Config/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:config:remove', 'title' => '参数设置']);
    Route::post('system/config/checkConfigKeyUnique', 'system.Config/checkConfigKeyUnique');

    // 通知
    Route::get('system/notice', 'system.Notice/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:notice:view']);
    Route::post('system/notice/list', 'system.Notice/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:notice:list']);
    Route::rule('system/notice/add', 'system.Notice/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:notice:add', 'title' => '通知公告']);
    Route::get('system/notice/edit/:id', 'system.Notice/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'system:notice:edit']);
    Route::post('system/notice/edit', 'system.Notice/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:notice:edit', 'title' => '通知公告']);
    Route::post('system/notice/remove', 'system.Notice/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'system:notice:remove', 'title' => '通知公告']);

    // 操作日志
    Route::get('monitor/operlog', 'monitor.Operlog/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:operlog:view']);
    Route::post('monitor/operlog/list', 'monitor.Operlog/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:operlog:list']);
    Route::get('monitor/operlog/detail/:id', 'monitor.Operlog/detail')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:operlog:detail']);
    Route::post('monitor/operlog/remove', 'monitor.Operlog/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:operlog:remove', 'title' => '操作日志']);
    Route::post('monitor/operlog/clean', 'monitor.Operlog/clean')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:operlog:remove', 'title' => '清空操作日志']);

    // 登录日志
    Route::get('monitor/logininfor', 'monitor.Logininfor/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:logininfor:view']);
    Route::post('monitor/logininfor/list', 'monitor.Logininfor/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:logininfor:list']);
    Route::post('monitor/logininfor/remove', 'monitor.Logininfor/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:logininfor:remove', 'title' => '登录日志']);
    Route::post('monitor/logininfor/clean', 'monitor.Logininfor/clean')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:logininfor:remove', 'title' => '清空登录日志']);
    Route::post('monitor/logininfor/unlock', 'monitor.Logininfor/unlock')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:logininfor:unlock', 'title' => '账户解锁']);
    Route::post('monitor/logininfor/export', 'monitor.Logininfor/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:logininfor:export']);

    // 操作日志导出
    Route::post('monitor/operlog/export', 'monitor.Operlog/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:operlog:export']);

    // 在线用户
    Route::get('monitor/online', 'monitor.Online/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:online:view']);
    Route::post('monitor/online/list', 'monitor.Online/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:online:list']);
    Route::post('monitor/online/batchForceLogout', 'monitor.Online/batchForceLogout')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:online:batchForceLogout', 'title' => '强退用户']);

    // 定时任务
    Route::get('monitor/job', 'monitor.Job/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:view']);
    Route::post('monitor/job/list', 'monitor.Job/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:list']);
    Route::rule('monitor/job/add', 'monitor.Job/add', 'GET|POST')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:add', 'title' => '定时任务']);
    Route::get('monitor/job/edit/:id', 'monitor.Job/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:edit']);
    Route::post('monitor/job/edit', 'monitor.Job/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:edit', 'title' => '定时任务']);
    Route::post('monitor/job/remove', 'monitor.Job/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:remove', 'title' => '定时任务']);
    Route::get('monitor/job/detail/:id', 'monitor.Job/detail')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:detail']);
    Route::post('monitor/job/changeStatus', 'monitor.Job/changeStatus')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:changeStatus', 'title' => '任务状态']);
    Route::post('monitor/job/run', 'monitor.Job/run')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:changeStatus', 'title' => '执行任务']);
    Route::post('monitor/job/checkCronExpressionIsValid', 'monitor.Job/checkCronExpressionIsValid');
    Route::get('monitor/job/cron', 'monitor.Job/cron')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:view']);
    Route::post('monitor/job/export', 'monitor.Job/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:export']);

    // 任务日志
    Route::get('monitor/jobLog', 'monitor.JobLog/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:detail']);
    Route::post('monitor/jobLog/list', 'monitor.JobLog/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:detail']);
    Route::get('monitor/jobLog/detail/:id', 'monitor.JobLog/detail')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:detail']);
    Route::post('monitor/jobLog/remove', 'monitor.JobLog/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:remove', 'title' => '调度日志']);
    Route::post('monitor/jobLog/clean', 'monitor.JobLog/clean')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:job:remove', 'title' => '清空调度日志']);
    Route::post('monitor/jobLog/export', 'monitor.JobLog/export')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:job:export']);

    // 服务监控
    Route::get('monitor/server', 'monitor.Server/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:server:view']);

    // 缓存监控
    Route::get('monitor/cache', 'monitor.Cache/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:cache:view']);
    Route::post('monitor/cache/getNames', 'monitor.Cache/getNames')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:cache:view']);
    Route::post('monitor/cache/getKeys', 'monitor.Cache/getKeys')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:cache:view']);
    Route::post('monitor/cache/getValue', 'monitor.Cache/getValue')->middleware(\app\middleware\Permission::class)->option(['perms' => 'monitor:cache:view']);
    Route::post('monitor/cache/clearCacheName', 'monitor.Cache/clearCacheName')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:cache:view', 'title' => '清理缓存']);
    Route::post('monitor/cache/clearCacheKey', 'monitor.Cache/clearCacheKey')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:cache:view', 'title' => '清理缓存键']);
    Route::get('monitor/cache/clearAll', 'monitor.Cache/clearAll')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'monitor:cache:view', 'title' => '清理全部缓存']);

    // 表单构建
    Route::get('tool/build', 'tool.Build/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:build:view']);

    // 代码生成
    Route::get('tool/gen', 'tool.Gen/index')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:view']);
    Route::post('tool/gen/list', 'tool.Gen/list')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:list']);
    Route::post('tool/gen/db/list', 'tool.Gen/dbList')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:list']);
    Route::post('tool/gen/importTable', 'tool.Gen/importTable')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:gen:import', 'title' => '导入表']);
    Route::get('tool/gen/edit/:id', 'tool.Gen/edit')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:edit']);
    Route::post('tool/gen/edit', 'tool.Gen/edit')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:gen:edit', 'title' => '代码生成']);
    Route::post('tool/gen/remove', 'tool.Gen/remove')->middleware([\app\middleware\Permission::class, \app\middleware\OperLog::class])->option(['perms' => 'tool:gen:remove', 'title' => '代码生成']);
    Route::get('tool/gen/preview/:id', 'tool.Gen/preview')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:preview']);
    Route::get('tool/gen/download/:id', 'tool.Gen/download')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:code']);
    Route::get('tool/gen/batchGenCode', 'tool.Gen/batchGenCode')->middleware(\app\middleware\Permission::class)->option(['perms' => 'tool:gen:code']);

    // 导出 / 导入
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

// 已启用插件公开路由 + 静态资源
AddonManager::instance()->mergePublicRoutes();

