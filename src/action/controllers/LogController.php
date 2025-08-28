<?php


namespace boltSystem\yii2Logs\src\action\controllers;


use app\controllers\backend\BaseController;
use app\models\backend\ErrorLogSearch;
use app\models\dictionaries\ErrorLevel;
use app\models\ErrorLog;
use Yii;

class LogController extends \boltSystem\yii2Logs\src\base\controllers\BaseController
{
    public $rules = [];

    public function getModel()
    {
        return LogDb::class;
    }

    public function getSearchModel()
    {
        return LogDbSearch::class;
    }

    public function actionIndex($ajax = null)
    {
        $classSearchModel = $this->getSearchModel();
        $searchModel      = new $classSearchModel();

        $searchParams = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($searchParams);

        return $this->render('@vendor/bolt-system/yii2-logs/src/action/views/index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('@vendor/bolt-system/yii2-logs/src/action/views/view', [
            'model' => $this->findModel($id),
        ]);
    }
}