<?php

namespace BoltSystem\Yii2Logs\log\action;

use BoltSystem\Yii2Logs\log\action\drivers\LogDb;
use BoltSystem\Yii2Logs\log\action\drivers\LogLoki;
use Yii;
use yii\base\BaseObject;

final class Log extends BaseObject
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

    public static function getDriverName(): string
    {
        return Yii::$app->params['log'] ?? static::LOG_DB;
    }

    public static function getDriver()
    {
        $logName = static::getDriverName();

        return match ($logName) {
            static::LOG_LOKI => LogLoki::instance(),
            static::LOG_DB   => LogDb::instance(),
            default          => throw new \Exception("[LogLoader] Driver name `{$logName}` not recognized")
        };
    }

    public static function createByClassName($className, $user_id, $action_id, $entity_id, $field)
    {
        return static::getDriver()::createByClassName($className, $user_id, $action_id, $entity_id, $field);
    }

    public static function create($type_id, $user_id, $action_id, $entity_id, $field)
    {
        return static::getDriver()::create($type_id, $user_id, $action_id, $entity_id, $field);
    }
}
