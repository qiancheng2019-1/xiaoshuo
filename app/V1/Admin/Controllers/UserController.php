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
     *     tags={"Users"},
     *     summary="登录",
     *     description="需要带上验证码key",
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
        //表单验证
        $request->validate([
            'username' => 'required|max:64',
            'password' => 'required|max:64',
            'key' => 'required|max:64',
            'captcha' => 'required|max:8',
        ]);

        //验证码验证
        if (!captcha_api_check($request->input('captcha'), $request->input('key'))) return $this->apiReturn('验证码检验不通过', 401, 100);

        //登录验证
        $this->validateLogin($request);
        if ($this->attemptLogin($request)) {
            $user = $this->guard('api')->user();
            $reuslt['id'] = $user->id;
            $reuslt['nickname'] = $user->nickname;
            $reuslt['username'] = $user->username;

            //每次登陆刷新token
            $token = Str::random(60);
            $request->user()->forceFill([
                'api_token' => hash('sha256', $token),
            ])->save();
            $reuslt['api_token'] = $token;

            return $this->apiReturn('登录成功', 201, 0, $reuslt);
        } else return $this->apiReturn('账号或密码错误', 401, 101);
    }

    /**
     * @OA\Delete(
     *     path="/token",
     *     tags={"Users"},
     *     summary="退出登录",
     *     description="需要token，退出后token失效需重新登录，右方锁型logo表示接口需要token",
     *     security={{"Token":{}}},
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
            $user->api_token = '';
            $user->save();
        }

        return $this->apiReturn('退出登录成功', 204, 0, $user);
    }

    /**
     * @OA\Put(
     *     path="/token",
     *     tags={"Users"},
     *     summary="修改密码",
     *     description="需要token，退出后token失效需重新登录，右方锁型logo表示接口需要token",
     *     security={
     *          {
     *              "Token":{}
     *          }
     *      },
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"password","password_rest"},
     *                 @OA\Property(
     *                     property="password",
     *                     default="qweqwe",
     *                     description="密码",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password_reset",
     *                     default="qweqwe",
     *                     description="重复输入密码",
     *                     type="string",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="api_token", type="string", description="api_token凭证令牌")
     *          )
     *        )
     *     )
     * )
     */
    public function changePWD(Request $request)
    {
        //表单验证
        $request->validate([
            'password' => 'required|max:64',
            'password_reset' => 'required|max:64',
        ]);

        if ($request->input('password') !== $request->input('password_reset')) return $this->apiReturn('验证码检验不通过', 422, 100);

        $user = $this->guard('api')->user();
        if ($user) {
            $token = hash('sha256',Str::random(60));
            $request->user()->forceFill([
                'password' => bcrypt($request->input('password')),
                'api_token' => $token
            ])->save();
            return $this->apiReturn('修改密码成功', 200, 0, ['api_token' => $token]);
        }
        $this->apiReturn();
    }
}

