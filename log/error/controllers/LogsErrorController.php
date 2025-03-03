<?php


namespace BoltSystem\Yii2Logs\log\error\controllers;


use BoltSystem\Yii2Logs\log\error\drivers\ErrorLogDb;
use BoltSystem\Yii2Logs\log\error\search\ErrorLogDbSearch;

class LogsErrorController extends \BoltSystem\Yii2Logs\log\base\controllers\BaseController
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

    public function actionPageCorrupted($id = '0')
    {
        try {
            if (\Yii::$app->user->can('admin')) {
                return $this->render('@vendor/BoltSystem/Yii2Logs/log/error/views/error', [
                    'title'       => 'На странице произошла ошибка',
                    'description' => 'Информация об ошибке:',
                    'back_url'    => '$error->url',
                    'error'       => '$error',
                    'admin'       => true,
                    'error_info'  => 'ErrorLevel::getMapList()[$error->level]',
                ]);
            } else {
                return $this->render('@vendor/BoltSystem/Yii2Logs/log/error/views/error', [
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