<?php

namespace App\Admin\Forms;

use App\Admin\Models\ConfigModel;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class Setting extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = '网站设置';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        if ($request->exists('web_icon')){
            $filePath = Storage::disk('public')->putFileAs('', $request->file('web_icon'),'favicon.png');
            ConfigModel::query()->where(['key'=>'web_icon'])->update(['value'=>$filePath]);
        }

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
        $this->text('web_name', trans('fiction.web').trans('admin.title'))->rules('required|max:64')
            ->help('标签：{web_name},可用于调用');
        $this->image('web_icon', trans('fiction.web').trans('admin.icon'))->disk('public');
        $this->text('web_desc', trans('fiction.web').trans('fiction.desc'))->rules('required|max:255');
        $this->text('file_dir', trans('admin.upload').trans('admin.uri'))->rules('required|max:64')
            ->help('上传文件储存目录');
        $this->text('views_between', trans('fiction.views_between'))->rules('required|alpha_dash')
            ->placeholder('1-1')
            ->help('阅读量虚拟#3-10表示随机增加3到10之间的点击数。如需固定请填写如1-1');
        $this->number('cache_select_time', trans('fiction.cache_select_time'))->rules('required|numeric')
            ->help('小说模块缓存时间，单位：秒');
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
