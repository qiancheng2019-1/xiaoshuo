<?php

namespace App\Listeners;

use Dingo\Api\Event\ResponseWasMorphed;
use Illuminate\Contracts\Pagination\Paginator;

class DingoApi
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
     * @param object $event
     * @return void
     */
    public function handle(ResponseWasMorphed $event)
    {
        //公共头部
        $origin = \request()->header('ORIGIN', '*');
        $event->response->headers->set('Access-Control-Allow-Origin', $origin);
        $event->response->headers->set('Access-Control-Allow-Credentials', 'true');
        $event->response->headers->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE');
        $event->response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        //分页对象处理
        if (isset($event->content['data']) and $event->content['data'] instanceof Paginator) {
            $paginate = $event->content['data'];
            $event->response->headers->set(
                'link',
                sprintf(
                    '<%s>; rel="first",<%s>; rel="last",<%s>; rel="next", <%s>; rel="prev"',
                    $paginate->url(1),
                    method_exists($paginate, 'lastPage') ? $paginate->url($paginate->lastPage()) : '',
                    $paginate->nextPageUrl(),
                    $paginate->previousPageUrl()
                )
            );

            $result['data'] = $paginate->items();
            $result['per_page'] = $paginate->perPage();
            $result['last_page'] = method_exists($paginate, 'lastPage') ? $paginate->lastPage() : 0;
            $result['current_page'] = $paginate->currentPage();
            $result['count'] = $paginate->count();
            $result['total'] = method_exists($paginate, 'total') ? $paginate->total() : 0;
            $event->content['data'] = $result;
        }
    }
}
