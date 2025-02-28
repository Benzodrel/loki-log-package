<?php


namespace BoltSystem\Yii2Logs\log\action\controllers;


use app\controllers\backend\BaseController;
use app\models\backend\ErrorLogSearch;
use app\models\dictionaries\ErrorLevel;
use app\models\ErrorLog;

class LogController extends \BoltSystem\Yii2Logs\log\base\controllers\BaseController
{
    public $rules = [
        [
            'allow'   => true,
            'actions' => ['page-corrupted'],
        ],
    ];

    public function getModel()
    {
        return LogDb::class;
    }

    public function getSearchModel()
    {
        return LogDbSearch::class;
    }
}