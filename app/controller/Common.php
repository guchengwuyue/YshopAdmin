<?php
declare(strict_types=1);

namespace app\controller;

use app\common\AjaxResult;
use app\common\ExcelUtil;
use app\controller\BaseController;

/**
 * 公共下载（Excel / zip 导出文件）
 */
class Common extends BaseController
{
    /**
     * 下载临时导出文件
     */
    public function download()
    {
        $fileName = (string) $this->request->param('fileName', '');
        $delete = filter_var($this->request->param('delete', false), FILTER_VALIDATE_BOOLEAN);
        $fileName = basename(str_replace(['\\', '/'], '', $fileName));
        if ($fileName === '' || str_contains($fileName, '..')) {
            return AjaxResult::error('invalid file');
        }
        $path = ExcelUtil::downloadDir() . $fileName;
        if (!is_file($path)) {
            return AjaxResult::error('file not found');
        }
        $response = download($path, $fileName);
        if ($delete) {
            register_shutdown_function(static function () use ($path) {
                @unlink($path);
            });
        }
        return $response;
    }
}
