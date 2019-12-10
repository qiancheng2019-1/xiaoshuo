<?php


namespace App\V1\Admin\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * 更新给定用户的信息。
     *
     * @param  Request  $request
     * @param  string  $id
     * @return Response
     */
    public function test()
    {
        return 'asd';
    }

    public function getCaptcha(){

    }

    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = [])
    {

    }
}
