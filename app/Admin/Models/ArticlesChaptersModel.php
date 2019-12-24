<?php


namespace App\Admin\Models;
use Illuminate\Database\Eloquent\Model;

class ArticlesChaptersModel extends Model
{
    protected $table = 'articles';

    public function views()
    {
        return $this->hasOne('App\Admin\Models\ArticlesViewsModel','article_id');
    }
}

