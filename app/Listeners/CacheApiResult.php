<?php

namespace App\Listeners;

use Illuminate\Foundation\Http\Events\RequestHandled;

class CacheApiResult
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RequestHandled  $event
     * @return void
     */
    public function handle(RequestHandled $event)
    {
        //$event->response->isCached这个属性是在后面的中间件里面加的，对象本身是没有这个属性的。
        if($event->request->isMethod('GET') and !defined('CACHE_IF')){
            //这里的key生成规则是我自己定义的，可以按需更改。
            $uri = $event->request->getUri();
            $params = $event->request->all();
            $keyStr = $uri . '::' . json_encode($params);

            $data = $event->response->header('Cache-Control','max-age='.config('env.cache_select_time'))->getContent();
            \Illuminate\Support\Facades\Cache::put(md5($keyStr), $data, config('env.cache_select_time'));
            //这里缓存一分钟，目前看来分钟好像是最小粒度了，以后需要再改进；可以按需改成其他缓存
        }
    }
}
