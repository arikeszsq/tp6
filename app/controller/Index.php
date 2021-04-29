<?php

namespace app\controller;

use app\BaseController;
use think\api\Client;
use think\facade\View;
use think\facade\Db;

class Index extends BaseController
{
    private $app_code = 'deb6841e39cfc9175e98114d0f6b4a08';
    public $double_ball = [
        'lottery_id' => "ssq",
        'lottery_name' => '双色球',
        'remarks' => '每周二、四、日开奖'
    ];
    public $big_happy = [
        'lottery_id' => "dlt",
        'lottery_name' => '超级大乐透',
        'remarks' => '每周一、三、六开奖'
    ];

    public function index()
    {
        $has_run_ssq = Db::table("lottery_run")->where('type',1)->where('add_date',date('Y-m-d',time()))->find();
        if(!$has_run_ssq){
            $this->addSSQ();
        }
        $has_run_dlt = Db::table("lottery_run")->where('type',2)->where('add_date',date('Y-m-d',time()))->find();
        if(!$has_run_dlt){
            $this->addDLT();
        }
        return View::fetch('index');
    }

    public function addSSQ()
    {
        $client = new Client($this->app_code);
        $result = $client->lotteryQuery()->withLotteryId('ssq')->request();
        $lottery_data = $result['data'];
        $lottery_res = $lottery_data['lottery_res'];
        $lottery_res_array = explode(',', $lottery_res);
        $blue = '';
        $red = [];
        foreach ($lottery_res_array as $key => $val) {
            if ($key == 6) {
                $blue = $val;
            } else {
                $red[] = $val;
            }
        }
        $red = join(',', $red);
        $data = [
            'name' => '双色球',
            'red_1' => $lottery_res_array[0],
            'red_2' => $lottery_res_array[1],
            'red_3' => $lottery_res_array[2],
            'red_4' => $lottery_res_array[3],
            'red_5' => $lottery_res_array[4],
            'red_6' => $lottery_res_array[5],
            'res_red' => $red,
            'res_blue' => $blue,
            'no' => $lottery_data['lottery_no'],
            'date' => $lottery_data['lottery_date'],
            'ex_date' => $lottery_data['lottery_exdate'],
            'create_time' => date('Y-m-d H:i:s', time())
        ];
        $ret = Db::table("lottery_ssq")->insert($data);
        if($ret){
            Db::table("lottery_run")->insert([
                'type'=>1,
                'add_date'=>date("Y-m-d"),
                'create_time' => date('Y-m-d H:i:s', time())
            ]);
        }
    }

    public function addDLT()
    {
        $client = new Client($this->app_code);
        $result = $client->lotteryQuery()->withLotteryId('dlt')->request();
        $lottery_data = $result['data'];
        $lottery_res = $lottery_data['lottery_res'];
        $lottery_res_array = explode(',', $lottery_res);
        $blue = [];
        $red = [];
        foreach ($lottery_res_array as $key => $val) {
            if ($key == 5 || $key == 6) {
                $blue[] = $val;
            } else {
                $red[] = $val;
            }
        }
        $red = join(',', $red);
        $blue = join(',', $blue);

        $data = [
            'name' => '超级大乐透',
            'ex_1' => $lottery_res_array[0],
            'ex_2' => $lottery_res_array[1],
            'ex_3' => $lottery_res_array[2],
            'ex_4' => $lottery_res_array[3],
            'ex_5' => $lottery_res_array[4],
            'af_1' => $lottery_res_array[5],
            'af_2' => $lottery_res_array[6],
            'res_red' => $red,
            'res_blue' => $blue,
            'no' => $lottery_data['lottery_no'],
            'date' => $lottery_data['lottery_date'],
            'ex_date' => $lottery_data['lottery_exdate'],
            'create_time' => date('Y-m-d H:i:s', time())
        ];
        $ret = Db::table("lottery_dlt")->insert($data);
        if($ret){
            Db::table("lottery_run")->insert([
                'type'=>2,
                'add_date'=>date("Y-m-d"),
                'create_time' => date('Y-m-d H:i:s', time())
            ]);
        }
    }
}
