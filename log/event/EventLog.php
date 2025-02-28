<?php

namespace BoltSystem\Yii2Logs\log\event;

use app\models\EventLog as EventLegacy;
use BoltSystem\Yii2Logs\log\event\drivers\EventLogDb;
use BoltSystem\Yii2Logs\log\event\drivers\EventLogLoki;
use Yii;
use yii\base\BaseObject;

final class EventLog extends BaseObject
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

    public static function getDriverName()
    {
        return Yii::$app->params['log'] ?? static::LOG_DB;
    }

    public static function getDriver()
    {
        $logName = static::getDriverName();

        return match ($logName) {
            static::LOG_LOKI => EventLogLoki::instance(),
            static::LOG_DB   => EventLogDb::instance(),
            default          => throw new \Exception("[EventLogLoader] Driver name `{$logName}` not recognized")
        };
    }

    public static function Add($type, $to_list, $data = [], $settings = [], $info = [])
    {
        return static::getDriver()::Add($type, $to_list, $data, $settings, $info);
    }
}
