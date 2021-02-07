<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRoute
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     * @throws \JsonException
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $log = [
            'REQUEST_BODY' => $request->all(),
            'RESPONSE' => $response->getContent(),
        ];

        Log::channel('api')->info(
            implode(' ',
                [
                    $request->getMethod(),
                    $request->getUri(),
                    json_encode($log, JSON_THROW_ON_ERROR),
                ]
            )
        );

        return $response;
    }
}
