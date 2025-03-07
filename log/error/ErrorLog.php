<?php

namespace BoltSystem\Yii2Logs\log\error;


use BoltSystem\Yii2Logs\log\error\drivers\ErrorLogDb;
use BoltSystem\Yii2Logs\log\error\drivers\ErrorLogLoki;
use Yii;
use yii\base\BaseObject;

final class ErrorLog extends BaseObject
{
    public const LOG_LOKI = 'loki';
    public const LOG_DB   = 'db';

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
    }

    public function bootstrap($app)
    {
        $app->controllerMap['logs-error'] = [
            'class' => \BoltSystem\Yii2Logs\log\error\controllers\LogsErrorController::class,
        ];
    }

    public static function getDriverName()
    {
        return Yii::$app->params['log'] ?? static::LOG_DB;
    }

    public static function getDriver()
    {
        $logName = static::getDriverName();

        return match ($logName) {
            static::LOG_LOKI => ErrorLogLoki::instance(),
            static::LOG_DB   => ErrorLogDb::instance(),
            default          => throw new \Exception("[ErrorLogLoader] Driver name `{$logName}` not recognized") //TODO: exception instead
        };
    }

    public static function RegisterError($code = '0', $description = '', $level = ErrorLogDb::LEVEL_NOTICE, $meta = [])
    {
        return static::getDriver()::RegisterError($code, $description, $level, $meta);
    }

    public static function RegisterErrorByErrorException($exception, $definedLevel = false)
    {
        return static::getDriver()::RegisterErrorByErrorException($exception, $definedLevel);
    }

    public static function RegisterErrorByPHPError($error_last = null)
    {
        return static::getDriver()::RegisterErrorByPHPError($error_last);
    }

    public static function mapPHPLevel($level)
    {
        return static::getDriver()::mapPHPLevel($level);
    }

    public static function GetProtectedProperty($obj, $prop)
    {
        return static::getDriver()::GetProtectedProperty($obj, $prop);
    }
}
