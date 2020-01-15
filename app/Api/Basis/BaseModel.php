<?php


namespace App\Api\Basis;

use Illuminate\Support\Facades\DB;

//DB::beginTransaction();

Trait BaseModel
{
    protected static function removeEmpty(array $data = [])
    {
        foreach ($data as $key => $item) {
            if (in_array($key,['title','name'])){
                $data[$key] = trim($item);
            }
            if (is_null($item)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    public static function updateOrInsert(string $table, array $data, array $where = [])
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $sql = $where ?
            DB::table($table)->where($where)->update($data) :
            DB::table($table)->insertGetId($data);
        return ['code' => 0, 'id' => $sql, 'msg' => '数据库成功'];
    }

    public static function transactionOn()
    {
        return DB::beginTransaction();
    }

    public static function transactionSql(string $table,array $data,array $where = [])
    {// 事务操作
        try {
            //$sql_name = substr($sql_name, 0, 1) == '.' ? substr($sql_name, 1) : (config('database.prefix') . $sql_name);
            $sql = self::updateOrInsert($table, $data, $where);
            self::commitSql();
            return $sql;
        } catch (\Exception $e) {
            self::rollbackSql();
            return [
                'code' => 40000,
                'msg' => $e->getMessage(),
                //                'msg'=>'数据库错误',
            ];
        }
    }

    public static function commitSql()
    {
        //提交事务
        DB::commit();
        return true;
    }

    public static function rollbackSql()
    {
        //回滚事务
        DB::rollback();
        return false;
    }
}
