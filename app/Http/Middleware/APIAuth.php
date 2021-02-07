<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class APIAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (App::environment() !== 'local') { //todo token local??
            // API token can get from header or request
            $apiToken = $request->header('X-Auth-Token', null);
            $apiToken = $request->api_token ?: $apiToken;

            if ($apiToken) {
                $userFound = User::where('api_token', '=', $apiToken)->first() ?: null;
            }

            if (!($apiToken && $userFound)) {
                return response('Unauthorized', 401);
            }
        }

        return $next($request);
    }
}
