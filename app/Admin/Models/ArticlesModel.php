<?php


namespace App\Admin\Models;
use Illuminate\Database\Eloquent\Model;

class ArticlesModel extends Model
{
    protected $table = 'articles_test';

    public function views()
    {
        return $this->hasOne('App\Admin\Models\ArticlesViewsModel','article_id');
    }
}

