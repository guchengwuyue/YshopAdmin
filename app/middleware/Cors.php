<?php
declare(strict_types=1);

namespace app\middleware;

use think\Request;
use think\Response;

/**
 * 双网关 CORS。预检直接 204，业务响应附带允许头。
 */
class Cors
{
    public function handle(Request $request, \Closure $next)
    {
        if (strtoupper($request->method()) === 'OPTIONS') {
            return $this->withCors($request, response('', 204));
        }
        return $this->withCors($request, $next($request));
    }

    protected function withCors(Request $request, Response $response): Response
    {
        $origin = $this->allowOrigin($request);
        $headers = [
            'Access-Control-Allow-Origin'  => $origin,
            'Access-Control-Allow-Headers' => (string) config('cors.allow_headers'),
            'Access-Control-Allow-Methods' => (string) config('cors.allow_methods'),
            'Access-Control-Max-Age'       => (string) config('cors.max_age', 86400),
            'Vary'                         => 'Origin',
        ];
        if ($origin !== '*') {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }
        return $response->header($headers);
    }

    protected function allowOrigin(Request $request): string
    {
        $configured = trim((string) config('cors.origins', '*'));
        $origin = trim((string) $request->header('origin', ''));
        if ($configured === '' || $configured === '*') {
            return '*';
        }
        $list = array_values(array_filter(array_map('trim', explode(',', $configured))));
        if ($origin !== '' && in_array($origin, $list, true)) {
            return $origin;
        }
        return 'null';
    }
}
