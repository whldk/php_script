<?php namespace models;

use vendor\base\Helpers;
use vendor\base\ValidateModel;

set_time_limit(0);

class AccountsModel extends ValidateModel
{
    private $url = 'http://218.244.144.69/v1/getMinerWithWorker';

    #private $url = 'http://172.16.4.221/v1/getMinerWithWorker';

    const NAME = 'accounts';

    const TABLE = 'walletlog';

    protected static $sets = [
        'id' => ['wallet', 'coin']
    ];

    protected static $fields = [
        'wallet' => null,
        'coin' => null
    ];

    public function get_last_hours_data($hours = 3)
    {
        $start = microtime(true);
        $db = static::getCore_db();
        $time = time() - 3600 * $hours;
        $result = $db->select(self::listFields())->from(self::NAME)
            ->where(['>', 'updated_at' , $time])
            ->orderby('updated_at', 'asc')
            ->result();
        $wallets = array_chunk($result, 1000, true);
        foreach ($wallets as $wallet) {
            $workers = [];
            foreach ($wallet as $item) {
                $worker = $this->get_miner_with_worker($item['coin'], $item['wallet']);
                if ($worker) {
                    $workers[] = $worker;
                }
            }
            //写入数据库中
            $this->insert_wallet_log($workers);
        }
        $end = microtime(true);
        return $end - $start;
    }

    public function get_miner_with_worker($coin, $wallet)
    {
        $url = $this->url . '?coin=' . $coin .'&wallet=' . $wallet;
        $worker = Helpers::HttpCurl($url, [], 'GET');
        sleep(0.1);
        if (isset($worker['data']) &&  isset($worker['data']['miner'])) {
            $miner = $worker['data']['miner'];
            $up_data = $this->get_24h_data($coin, $wallet);
            return [$coin, $wallet, $miner['difff24'], $miner['share24'], $miner['delay24'], $miner['reject24'], $up_data['up_diff'], $up_data['up_share'], time()];
        }
    }

    /**
     * 写入wallet_log表
     * @param $workers
     * @return mixed
     */
    public function insert_wallet_log($workers)
    {
        $res = static::getDb()->insert(
            $workers,
            ['coin', 'wallet', 'diff', 'share', 'delay', 'reject', 'up_diff',  'up_share', 'uptime'])
            ->table(self::TABLE)
            ->result();
        return $res;
    }

    /**
     * 获取第一次的记录
     * @param $coin
     * @param $wallet
     * @return array
     */
    public function get_24h_data($coin, $wallet)
    {
        //减少几分钟的延迟
        $time = strtotime(date('Y-m-d'));
        $db = static::getDb();
        $result = $db->select(['diff', 'share'])->from(self::TABLE)->where(['coin' => $coin, 'wallet' => $wallet, ['>', 'uptime', $time]])
            ->orderby('uptime', 'asc')
            ->limit(1)
            ->result();
       if (is_array($result) && count($result) > 0) {
            return ['up_diff' => $result[0]['diff'], 'up_share' => $result[0]['share']];
        }
       //代表刚开始挖、刚接入
        return ['up_diff' => 0, 'up_share' => 0];
    }

}