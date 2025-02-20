<?php

namespace BoltSystem\Yii2LokiLog\log\event;

use app\models\EventLog as EventLegacy;
use Yii;
use yii\base\BaseObject;

final class EventLog extends EventLegacy
{
    public const LOG_LOKI = 'loki';
    public const LOG_DB   = 'db';

    public function __construct($config = [])
    {
        BaseObject::__construct($config);
    }

    public function init()
    {
        BaseObject::init();
    }

    public static function getDriverName()
    {
        return Yii::$app->params['log'] ?? static::LOG_DB;
    }

    public static function getDriver()
    {
        $logName = static::getDriverName();

        return match ($logName) {
            static::LOG_LOKI => EventLogLoki::instance(),
            static::LOG_DB   => EventLegacy::instance(),
            default          => throw new \Exception("[EventLogLoader] Driver name `{$logName}` not recognized")
        };
    }

    public static function Add($type, $to_list, $data = [], $settings = [], $info = [])
    {
        return static::getDriver()::Add($type, $to_list, $data, $settings, $info);
    }
}
