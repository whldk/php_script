<?php namespace models;

use vendor\base\Helpers;
use vendor\base\ValidateModel;

class LoginModel extends ValidateModel
{
    //来源数据表
    const TABLE_1 = 'logininfo';
    const TABLE_2 = 'largelogininfo';

    //写入数据表
    const TABLE_3 = 'logincount';
    const TABLE_4 = 'largelogincount';

    protected static $sets = [
        'id' => ['md5']
    ];

    /**
     * 小户用户统计、每个一个小时统计区间
     * @param int $second
     * @return mixed
     * @throws \vendor\exceptions\DbErrorException
     * @throws \vendor\exceptions\RollbackException
     * @throws \vendor\exceptions\UnknownException
     * @throws \vendor\exceptions\UserErrorException
     */
    public function get_login_data($time, $curr_time)
    {
        $start = microtime(true);
        $db = static::getDb();
        //获取过去60秒的登录信息
        $result = $db->select('`model`, `wallet`, count(`wallet`) as count')->from(self::TABLE_1)
            ->where(['>', 'uptime', $time])
            ->groupby('wallet')
            ->result();

        if ($result) {

            $data = [];
            //初始化md5的数据
            foreach ($result as $item) {
                $md5 = md5($curr_time . $item['model']. $item['wallet']);
                $data[] = [
                        $md5, $item['model'], $item['wallet'], $item['count'], $curr_time
                    ];
            }
            //分块操作 、减少时间
            $quick = $this->callInTransaction(function () use ($data, $db) {
                $inserts = array_chunk($data, 500, true);
                foreach ($inserts as $insert) {
                    //批量查询、减少时间
                    $md5s =  array_column($insert, 0);
                    $res = $db->select(['md5','count'])->from(self::TABLE_3)->where(['md5' => $md5s])->result();
                    $md5_count = Helpers::array_index($res, 'md5');

                    //批量删除、减少时间最差的准备
                    $del = $db->delete(self::TABLE_3)->where(['md5' => $md5s])->result();

                    if ($del === null) {
                        self::throwDbException();
                    }

                    foreach ($insert as &$item) {
                        if (isset($md5_count[$item[0]])) {
                            $item[3] += $md5_count[$item[0]]['count'];
                        }
                    }

                    //批量插入、减少时间
                    $ins = $this->insert_log($insert, self::TABLE_3);

                    if ($ins === null) {
                        self::throwDbException();
                    }
                }

                return true;
            });

        }
        $end = microtime(true);
        return $end - $start;
    }

    /**
     * 大户用户统计、每个一个小时统计区间
     */
    public function get_large_login_data($time, $curr_time)
    {
        $start = microtime(true);
        $db = static::getDb();
        //获取过去60秒的登录信息
        $result = $db->select('`model`, `wallet`, count(`wallet`) as count')->from(self::TABLE_2)
            ->where(['>', 'uptime', $time])
            ->groupby('wallet')
            ->result();

        if ($result) {
            $data = [];
            //初始化md5的数据
            foreach ($result as $item) {
                $md5 = md5($curr_time . $item['model']. $item['wallet']);
                $data[] = [
                    $md5, $item['model'], $item['wallet'], $item['count'], $curr_time
                ];
            }
            //分块操作 、减少时间
            $quick = $this->callInTransaction(function () use ($data, $db) {
                $inserts = array_chunk($data, 500, true);
                foreach ($inserts as $insert) {
                    //批量查询、减少时间
                    $md5s =  array_column($insert, 0);
                    $res = $db->select(['md5','count'])->from(self::TABLE_4)->where(['md5' => $md5s])->result();
                    $md5_count = Helpers::array_index($res, 'md5');

                    //批量删除、减少时间
                    $del = $db->delete(self::TABLE_4)->where(['md5' => $md5s])->result();

                    if ($del === null) {
                        self::throwDbException();
                    }

                    foreach ($insert as &$item) {
                        if (isset($md5_count[$item[0]])) {
                            $item[3] += $md5_count[$item[0]]['count'];
                        }
                    }
                    //批量插入、减少时间
                    $ins = $this->insert_log($insert, self::TABLE_4);

                    if ($ins === null) {
                        self::throwDbException();
                    }
                }
                return true;
            });
        }
        $end = microtime(true);
        return $end - $start;
    }

    /**
     * 写入 count 表
     * @param $workers
     * @return mixed
     */
    public function insert_log($logs, $table)
    {
        $res = static::getDb()->insert(
            $logs,
            ['md5', 'model', 'wallet', 'count', 'uptime'])
            ->table($table)
            ->result();
        return $res;
    }
}