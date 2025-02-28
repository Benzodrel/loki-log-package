<?php


namespace BoltSystem\Yii2Logs\log\event\controllers;


use app\controllers\backend\BaseController;
use app\models\backend\ErrorLogSearch;
use app\models\dictionaries\ErrorLevel;
use app\models\ErrorLog;

class EventController  extends \BoltSystem\Yii2Logs\log\base\controllers\BaseController
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
        return ErrorDb::class;
    }

    public function getSearchModel()
    {
        return ErrorDbSearch::class;
    }

    public function actionPageCorrupted($id = '0')
    {
        try {
            if (\Yii::$app->user->can('admin')) {
                return $this->render('/backend/error', [
                    'title'       => 'На странице произошла ошибка',
                    'description' => 'Информация об ошибке:',
                    'back_url'    => '$error->url',
                    'error'       => '$error',
                    'admin'       => true,
                    'error_info'  => 'ErrorLevel::getMapList()[$error->level]',
                ]);
            } else {
                return $this->render('/backend/error', [
                    'title'       => 'На странице произошла ошибка',
                    'description' => 'Попробуйте перейти в другой раздел. Если проблема повторится, свяжитесь с администрацией проекта и скопируйте им нижеследующую информацию об ошибке.',
                    'back_url'    => '$error->url',
                    'error'       => '$error',
                    'admin'       => false,
                    'error_info'  => 'ErrorLevel::getMapList()[$error->level]',
                ]);
            }
        } catch(\Exception $e) {
            var_dump($e);
        }
    }
}