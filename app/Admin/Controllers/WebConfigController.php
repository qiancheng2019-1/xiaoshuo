<?php

namespace App\Admin\Controllers;

use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Admin\Models\ConfigModel;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\Cache;

class WebConfigController extends AdminController {
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('fiction.ad');
    }

    public function form()
    {
        $form = new Form(new ConfigModel());

        $form->display('id', 'ID');
        $form->text('name', trans('fiction.keyword'))->rules('max:64')->default('');
        $form->textarea('value', trans('fiction.desc'))->rules('max:255')->default('');

        //保存后回调
        $form->saved(function (Form $form) {
            foreach (ConfigModel::all(['key', 'value']) as $item) $config['env.' . $item['key']] = $item['value'];
            Cache::forever('config', $config);
        });

        return $form;
    }

    protected function grid()
    {
        $grid = new Grid(new ConfigModel());
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disableActions();
        $grid->disableFilter();

        $grid->column('key', 'ID')->sortable();
        $grid->column('name', trans('admin.name'));
        $grid->column('value', trans('fiction.info').('#JS'))->editable('textarea');
        $grid->column('desc', trans('fiction.desc'));

        return $grid;
    }
}
