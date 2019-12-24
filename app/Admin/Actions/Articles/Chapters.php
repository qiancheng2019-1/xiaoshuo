<?php

namespace App\Admin\Actions\Articles;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;

class Chapters extends RowAction
{
    public $name = '章节列表';

    public function href()
    {
        return "/admin/articles/".$this->getKey()."/chapters";
    }

    public function handle(Model $model)
    {
        // $model ...
    }

}
