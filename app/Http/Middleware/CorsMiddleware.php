<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * @var array
     */
    private $allowedHeaders = [
        'Content-Type',
        'Authorization',
        'X-Requested-With'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$allowedOrigin = env('ALLOWED_ORIGIN')) {
            return response()->json(['message' => 'No valid cors settings found!'], 401);
        }

        $headers = [
            'Access-Control-Allow-Origin' => $allowedOrigin,
            'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Headers' => $this->getAllowedHeaders()
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);

        foreach ($headers as $key => $value) {
            if (method_exists($response, 'header')) {
                $response->header($key, $value);
            }
        }

        return $response;
    }

    /**
     * @return string
     */
    private function getAllowedHeaders()
    {
        return implode(', ', $this->allowedHeaders);
    }
}
