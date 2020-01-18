<?php


namespace App\Admin\Models;
use App\Api\Basis\ReptileModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticlesChaptersModel extends Model
{

    private $Storage,$storage_id;
    protected $table = 'articles_chapter_test';

    function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->Storage = Storage::disk('local');
        $this->storage_id = floor(ARTICLE_ID / 1000) . '/' . ARTICLE_ID;
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('article', function (Builder $builder) {
            $builder->where('title_id', ARTICLE_ID)->orderBy('chapter_id');
        });
    }
//

//    private function existsChapter(){
//        if (!$this->Storage->exists($this->storage_id . '/chapters')){
//            $reptile = new ReptileModel();
//            $reptile->getArticle(ARTICLE_ID,ArticlesModel::query()->where(['id'=>ARTICLE_ID])->value('url'));
//        }
//    }
//
//    public function paginate()
//    {
//        $perPage = Request::get('per_page', 10);
//        $page = Request::get('page', 1);
//
//        $this->existsChapter();
//        $chapter_list = json_decode($this->Storage->get($this->storage_id . '/chapters'),true) ?: [];
//        foreach ($chapter_list as $item){
//            $data[] = ['id'=>$item['id'],'chapter_id'=>$item['id'],'chapter_name'=>$item['title']];
//        }
//
//        $total = count($data);
//
//        $data = array_chunk($data, $perPage);
//        $movies = static::hydrate($data[$page-1]??[]);
//
//        $paginator = new LengthAwarePaginator($movies, $total, $perPage);
//
//        $paginator->setPath(url()->current());
//
//        return $paginator;
//    }
//
//    // 获取单项数据展示在form中
//    public function findOrFail($id)
//    {
//        $this->existsChapter();
//        $chapter_list = json_decode($this->Storage->get($this->storage_id . '/chapters'),true) ?: [];
//        if (!$chapter_list[$id]) return false;
//
//        $article = ArticlesModel::query()->find(ARTICLE_ID,['id','url']);
//        if (!$this->Storage->exists($this->storage_id . '/' .$id)){
//            $reptile = new ReptileModel();
//            $reptile->getChapter($article,$chapter_list[$id]);
//        }
//
//        $data = json_decode($this->Storage->get($this->storage_id . '/' . $id),true) ?: [];
//        $data['link'] = $chapter_list[$id]['link'];
//        $data['chapter_name'] = $chapter_list[$id]['title'];
//        $data['chapter_id'] = $data['id'] = $id;
//
////        $data['content'] =  Storage::disk('sftp')->get($article->category_id . '/' . $article->pinyin . '/' . $id . '.txt');
////        $data['content'] =
//
//        return static::newFromBuilder($data);
//    }
//
//    // 保存提交的form数据
//    public function save(array $options = [])
//    {
//        $attributes = $this->getAttributes();
//
//        //更新保存
//        if ($this->exists) {
//            $chapter['title'] = $attributes['title'];
//            $chapter['content'] = $attributes['content'];
//            $this->Storage->put($this->storage_id . '/' . $this->id, json_encode($chapter));
//
//            $chapters_list = $this->Storage->exists($this->storage_id . '/chapters') ? json_decode($this->Storage->get($this->storage_id . '/chapters'), true) : [];
//
//            $chapters_list[$this->id]['title'] = $attributes['title'];
//            $chapters_list[$this->id]['link'] = $attributes['link'];
//            $this->Storage->put($this->storage_id . '/chapters', json_encode($chapters_list));
//        }
//
//        //插入数据
//        else {
//            $title = ['title' => $attributes['title'],'link' => $attributes['link']];
//            $chapter_list = $this->Storage->exists($this->storage_id . '/chapters') ? json_decode($this->Storage->get($this->storage_id . '/chapters'), true) : [];
//
//            $chapter_id = end($chapter_list)['id'];
//            $chapter_list[$chapter_id]= $title + ['id' => $chapter_id];
//            $this->Storage->put($this->storage_id . '/chapters', json_encode($chapter_list));
//
//            $chapter['title'] = $attributes['title'];
//            $chapter['content'] = $attributes['content'];
//            $this->Storage->put($this->storage_id . '/' . $chapter_id, json_encode($chapter));
//        }
//
//        // save $attributes
//        return true;
//    }
//
//    public function destroyArr($ids)
//    {
//        $chapter_list = $this->Storage->exists($this->storage_id . '/chapters') ? json_decode($this->Storage->get($this->storage_id . '/chapters'), true) : [];
//        foreach(explode(',', $ids) as $id){
//            $this->Storage->delete($this->storage_id . '/' . $id);
//            if (isset($chapter_list[$id])) unset($chapter_list[$id]);
//        }
//
//        //清空目录 or 最后章节获取
//        if (empty($chapter_list)) {
//            $this->Storage->delete($this->storage_id . '/chapters');
//            $last_chapter = [
//                'id'    => 0,
//                'title' => ''];
//        } else {
//            $this->Storage->put($this->storage_id . '/chapters', json_encode($chapter_list));
//            $last_chapter = end($chapter_list);
//        }
//
//        ArticlesModel::query()->where(['id'=>ARTICLE_ID])->update([
//            'last_chapter_id' => $last_chapter['id'],
//            'last_chapter'    => $last_chapter['title'],
//        ]);
//
//        return true;
//    }
//
//    public static function with($relations)
//    {
//        return new static;
//    }
//
//    // 覆盖`orderBy`来收集排序的字段和方向
//    public function orderBy($column, $direction = 'asc')
//    {
//
//    }
//
//    // 覆盖`where`来收集筛选的字段和条件
//    public function where($column, $operator = null, $value = null, $boolean = 'and')
//    {
//
//    }
}

