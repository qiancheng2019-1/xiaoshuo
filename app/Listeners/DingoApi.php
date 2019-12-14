<?php

namespace App\Listeners;

use Dingo\Api\Http\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Dingo\Api\Event\ResponseWasMorphed;

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
     * @param  object  $event
     * @return void
     */
    public function handle(ResponseWasMorphed $event)
    {
        $origin = \request()->header('ORIGIN', '*');
        $event->response->headers->set('Access-Control-Allow-Origin',$origin);
        $event->response->headers->set('Access-Control-Allow-Credentials','true');
        $event->response->headers->set('Access-Control-Allow-Methods','POST, GET, PUT, DELETE');
        $event->response->headers->set('Access-Control-Allow-Headers','Content-Type, Authorization');
    }
}
