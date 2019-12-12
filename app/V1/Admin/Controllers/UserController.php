<?php


namespace App\V1\Admin\Controllers;

use Dingo\Api\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Str;


class UserController extends BaseController
{
    use AuthenticatesUsers;

    public function username()
    {
        return 'username';
    }

    /**
     * @OA\Post(
     *     path="/token",
     *     tags={"user"},
     *     description="登录接口",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username","password","key","captcha"},
     *                 @OA\Property(
     *                     property="username",
     *                     default="admin",
     *                     description="用户账号",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     default="qweqwe",
     *                     description="密码",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="key",
     *                     description="验证码api凭证",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="captcha",
     *                     description="验证码",
     *                     type="string",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="user_id"),
     *              @OA\Property(property="nickname", type="string", description="用户昵称"),
     *              @OA\Property(property="username", type="string", description="用户账号"),
     *              @OA\Property(property="api_token", type="string", description="api_token凭证令牌")
     *          )
     *        )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        $captcha = $request->input('captcha');
        $key = $request->input('key');

        if (captcha_api_check($captcha, $key)) return $this->jsonResult('验证码检验不通过', 401, 100);

        if ($this->attemptLogin($request)) {
            $user = $this->guard('api')->user();
            $reuslt['id'] = $user['id'];
            $reuslt['nickname'] = $user['nickname'];
            $reuslt['username'] = $user['username'];
            $reuslt['api_token'] = $user['api_token'];

            //每次登陆刷新token
//            $token = Str::random(60);
//            $request->user()->forceFill([
//                'api_token' => hash('sha256', $token),
//            ])->save();
//            $reuslt['api_token'] = $token;

            return $this->jsonResult('登录成功', 201, 0, $reuslt);
        } else return $this->jsonResult('账号或密码错误', 401, 101);
    }

    /**
     * @OA\Delete(
     *     path="/token",
     *     tags={"user"},
     *     description="退出登录接口",
     *     security={
     *          {
     *              "Token":{}
     *          }
     *      },
     *     @OA\Response(
     *         response=204,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function loginOut(Request $request)
    {

        $user = $this->guard('api')->user();

        if ($user) {
            $user->api_api_token = null;
            $user->save();
        }

        return $this->jsonResult('退出登录成功', 204, 0, $user);
    }
}

