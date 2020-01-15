<?php


namespace App\Admin\Models;
use App\Api\Basis\ReptileModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ArticlesModel extends Model
{
//    protected $table = 'articles_test';
    protected $table = 'articles';

    public function views()
    {
        return $this->hasOne('App\Admin\Models\ArticlesViewsModel','article_id');
    }

    public static function with($relations)
    {
        return new static;
    }

    // 获取单项数据展示在form中
    public static function findOrFail(int $id)
    {
        if (!Storage::disk('local')->exists(floor($id / 1000) . '/' . $id . '/chapters')){
            $reptile = new ReptileModel();
            $reptile->getArticle($id,self::query()->where(['id'=>$id])->value('url'));
        }else die;

        return self::query()->find($id,['*']);
    }
}

