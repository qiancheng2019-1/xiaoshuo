<?php


namespace App\V1\App\Controllers;

use App\Rules\mobile;
use App\User;
use Dingo\Api\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class UserController extends IndexController
{
    use AuthenticatesUsers;

    public function username()
    {
        return 'username';
    }

    protected function guard()
    {
        return Auth::guard('app');
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
     *                 required={"username","sms_code","key","captcha"},
     *                 @OA\Property(
     *                     property="username",
     *                     default="crazypeak",
     *                     description="用户账号",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="sms_code",
     *                     default="888888",
     *                     description="短信验证码",
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
            'username' => ['required', 'string', new mobile()],
            'sms_code' => 'required|integer|max:6',
        ]);

        //获取对应用户资料
        $user = $this->guard()->getProvider()->retrieveByCredentials($request->only($this->username(), 'password'));

        //登录验证
        if ($this->guard()->getProvider()->validateCredentials($user, $request->only($this->username(), 'password'))) {
            $this->guard()->setUser($user);
            $user = $this->guard()->user();
            $reuslt['id'] = $user->id;
            $reuslt['nickname'] = $user->nickname;
            $reuslt['username'] = $user->username;

            //每次登陆刷新token
            $token = Str::random(60);
            $request->user('app')->forceFill([
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
    public function loginOut()
    {
        $user = $this->guard()->user();

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
     *     summary="刷新token",
     *     description="需要token",
     *     security={{"Token":{}}},
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
        $user = $this->guard()->user();
        if ($user) {
            $token = hash('sha256', Str::random(60));
            $request->user('app')->forceFill([
                'api_token' => $token
            ])->save();
            return $this->apiReturn('修改token', 200, 0, ['api_token' => $token]);
        }
        $this->apiReturn();
    }

    /**
     * @OA\Post(
     *     path="/user",
     *     tags={"Users"},
     *     summary="注册",
     *     description="需要带上验证码key",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username","sms_code"},
     *                 @OA\Property(
     *                     property="username",
     *                     default="crazypeak",
     *                     description="用户账号#手机",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="sms_code",
     *                     default="qweqwe",
     *                     description="短信验证码",
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
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', new mobile()],
            'sms_code' => 'required|integer|6',
        ]);

        if ($this->guard()->getProvider()->retrieveByCredentials($request->only($this->username())))
            return $this->apiReturn('已存在用户账号', 422, 11);

        $token = Str::random(60);
        $user = User::create([
            'nickname' => '用户' . date('mdHis'),
            'username' => $data[$this->username()],
            'api_token' => hash('sha256', $token)
        ]);

        if ($user) {
            $this->guard()->setUser($user);
            $user = $this->guard()->user();
            $reuslt['id'] = $user->id;
            $reuslt['nickname'] = $user->nickname;
            $reuslt['username'] = $user->username;
            $result['api_token'] = $token;

            return $this->apiReturn('注册成功', 201, 0, $user);
        }

        return $this->apiReturn();
    }

    /**
     * @OA\Put(
     *     path="/user",
     *     tags={"Users"},
     *     summary="修改头像、昵称",
     *     description="需要token",
     *     security={{"Token":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"nickname","avatar"},
     *                 @OA\Property(
     *                     property="nickname",
     *                     default="crazypeak",
     *                     description="用户昵称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     default="/storage/default/C0Vw6a0Vr6FeqfWYLxxj8SQvhOIPeHcSS5584uki.jpeg",
     *                     description="上传后图片路径",
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
    public function put(Request $request)
    {
        $request->validate([
            'nickname' => 'string|max:128',
            'avatar' => 'string:max:128',
        ]);

        $user = $this->guard()->user();
        if ($user) {
            $request->user('app')->forceFill(
                $request->only('nickname','avatar')
            )->save();
            return $this->apiReturn('修改token', 200, 0, $user);
        }
    }

    /**
     * @OA\Get(
     *     path="/collect",
     *     tags={"Users"},
     *     summary="用户书架",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="page",
     *       in="query",
     *       required=true,
     *       description="当前页",
     *       @OA\Schema(
     *          type="integer",
     *          default="1",
     *       )
     *     ),
     *     @OA\Parameter(
     *       name="limit",
     *       in="query",
     *       required=true,
     *       description="每页个数",
     *       @OA\Schema(
     *          type="integer",
     *          default="10",
     *       )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="id", type="integer", description="书本id #article_id"),
     *              @OA\Property(property="title", type="string", description="书本名称"),
     *              @OA\Property(property="author", type="integer", description="作者"),
     *              @OA\Property(property="category", type="string", description="分类名称"),
     *              @OA\Property(property="thumb", type="integer", description="封面"),
     *              @OA\Property(property="info", type="integer", description="简介")
     *         ),
     *        )
     *     )
     * )
     */
    public function getCollect(int $user_id){

    }
}

