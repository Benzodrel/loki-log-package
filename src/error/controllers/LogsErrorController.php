<?php


namespace boltSystem\yii2Logs\src\error\controllers;


use boltSystem\yii2Logs\src\error\drivers\ErrorLogDb;
use boltSystem\yii2Logs\src\error\search\ErrorLogDbSearch;
use Yii;

class LogsErrorController extends \boltSystem\yii2Logs\src\base\controllers\BaseController
{
    public $rules = [
        [
            'allow'   => true,
            'actions' => ['page-corrupted'],
        ],
    ];

    public function getModel()
    {
        return ErrorLogDb::class;
    }

    public function getSearchModel()
    {
        return ErrorLogDbSearch::class;
    }

    public function behaviors()
    {
        $rules = Yii::$app->params['controllerRules'] ?? $this->rules;

        return [
            'access' => [
                'class' => \yii\filters\AccessControl::class,
                'rules' => $rules,
            ],
        ];
    }

    public function actionPageCorrupted($id = '0')
    {
        if (strlen($id) < 36){

            $error = ErrorLogDb::findOne(intval($id));

            return $this->render('@vendor/bolt-system/yii2-logs/src/error/views/error', [
                'title' => 'На странице произошла ошибка',
                'description' => 'Попробуйте перейти в другой раздел. Если проблема повторится, свяжитесь с администрацией проекта и скопируйте им нижеследующую информацию об ошибке.',
                'back_url' => $error->url,
                'error' => $error,
                'admin' => false,
            ]);
        }

        return $this->render('@vendor/bolt-system/yii2-logs/src/error/views/error', [
            'title' => 'На странице произошла ошибка',
            'description' => 'Попробуйте перейти в другой раздел. Если проблема повторится, свяжитесь с администрацией проекта и скопируйте им нижеследующую информацию об ошибке.',
            'error_id' => $id,
        ]);
    }

    public function actionIndex($ajax = null)
    {
        $classSearchModel = $this->getSearchModel();
        $searchModel      = new $classSearchModel();

        $searchParams = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($searchParams);

        return $this->render('@vendor/bolt-system/yii2-logs/src/error/views/index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('@vendor/bolt-system/yii2-logs/src/error/views/view', [
            'model' => $this->findModel($id),
        ]);
    }
}