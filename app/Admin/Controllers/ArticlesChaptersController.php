<?php

namespace App\Admin\Controllers;

use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Admin\Models\ArticlesCategoryModel;
use Encore\Admin\Controllers\AdminController;

class ArticlesChaptersController extends AdminController {
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('book');
    }

    public function form()
    {
        $form = new Form(new ArticlesCategoryModel());

        $form->display('id', 'ID');

        $form->text('name', trans('admin.name'))->rules('required|max:64');
        $form->text('title', trans('admin.title'))->rules('max:64')->default('');
        $form->text('keyword', trans('fiction.keyword'))->rules('max:64')->default('');
        $form->textarea('desc', trans('fiction.desc'))->rules('max:255')->default('');

        $form->number('order', trans('admin.order'))->default(99);
        $form->switch('status', trans('fiction.status'))->default(1);

        // 在表单提交前调用
        $form->saving(function (Form $form) {
            //...
            $form->title = $form->title ?: $form->name . '标题';
            $form->desc = $form->desc ?: $form->name . '描述';
            $form->keyword = $form->keyword ?: $form->name . '关键字';
        });

        return $form;
    }

    protected function grid()
    {
        $grid = new Grid();
        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableView();
            if (in_array($actions->getKey(), [1, 2, 3, 4, 5, 6])) {
                $actions->disableDelete();
            }
        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('name', trans('admin.name'));
        $grid->column('order', trans('admin.order'))->sortable();
        $grid->column('status', trans('fiction.push'))->switch()->sortable();

        return $grid;
    }
}
