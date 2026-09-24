<?php
namespace app;

use app\common\ApiCode;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param  Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if ($e instanceof HttpResponseException) {
            return parent::render($request, $e);
        }
        $path = ltrim((string) $request->pathinfo(), '/');
        if ($path === 'api' || str_starts_with($path, 'api/')) {
            $status = 500;
            $code = ApiCode::ERROR;
            $msg = '服务器错误';
            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();
                $code = $status >= 400 ? $status : ApiCode::ERROR;
                if ($e->getMessage() !== '') {
                    $msg = $e->getMessage();
                }
            } elseif ($e instanceof ValidateException) {
                $status = 200;
                $msg = $e->getMessage();
            } elseif ($this->app->isDebug() && $e->getMessage() !== '') {
                $msg = $e->getMessage();
            }
            return json(['code' => $code, 'msg' => $msg], $status);
        }
        return parent::render($request, $e);
    }
}
