<?php
// +----------------------------------------------------------------------
// | YshopAdmin backend entry (/yshopadmin/*)
// +----------------------------------------------------------------------

use think\App;

define('YSHOP_ENTRY', 'admin');

require __DIR__ . '/../vendor/autoload.php';

// think-multi-app binds by SCRIPT_FILENAME basename; "yshopadmin" would
// map to missing app/yshopadmin/. Point SCRIPT_FILENAME at index.php only
// for MultiApp::getScriptName(); keep SCRIPT_NAME / REQUEST_URI unchanged.
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . DIRECTORY_SEPARATOR . 'index.php';

$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
