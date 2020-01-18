<?php

namespace App\Admin\Controllers;

use Encore\Admin\Grid;
use Encore\Admin\Form;
use App\Admin\Models\ArticlesModel;
use App\Admin\Actions\Articles\Reptile;
use App\Admin\Actions\Articles\Chapters;
use App\Admin\Models\ArticlesCategoryModel;
use Encore\Admin\Controllers\AdminController;

class ArticlesController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('fiction.book');
    }

    public function form()
    {
        $form = new Form(new ArticlesModel());

        $form->display('id', 'ID');

        $form->text('title', trans('admin.title'))->rules('required|max:64');
        $form->text('author', trans('fiction.author'))->rules('required|max:64');
        $form->image('thumb', trans('fiction.thumb'))->uniqueName()->default('');
        $form->ckeditor('info', trans('fiction.info'))->default('&nbsp;&nbsp;')->rules('max:512');

        $category = ArticlesCategoryModel::query()->where(['status'=>1])->pluck('name','id');
        $form->select('category_id', trans('fiction.category'))->options($category)->default(1)->rules('required');
        $form->hidden('category');

        $form->number('views.week_views', trans('fiction.week_views'))->default(0);
        $form->number('views.month_views', trans('fiction.month_views'))->default(0);
        $form->number('views.total_views', trans('fiction.total_views'))->default(0);

        $form->switch('is_push', trans('fiction.push'));
        $form->switch('is_full', trans('fiction.full'));
        $form->switch('is_original', trans('fiction.original'));
        $form->switch('status', trans('fiction.status'))->default(1);

        // 在表单提交前调用
        $form->saving(function (Form $form) {
            //...
            $category = ArticlesCategoryModel::query()->find($form->category_id);
            $category and $form->category = $category->name;
        });

        return $form;
    }

    protected function grid(){
        $grid = new Grid(new ArticlesModel());
        $grid->actions(function ($actions) {
            // 去掉查看
            $actions->disableView();
            $actions->add(new Chapters());
        });
        $grid->filter(function($filter){
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->where(function ($query) {
                $query->where('title', 'like', "{$this->input}%")
                    ->orWhere('author', 'like', "{$this->input}%");
            }, trans('admin.title').' or '.trans('fiction.author'));

            foreach (ArticlesCategoryModel::query()->where(['status'=>1])->get(['id','name']) as $item) $category[$item['id']] = $item['name'];
            $filter->equal('category_id',trans('fiction.category'))->select($category ?? []);
        });
//        $grid->tools(function (Grid\Tools $tools) {
//            $tools->append(new Reptile());
//        });

        $grid->column('id', 'ID')->sortable();
        $grid->column('title', trans('admin.title'));
        $grid->column('category', trans('fiction.category'))->sortable();
        $grid->column('author', trans('fiction.author'));
        $grid->column('url', trans('fiction.url'));
        $grid->column('views.week_views',trans('fiction.week_views'))->sortable();
        $grid->column('views.month_views', trans('fiction.month_views'))->sortable();
        $grid->column('views.total_views', trans('fiction.total_views'))->sortable();
        $grid->column('is_push', trans('fiction.push'))->switch()->sortable();
        $grid->column('status', trans('fiction.status'))->switch()->sortable();
        $grid->column('created_at', trans('admin.created_at'))->sortable();

        return $grid;
    }
}
