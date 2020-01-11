<?php

namespace App\Admin\Forms;

use App\Admin\Models\ConfigModel;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
        //dump($request->all());
        foreach ($request->post() as $key => $item){
            $c = ConfigModel::query()->where(['key'=>$key])->first();
            if (!$c) continue;

            $c->value = $item;
            $c->save();
        }

        foreach (ConfigModel::all(['key', 'value']) as $item) $config['env.' . $item['key']] = $item['value'];
        Cache::forever('config', $config);
        admin_success('数据处理成功.');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('web_name', trans('fiction.web').trans('admin.title'))->rules('required|max:64');
        $this->image('web_icon', trans('fiction.web').trans('admin.icon'))->disk('public')
            ->default(config('env.web_icon','/favicon.png'))
            ->uniqueName();
        $this->text('web_desc', trans('fiction.web').trans('fiction.desc'))->rules('required|max:255');
        $this->text('file_dir', trans('admin.upload').trans('admin.uri'))->rules('required|max:64');
        $this->text('views_between', trans('fiction.views_between'))->rules('required|alpha_dash');
        $this->text('cache_select_time', trans('fiction.cache_select_time'))->rules('required|numeric');
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
