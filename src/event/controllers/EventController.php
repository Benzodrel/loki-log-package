<?php


namespace boltSystem\yii2Logs\src\event\controllers;


use app\controllers\backend\BaseController;
use boltSystem\yii2Logs\src\event\drivers\EventLogDb;
use boltSystem\yii2Logs\src\event\search\EventLogDbSearch;
use Yii;

class EventController extends \boltSystem\yii2Logs\src\base\controllers\BaseController
{
    public $rules = [
        [
            'allow'   => true,
            'actions' => ['page-corrupted'],
        ],
        [
            'allow' => true,
            'roles' => ['admin', 'system'],
        ],
    ];

    public function getModel()
    {
        return EventLogDb::class;
    }

    public function getSearchModel()
    {
        return EventLogDbSearch::class;
    }

    public function actionIndex($ajax = null)
    {
        $classSearchModel = $this->getSearchModel();
        $searchModel      = new $classSearchModel();

        $searchParams = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($searchParams);

        return $this->render('@vendor/bolt-system/yii2-logs/src/event/views/index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('@vendor/bolt-system/yii2-logs/src/event/views/view', [
            'model' => $this->findModel($id),
        ]);
    }
}