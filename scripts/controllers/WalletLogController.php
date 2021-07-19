<?php
namespace scripts\controllers;

use models\AccountsModel;
use vendor\base\Controller;

class WalletLogController extends Controller
{
    public function actionRefresh()
    {
        $model = new AccountsModel();
        $res = $model->get_last_hours_data(3);
        return $this->response($res, $model, 201);
    }
}