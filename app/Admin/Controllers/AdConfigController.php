<?php

namespace App\Admin\Controllers;

use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Admin\Models\AdModel;
use Encore\Admin\Controllers\AdminController;

class AdConfigController extends AdminController {
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('fiction.ad');
    }

    public function form()
    {
        $form = new Form(new AdModel());

        $form->display('id', 'ID');
        $form->text('name', trans('fiction.keyword'))->rules('max:64')->default('');
        $form->textarea('value', trans('fiction.desc'))->rules('max:255')->default('');

        return $form;
    }

    protected function grid()
    {
        $grid = new Grid(new AdModel());
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disableActions();
        $grid->disableFilter();

        $grid->column('key', 'ID')->sortable();
        $grid->column('name', trans('admin.name'))->editable();
        $grid->column('value', trans('fiction.info').('#HTML'))->editable('textarea');

        return $grid;
    }
}
