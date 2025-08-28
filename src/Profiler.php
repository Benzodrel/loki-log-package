<?php


namespace boltSystem\yii2Logs\src;

use Yii;
use yii\base\BaseObject;
use yii\log\Logger;


class Profiler extends BaseObject
{
    private static $category = [];

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function init()
    {
        parent::init();
    }

    public static function beginProfile($category){
        if(array_key_exists($category, static::$category)){
            throw new \Exception('Profile category name is already in use');
        }

        $timeBegin = microtime();
        $memoryBegin = memory_get_usage();

        $categoryData = [
            'time'         => $timeBegin,
            'memory_usage' => $memoryBegin
        ];
        static::$category[$category] = $categoryData;
    }

    public static function endProfile($category){
        $timeEnd = microtime();
        $memoryEnd = memory_get_usage();

        if (array_key_exists($category, static::$category)){
            $start = static::$category[$category];

            $message = [
                'time'               => $timeEnd - $start['time'],
                'memory_usage_start' => $start['memory_usage'],
                'memory_usage_end'   => $memoryEnd,
                'time_start'         => $start['time'],
                'time_end'           => $timeEnd
            ];

            Yii::getLogger()->log($message,Logger::LEVEL_PROFILE_END, $category);

            unset(static::$category[$category]);
        } else {
            throw new \Exception('Cannot find profiling begin for ' . $category);
        }
    }
}