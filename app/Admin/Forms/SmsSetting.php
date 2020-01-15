<?php

namespace App\Admin\Forms;

use App\Admin\Models\ConfigModel;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SmsSetting extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = '短信设置';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        //dump($request->all());
        foreach ($request->post() as $key => $item){
            $c = ConfigModel::query()->where(['key'=>$key])->first();
            if (!$c) continue;

            $c->value = $item;
            $c->save();
        }

        admin_success('数据处理成功.');
        Cache::forget('config');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('253短信平台');
        $this->number('sms_cache', trans('sms.sms').trans('sms.cache'))->rules('required|numeric|min:1|max:60')
            ->help('验证码类短信有效期，标签：{sms_cache}，单位：分钟')
            ->placeholder('单位：分钟');
        $this->text('sms_title', trans('sms.sms').trans('sms.title').'【】')->rules('required|string|max:64')
            ->help('短信签名，模板自动添加')
            ->placeholder('模板自动添加');
        $this->text('sms_api_id', 'api_ID')->rules('required|alpha_dash');
        $this->text('sms_api_pwd', 'api_'.trans('admin.password'))->rules('required|alpha_dash');
        $this->text('sms_api_url', 'api_'.trans('admin.uri'))->rules('required')->default('http://')
            ->help('253后台提供专属接口url')
            ->placeholder('请输入http://XXXX');

        $this->divider('短信模板');
        $this->textarea('sms_code_template', trans('sms.sms').trans('admin.captcha'))->rules('required|string')
            ->help('{sms_code}=>验证码，{sms_cache}=>有效时间')
            ->placeholder('例：您好，您的验证码为：{sms_code}，请在{sms_cache}分钟之内填写，请不要把验证码泄露给别人，以防信息外泄');

    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        $data = [];
        foreach (ConfigModel::all(['key', 'value']) as $item){
            $data[$item->key] = $item->value;
        }

        return $data;
    }
}
