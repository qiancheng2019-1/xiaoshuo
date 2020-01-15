<?php

namespace App\Admin\Controllers;

use App\Admin\Forms\Setting;
use App\Admin\Forms\SmsSetting;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Admin\Models\ConfigModel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Cache;
use Encore\Admin\Widgets\Tab;

class WebConfigController extends AdminController
{

    public function index(Content $content)
    {
        $forms = [
            'config' => Setting::class,
            'sms' => SmsSetting::class,
        ];

        return $content
            ->title('系统设置')
            ->body(Tab::forms($forms));
    }

//    /**
//     * {@inheritdoc}
//     */
//    protected function title()
//    {
//        return trans('fiction.ad');
//    }
//
//    public function form()
//    {
//        $form = new Form(new ConfigModel());
//
//        $form->text('web_name', trans('fiction.keyword'))->rules('max:64');
//        $form->text('web_icon', trans('fiction.keyword'))->rules('max:64');
//        $form->text('web_desc', trans('fiction.keyword'))->rules('max:64');
//        $form->text('file_dir', trans('fiction.uri'))->rules('max:64');
//        $form->text('views_between', trans('fiction.keyword'))->rules('max:64');
//        $form->text('cache_select_time', trans('fiction.keyword'))->rules('max:64');
//
//        //保存后回调
//        $form->saved(function (Form $form) {
//            foreach (ConfigModel::all(['key', 'value']) as $item) $config['env.' . $item['key']] = $item['value'];
//            Cache::forever('config', $config);
//        });
//
//        return $form;
//    }
//
//    protected function grid()
//    {
//        $grid = new Grid(new ConfigModel());
//        $grid->disableBatchActions();
//        $grid->disableCreateButton();
//        $grid->disablePagination();
//        $grid->disableActions();
//        $grid->disableFilter();
//
//        $grid->column('key', 'ID')->sortable();
//        $grid->column('name', trans('admin.name'));
//        $grid->column('value', trans('fiction.info').('#JS'))->editable('textarea');
//        $grid->column('desc', trans('fiction.desc'));
//
//        return $grid;
//    }
}
