<?php

namespace App\Http\Middleware;

use Closure;

class ApiCache
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $uri = $request->getUri();
        $params = $request->all();
        $keyStr = $uri . '::' . json_encode($params);
        $data = \Illuminate\Support\Facades\Cache::get(md5($keyStr));

        if ($data) {
            $data = json_decode($data, true);
            $response = response()->json($data)
                ->header('Cache-Control','max-age='.config('env.cache_select_time'))
                ->setStatusCode($data['status_code']);

            define('CACHE_IF',true);
            return $response;
        }else
            define('CACHE_IF',false);

        return $next($request);
    }
}
