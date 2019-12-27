<?php


namespace App\Admin\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticlesChaptersModel extends Model
{
    private $Storage,$storage_id;
    function __construct(array $attributes = []) {
        parent::__construct($attributes);
        $this->Storage = Storage::disk('local');
        $this->storage_id = floor(ARTICLE_ID / 1000) . '/' . ARTICLE_ID;
    }

    public function paginate()
    {
        $perPage = Request::get('per_page', 10);
        $page = Request::get('page', 1);

        $chapter_list = $this->Storage->exists($this->storage_id . '/chapters') ? json_decode($this->Storage->get($this->storage_id . '/chapters'),true) : [];
        $total = count($chapter_list);

        $chapter_list = array_chunk($chapter_list, $perPage);
        $movies = static::hydrate($chapter_list[$page-1]??[]);

        $paginator = new LengthAwarePaginator($movies, $total, $perPage);

        $paginator->setPath(url()->current());

        return $paginator;
    }

    // 获取单项数据展示在form中
    public function findOrFail($id)
    {
        $chapter_list = $this->Storage->exists($this->storage_id . '/chapters') ? json_decode($this->Storage->get($this->storage_id . '/chapters'),true) : [];

        $data = $this->Storage->exists($this->storage_id . '/' .$id) ? json_decode($this->Storage->get($this->storage_id . '/' . $id),true) : [];
        $data['title'] = $chapter_list[$id]['title'] ?? '';
        $data['link'] = $chapter_list[$id]['link'] ?? '';
        $data['id'] = $id;

        return static::newFromBuilder($data);
    }

    // 保存提交的form数据
    public function save(array $options = [])
    {
        $attributes = $this->getAttributes();

        //更新保存
        if ($this->exists) {
            $chapter['title'] = $attributes['title'];
            $chapter['content'] = $attributes['content'];
            $this->Storage->put($this->storage_id . '/' . $this->id, json_encode($chapter));

            $chapters_list = $this->Storage->exists($this->storage_id . '/chapters') ? json_decode($this->Storage->get($this->storage_id . '/chapters'), true) : [];

            $chapters_list[$this->id]['title'] = $attributes['title'];
            $chapters_list[$this->id]['link'] = $attributes['link'];
            $this->Storage->put($this->storage_id . '/chapters', json_encode($chapters_list));
        }

        //插入数据
        else {
            $title = ['title' => $attributes['title'],'link' => $attributes['link']];
            $chapter_list = $this->Storage->exists($this->storage_id . '/chapters') ? json_decode($this->Storage->get($this->storage_id . '/chapters'), true) : [];

            $chapter_id = end($chapter_list)['id'];
            $chapter_list[$chapter_id]= $title + ['id' => $chapter_id];
            $this->Storage->put($this->storage_id . '/chapters', json_encode($chapter_list));

            $chapter['title'] = $attributes['title'];
            $chapter['content'] = $attributes['content'];
            $this->Storage->put($this->storage_id . '/' . $chapter_id, json_encode($chapter));
        }

        // save $attributes
        return true;
    }

    public function destroyArr($ids)
    {
        $chapter_list = $this->Storage->exists($this->storage_id . '/chapters') ? json_decode($this->Storage->get($this->storage_id . '/chapters'), true) : [];
        foreach(explode(',', $ids) as $id){
            $this->Storage->delete($this->storage_id . '/' . $id);
            if (isset($chapter_list[$id])) unset($chapter_list[$id]);
        }

        //清空目录 or 最后章节获取
        if (empty($chapter_list)) {
            $this->Storage->delete($this->storage_id . '/chapters');
            $last_chapter = [
                'id'    => 0,
                'title' => ''];
        } else {
            $this->Storage->put($this->storage_id . '/chapters', json_encode($chapter_list));
            $last_chapter = end($chapter_list);
        }

        ArticlesModel::query()->where(['id'=>ARTICLE_ID])->update([
            'last_chapter_id' => $last_chapter['id'],
            'last_chapter'    => $last_chapter['title'],
        ]);

        return true;
    }

    public static function with($relations)
    {
        return new static;
    }

    // 覆盖`orderBy`来收集排序的字段和方向
    public function orderBy($column, $direction = 'asc')
    {

    }

    // 覆盖`where`来收集筛选的字段和条件
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {

    }
}

