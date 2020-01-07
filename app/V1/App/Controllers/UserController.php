<?php


namespace App\V1\App\Controllers;

use App\Rules\mobile;
use App\User;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\V1\App\Models\UsersCollect;
use App\V1\App\Models\Articles;
use Illuminate\Support\Facades\Cache;
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
     *                     default="",
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
     *              ),
     *             example={{"id": 0,"nickname": "string","username": "string","api_token": "uubibEjHplZyWyq7AFrIoPSBjUXRzPTt3tPJ6JfgjdI563HixhGNdXBm2nGY"}}
     *        )
     *     )
     * )
     */
    public function login(Request $request)
    {
        //表单验证
        $request->validate([
            $this->username() => ['required', 'string', new mobile()], 'sms_code' => 'required|string|max:16',]);

        //获取对应用户资料
        $user = $this->guard()->getProvider()->createModel()->where($request->only($this->username()))->first();

        //登录验证
        if ($user and Cache::get(md5($user->username)) == $request->input('sms_code')) {
            $this->guard()->setUser($user);
            $user = $this->guard()->user();
            $reuslt['id'] = $user->id;
            $reuslt['nickname'] = $user->nickname;
            $reuslt['username'] = $user->username;

            //每次登陆刷新token
            $token = Str::random(60);
            $request->user('app')->forceFill([
                'api_token' => hash('sha256', $token),])->save();
            $reuslt['api_token'] = $token;

            Cache::forget(md5($user->username));
            return $this->apiReturn('登录成功', 201, 0, $reuslt);
        }
        return $this->apiReturn('账号不存在或短信验证错误', 401, 101);
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     tags={"Users"},
     *     summary="个人信息",
     *     description="需要token，退出后token失效需重新登录，右方锁型logo表示接口需要token",
     *     security={{"Token":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function get()
    {
        $user = $this->guard()->user();
        $reuslt['id'] = $user->id;
        $reuslt['nickname'] = $user->nickname;
        $reuslt['username'] = substr($user->username, 0, 3) . '****' . substr($user->username, 7);
        $reuslt['avatar'] = $user->avatar;
        return $this->apiReturn('个人信息', 200, 0, $reuslt);
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
     *     tags={"Invalid"},
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
                'api_token' => $token])->save();
            return $this->apiReturn('修改token', 200, 0, ['api_token' => $token]);
        }
        $this->apiReturn();
    }

    /**
     * @OA\Post(
     *     path="/user",
     *     tags={"Users"},
     *     summary="注册",
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
     *                     default="",
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
            'username' => ['required', 'string', new mobile()], 'sms_code' => 'required|string|max:6',]);

        if (Cache::get(md5($request->input($this->username()))) !== $request->input('sms_code'))
            return $this->apiReturn('验证错误', 401, 101);

        if ($this->guard()->getProvider()->createModel()->where($request->only($this->username()))->first(['id'])) return $this->apiReturn('已存在用户账号', 422, 11);

        $token = Str::random(60);
        $user = User::create([
            'nickname' => '用户' . date('mdHis'), 'username' => $data[$this->username()], 'api_token' => hash('sha256', $token)]);

        if ($user) {
            $this->guard()->setUser($user);
            $user = $this->guard()->user();
            $reuslt['id'] = $user->id;
            $reuslt['nickname'] = $user->nickname;
            $reuslt['username'] = substr($user->username, 0, 3) . '****' . substr($user->username, 7);
            $result['api_token'] = $token;

            return $this->apiReturn('注册成功', 201, 0, $result);
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
     *                 @OA\Property(
     *                     property="nickname",
     *                     default="NULL",
     *                     description="用户昵称",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     default="NULL",
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
            'nickname' => 'string|max:128', 'avatar' => 'string|max:128',]);

        $user = $this->guard()->user();
        $request->user('app')->forceFill($request->only('nickname', 'avatar'))->save();
        $reuslt['id'] = $user->id;
        $reuslt['nickname'] = $user->nickname;
        $reuslt['username'] = substr($user->username, 0, 3) . '****' . substr($user->username, 7);

        return $this->apiReturn('个人资料', 200, 0, $reuslt);
    }

    /**
     * @OA\Post(
     *     path="/user/collect",
     *     tags={"Users"},
     *     summary="加入书架",
     *     security={{"Token":{}}},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"article_id"},
     *                 @OA\Property(
     *                     property="article_id",
     *                     description="书本id",
     *                     type="integer",
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SUCCESS/成功"
     *     )
     * )
     */
    public function postCollect(Request $request)
    {
        $article_id = $request->input('article_id', 0);
        $article = Articles::get($article_id, ['id']);
        if (!$article) return $this->apiReturn('书本数据不存在', 404, 21);

        $user = $this->guard()->user();

        $collect = new UsersCollect();
        $collect->user_id = $user->id;
        $collect->article_id = $article_id;
        $last_chapter_id = Cache::get($request->ip() . '/' . $article_id, 0) ?: Cache::get($user->id . '/' . $article_id, 0);

        if ($item = $collect->withTrashed()->where(['user_id'=>$user->id,'article_id'=>$article_id])->first()) {
            $item->restore();
            $item->forceFill([
                'last_chapter_id' => $last_chapter_id])->save();
        } else
            $collect->forceFill([
                'last_chapter_id' => $last_chapter_id])->save();

        return $this->apiReturn('收藏成功', 200, 0);
    }

    /**
     * @OA\Delete(
     *     path="/user/collect/{id}",
     *     tags={"Users"},
     *     summary="删除收藏#可批量'-'分隔id",
     *     security={{"Token":{}}},
     *     @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       @OA\Schema(
     *          type="string",
     *          default="1-2-3",
     *       )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="SUCCESS/成功"
     *     )
     * )
     * @param $ids
     * @return mixed
     */
    public function deleteCollect($ids)
    {
        $user = $this->guard()->user();
        $collect = new UsersCollect();

        foreach (strstr($ids, '-') ? explode('-', $ids) : [$ids] as $id) {
            $collect->where(['user_id' => $user->id, 'article_id' => $id])->delete();
        }
        return $this->apiReturn('已取消收藏', 204, 0);
    }

    /**
     * @OA\Get(
     *     path="/user/collect",
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
     *              @OA\Property(property="info", type="integer", description="简介"),
     *              @OA\Property(property="last_view", type="string", description="用户最后看的章节"),
     *              @OA\Property(property="last_view_id", type="integer", description="用户最后看的章节"),
     *              @OA\Property(property="last_chapter", type="string", description="书本最新章节"),
     *              @OA\Property(property="last_chapter_id", type="integer", description="书本最新章节")
     *         ),
     *        )
     *     )
     * )
     */
    public function getCollect(Request $request)
    {
//        $Storage = Storage::disk('local');

        $user = $this->guard()->user();
        $collect = new UsersCollect();

        $list = $collect->query()->where(['user_id' => $user->id])->orderByDesc('updated_at')->paginate($request->query('limit', 10), ['id', 'article_id', 'last_chapter_id'], 'page', $request->query('page', 1));
        foreach ($list as $key => &$item) {
            //过滤不存在书本数据
            if (!$item->article) {
                $collect->query()->where(['id' => $item->id])->delete();
                unset($list[$key]);
                continue;
            }

            !$item->last_chapter_id
                and $item->last_chapter_id = $item->article->getChapter()->orderBy('chapter_id')->first()->chapter_id
                and $item->save();

            $item->id = $item->article_id;

            $item->title = $item->article->title;
            $item->author = $item->article->author;
            $item->category = $item->article->category;
            $item->thumb = $item->article->thumb;
            $item->info = $item->article->info;

//            $storage_id = floor($item->article_id / 1000) . '/' . $item->article_id;
//            $chapter = $Storage->exists($storage_id . '/chapters') ? json_decode($Storage->get($storage_id . '/chapters'), true) : [];
//
//            $item->last_view = $chapter[$item->last_chapter_id]['title'] ?? '';
//            $item->last_view_id = $item->last_chapter_id;

            $item->last_view = $item->article->getChapter()->where(['chapter_id' => $item->last_chapter_id])->first()->chapter_name;
            $item->last_view_id = $item->last_chapter_id;

            $item->last_chapter = $item->article->last_chapter;
            $item->last_chapter_id = $item->article->last_chapter_id;

            $item->updated_at = $item->article->updated_at;

            unset($item->article_id);
            unset($item->article);
        }

        return $this->apiReturn('收藏列表', 200, 0, $list);
    }
}

