<?php

namespace boltSystem\yii2Logs\src\error;


use boltSystem\yii2Logs\src\error\drivers\ErrorLogDb;
use boltSystem\yii2Logs\src\error\drivers\ErrorLogLoki;
use Yii;
use yii\base\BaseObject;
use yii\base\BootstrapInterface;

final class ErrorLog extends BaseObject implements BootstrapInterface
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
        if($app instanceof \Yii\web\Application)
        {
            $app->controllerMap['logs-error'] = [
                'class' => \boltSystem\yii2Logs\src\error\controllers\LogsErrorController::class,
            ];
            Yii::$app->urlManager->addRules([
                '/backend/logs/error/<action>' => 'logs-error/<action>',
            ], false);
        }
        Yii::setAlias('@error-migrations', '@vendor/bolt-system/yii2-logs/src/error/migrations');
    }

    public static function getDriverName()
    {
        return Yii::$app->params['error_log_driver'] ?? static::LOG_DB;
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
