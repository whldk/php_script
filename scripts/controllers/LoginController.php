<?php
namespace scripts\controllers;

use models\LoginModel;
use vendor\base\Controller;

class LoginController extends Controller
{
    public function actionRefresh()
    {
        $pass_time = time() - 60;
        $curr_hours = date('Y-m-d H').":00:00";
        $curr_time = strtotime($curr_hours);
        $model = new LoginModel();
        $time1 = $model->get_large_login_data($pass_time, $curr_time);
        $time2 = $model->get_login_data($pass_time, $curr_time);
        $res = $time1 + $time2;
        return $this->response($res, $model, 201);
    }
}