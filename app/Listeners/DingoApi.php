<?php

namespace App\Listeners;

use Dingo\Api\Event\ResponseWasMorphed;
use Illuminate\Contracts\Pagination\Paginator;

class DingoApi
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(ResponseWasMorphed $event)
    {
        //公共头部
        $origin = \request()->header('ORIGIN', '*');
        $event->response->headers->set('Access-Control-Allow-Origin', $origin);
        $event->response->headers->set('Access-Control-Allow-Credentials', 'true');
        $event->response->headers->set('Access-Control-Allow-Methods', 'POST, GET, PUT, DELETE');
        $event->response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        $event->response->headers->set('Connection', 'keep-alive');

        //数据清洗
        if (isset($event->content['data'])) {

            //分页对象处理
            if ($event->content['data'] instanceof Paginator) {
                $paginate = $event->content['data'];
                $event->response->headers->set('link', sprintf('<%s>; rel="first",<%s>; rel="last",<%s>; rel="next", <%s>; rel="prev"', $paginate->url(1), method_exists($paginate, 'lastPage') ? $paginate->url($paginate->lastPage()) : '', $paginate->nextPageUrl(), $paginate->previousPageUrl()));

                //数据集
                $result['data'] = $paginate->toArray()['data'];
                //单页的数据条数
                $result['per_page'] = $paginate->perPage();
                //最后页、最大页数
                $result['last_page'] = method_exists($paginate, 'lastPage') ? $paginate->lastPage() : 0;
                //当前页页码
                $result['current_page'] = $paginate->currentPage();
                //当前页数据条数
                $result['count'] = $paginate->count();
                //总数据条数
                $result['total'] = method_exists($paginate, 'total') ? $paginate->total() : 0;

                $event->content['data'] = $result;
            }

            $event->content['data'] = self::formatResponseData($event->content['data']);
        }
    }

    /**
     * @param $data
     * @return mixed
     * 字段验证器
     * 接口数据清洗，强制检测与转换
     */
    protected static function formatResponseData($data)
    {
        foreach ($data as $key => &$item) {
            if (is_null($item)) {
                $item = '';
                continue;
            }

            if (is_array($item) or is_object($item)) {
                method_exists($item, 'toArray') and $item = $item->toArray();
                $item = self::formatResponseData($item);
                continue;
            }

            if (is_string($item)) {
                if (in_array(substr($item, -4), ['.png', '.jpg', 'jpeg'])) {
                    //忽略部分字段
                    if ($key === 'file_path') continue;
                    $item = self::formatImagesUrl($item);
                    continue;
                }
            }
        }

        return $data;
    }

    /**
     * 转化数据库保存的文件路径，为可以访问的url
     * @param string $file
     * @param mixed $style 图片样式,支持各大云存储
     * @return string
     */
    protected static function formatImagesUrl(string $file_path = '')
    {
        if (strpos($file_path, "http") === 0) {
            return $file_path;
        }
        return url(\Illuminate\Support\Facades\Storage::url($file_path));
    }

}
