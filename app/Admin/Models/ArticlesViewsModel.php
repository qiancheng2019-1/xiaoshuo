<?php


namespace App\Admin\Models;
use Illuminate\Database\Eloquent\Model;

class ArticlesViewsModel extends Model
{
    protected $table = 'articles_views';

    protected $primaryKey = 'article_id';

    public $timestamps = false;

    public function articles()
    {
        return $this->belongsTo('App\Admin\Models\Articles','article_id');
    }
}
