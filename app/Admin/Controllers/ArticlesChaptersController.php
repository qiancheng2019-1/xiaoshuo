<?php

namespace App\Admin\Controllers;

use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Request;
use App\Admin\Models\ArticlesModel;
use App\Admin\Models\ArticlesChaptersModel;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Support\Facades\Storage;

class ArticlesChaptersController extends AdminController {

    function __construct() {
        define('ARTICLE_ID',Request::route('article_id',0));
    }

    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('fiction.book').trans('fiction.chapter');
    }


    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $articleChapters = new ArticlesChaptersModel();
        $chapter = $articleChapters->findOrFail(Request::route('chapter',0));
        $show = new Show($chapter);
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableDelete();
            });

        $show->divider();
        $show->field('chapter_id', 'ID');
        $show->field('chapter_name', trans('fiction.chapter'));
        $show->field('content',trans('fiction.content'))->unescape();

        return $show;
    }

    public function edit($id, Content $content)
    {
        return parent::edit(Request::route('chapter',0), $content); // TODO: Change the autogenerated stub
    }

    public function update($id)
    {
        return parent::update(Request::route('chapter',0));
    }

    public function destroy($id)
    {
        $articlesChaptersModel = new ArticlesChaptersModel();
        try {
            $articlesChaptersModel->destroyArr(Request::route('chapter',0));
            $response = [
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ];
        } catch (\Exception $exception) {
            $response = [
                'status'  => false,
                'message' => $exception->getMessage() ?: trans('admin.delete_failed'),
            ];
        }

        return response()->json($response);
    }

    public function form()
    {
        $form = new Form(new ArticlesChaptersModel());

        $form->display('id', 'ID');
        $form->text('title', trans('admin.title'))->rules('required|max:64');
        $form->text('link', trans('fiction.url'))->rules('max:64')->default('');
        $form->ckeditor('content', trans('fiction.content'))->default('&nbsp;&nbsp;')->rules('max:20480');

        return $form;
    }

    protected function grid()
    {
        $grid = new Grid(new ArticlesChaptersModel([]));
        $grid->disableFilter();
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });

        $grid->column('chapter_id', 'ID');
        $grid->column('chapter_name', trans('fiction.chapter'));

        return $grid;
    }
}


