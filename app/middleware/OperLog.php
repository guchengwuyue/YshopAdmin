<?php
declare(strict_types=1);

namespace app\middleware;

use app\common\Auth;
use think\facade\Db;
use think\Request;

/**
 * 操作日志中间件（简化：POST 写操作记录）
 */
class OperLog
{
    /**
     * 记录 POST 写操作日志
     */
    public function handle(Request $request, \Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);

        try {
            if (strtoupper($request->method()) === 'POST') {
                $path = $request->pathinfo();
                // 排除登录与纯查询 list
                if (!str_contains($path, 'login') && !preg_match('#/list$#', $path)) {
                    $status = 0;
                    $errorMsg = '';
                    $content = method_exists($response, 'getContent') ? (string) $response->getContent() : '';
                    if ($content !== '') {
                        $json = json_decode($content, true);
                        if (is_array($json) && isset($json['code']) && (string) $json['code'] !== '0' && $json['code'] !== 0) {
                            $status = 1;
                            $errorMsg = (string) ($json['msg'] ?? '');
                        }
                    }
                    $cost = (int) ((microtime(true) - $start) * 1000);
                    Db::name('sys_oper_log')->insert([
                        'title'         => $request->rule()?->getOption('title') ?: $path,
                        'business_type' => 0,
                        'method'        => $request->controller() . '/' . $request->action(),
                        'request_method'=> $request->method(),
                        'operator_type' => 1,
                        'oper_name'     => Auth::getLoginName(),
                        'dept_name'     => '',
                        'oper_url'      => '/' . ltrim($path, '/'),
                        'oper_ip'       => $request->ip(),
                        'oper_location' => '',
                        'oper_param'    => mb_substr(json_encode($request->post(), JSON_UNESCAPED_UNICODE) ?: '', 0, 2000),
                        'json_result'   => mb_substr($content, 0, 2000),
                        'status'        => $status,
                        'error_msg'     => $errorMsg,
                        'oper_time'     => date('Y-m-d H:i:s'),
                        'cost_time'     => $cost,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return $response;
    }
}
