<?php

namespace boltSystem\yii2Logs\src\helpers;

use boltSystem\yii2Logs\src\action\Log;
use boltSystem\yii2Logs\src\error\ErrorLog;
use boltSystem\yii2Logs\src\error\handlers\ErrorHandler;
use boltSystem\yii2Logs\src\event\EventLog;
use boltSystem\yii2Logs\src\Profiler;
use boltSystem\yii2Logs\src\target\CustomLokiLogTarget;
use Yii;
use yii\web\Request;

class LogConfiguratorBuilder
{
    private const LOKI = 'loki';
    private const DB = 'db';

    private const ERROR_LOG_COMPONENT = 'errorLog';
    private const ACTION_LOG_COMPONENT = 'Log';
    private const EVENT_LOG_COMPONENT = 'eventLog';
    private const PROFILER_COMPONENT = 'profiler';
    public $config;
    public $logType;

    private array $availableDrivers = [self::LOKI, self::DB];

    public array $lokiConfig = [];

    public function __construct(string $logType, array $config)
    {
        $this->config = $config;
        $this->logType = $logType;

        if(!in_array($logType, $this->availableDrivers)){
            throw new \Exception($logType . ' is not a valid log type, must be one of:' . implode(',', $this->availableDrivers));
        }

        switch ($logType) {
            case self::LOKI:
                $this->lokiConfig = $this->lokiDefaultConfig();
                $this->config['bootstrap'][] = 'log';
                $this->config['components']['log'] = $this->lokiConfig;
                $this->config['params']['error_log_driver'] = self::LOKI;
                $this->config['params']['action_log_driver'] = self::LOKI;
                $this->config['params']['event_log_driver'] = self::LOKI;
                break;

            case self::DB:
                $this->config['params']['error_log_driver'] = self::DB;
                $this->config['params']['action_log_driver'] = self::DB;
                $this->config['params']['event_log_driver'] = self::DB;
                break;
        }
    }

    private function lokiDefaultConfig(): array
    {
        return [
            'traceLevel' => 3,
            'flushInterval' => 100,
            'targets' => [
                [
                    'class' => CustomLokiLogTarget::class,
                    'enabled' => true,
                    'prefix' => function ($message) {

                        $code = $message[0]['code'] ?? '-';
                        $id = $message[0]['id'] ?? '-';

                        $request = Yii::$app->getRequest();
                        $ip = $request instanceof Request ? $request->getUserIP() : '-';

                        $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
                        if ($user && ($identity = $user->getIdentity(false))) {
                            $userID = $identity->getId();
                        } else {
                            $userID = '-';
                        }

                        return "[$ip][$userID][$id][$code]";
                    },
                    'lokiPushUrl' => "http://loki:3100/loki/api/v1/push",
                    'lokiAuthUser' => "loki", // HTTP Basic Auth User
                    'lokiAuthPassword' => "...", // HTTP Basic Auth Password

                    'levels' => ['error', 'warning', 'info', 'profile'],
                    'logVars' => ['_GET', '_POST', '_SERVER'],
                    //                'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'],
                    // optionally exclude categories
                    'except' => [
                        'yii\base\Application::bootstrap',
                        'yii\web\UrlManager::parseRequest',
                        'yii\web\Session::open',
                        'yii\db\Command::query',
                        'yii\db\Connection::open',
                        'yii\db\Command::execute',
                        'yii\httpclient\StreamTransport::send',
                    ],

                    // optionally re-map log level for certain categories
                    'levelMap' => [
                        // yii category
                        'yii\web\HttpException:404' => [
                            // yii level => loki level
                            // set loki level to false, to drop messages with that category
                            '*' => 'info',
                        ],
                        'yii\web\HttpException:401' => [
                            // yii level => loki level
                            // set loki level to false, to drop messages with that category
                            '*' => 'warning',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function changeLokiConfig(array $configToChange): LogConfiguratorBuilder
    {
        $this->lokiConfig = array_merge($this->lokiConfig, $configToChange);
        return $this;
    }

    public function setLogsParams(array $logsParams)
    {
        $this->config['params'] = array_merge($this->config['params'], $logsParams);
        return $this;
    }

    public function setLokiUser(string $user): LogConfiguratorBuilder
    {
        $this->lokiConfig['targets'][0]['lokiAuthUser'] = $user;
        return $this;
    }

    public function setLokiPassword(string $password): LogConfiguratorBuilder
    {
        $this->lokiConfig['targets'][0]['lokiAuthPassword'] = $password;
        return $this;
    }

    public function setLokiPushUrl(string $url): LogConfiguratorBuilder
    {
        $this->lokiConfig['targets'][0]['lokiPushUrl'] = $url;
        return $this;
    }

    public function build(): array
    {
        if ($this->logType == self::LOKI) {
            $this->config['bootstrap'][] = 'log';
            $this->config['components']['log'] = $this->lokiConfig;
        }

        return $this->config;
    }

    public function addErrorLog
    (
        string $componentName = self::ERROR_LOG_COMPONENT,
        array  $errorLogParams = ['class' => ErrorLog::class],
        array  $errorHandlerParams = ['class' => ErrorHandler::class],
    ): LogConfiguratorBuilder
    {
        $this->config['bootstrap'][] = $componentName;
        $this->config['components'][$componentName] = $errorLogParams;
        $this->config['components']['errorHandler'] = $errorHandlerParams;
        return $this;
    }

    public function addActionLog(string $componentName = self::ACTION_LOG_COMPONENT, array $params = ['class' => Log::class]): LogConfiguratorBuilder
    {
        $this->config['bootstrap'][] = $componentName;
        $this->config['components'][$componentName] = $params;
        return $this;
    }

    public function setActionLogDriver(string $driver): LogConfiguratorBuilder
    {
        if(in_array($driver, $this->availableDrivers)){
            $this->config['params']['action_log_driver'] = $driver;
            return $this;
        }
       throw new \Exception($driver . ' is not a valid driver value, must be one of:' . implode(',', $this->availableDrivers));
    }

    public function setErrorLogDriver(string $driver): LogConfiguratorBuilder
    {
        if(in_array($driver, $this->availableDrivers)){
            $this->config['params']['error_log_driver'] = $driver;
            return $this;
        }
        throw new \Exception($driver . ' is not a valid driver value, must be one of:' . implode(',', $this->availableDrivers));
    }

    public function setEventLogDriver(string $driver): LogConfiguratorBuilder
    {
        if(in_array($driver, $this->availableDrivers)){
            $this->config['params']['event_log_driver'] = $driver;
            return $this;
        }
        throw new \Exception($driver . ' is not a valid driver value, must be one of:' . implode(',', $this->availableDrivers));
    }

    public function addEventLog
    (
        string $componentName = self::EVENT_LOG_COMPONENT,
        array $params = ['class' => EventLog::class]
    ): LogConfiguratorBuilder
    {
        $this->config['bootstrap'][] = $componentName;
        $this->config['components'][$componentName] = $params;
        return $this;
    }

    public function addProfiler
    (
        string $componentName = self::PROFILER_COMPONENT,
        array $params = ['class' => Profiler::class]
    ): LogConfiguratorBuilder
    {
        $this->config['bootstrap'][] = $componentName;
        $this->config['components'][$componentName] = $params;
        return $this;
    }
}