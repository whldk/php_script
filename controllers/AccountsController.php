<?php namespace controllers;

use models\AccountsModel;
use models\LoginModel;
use vendor\base\Controller;

class AccountsController extends Controller
{

    public function actionTest()
    {
        return $this->response('hello',null,201);
    }

    public function actionIndex()
    {
        $model = new AccountsModel();
        $res = $model->get_last_hours_data(3);
        return $this->response($res, $model, 201);
    }

    public function actionData24h()
    {
        $model = new AccountsModel();
        $res = $model->get_24h_data($this->params['coin'], $this->params['wallet']);
        return $this->response($res, $model, 201);
    }

    public function actionLoginCount()
    {
        $model = new LoginModel();
        $res = $model->get_login_data(60);
        return $this->response($res, null, 201);
    }
}