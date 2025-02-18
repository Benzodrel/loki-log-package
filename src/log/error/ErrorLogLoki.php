<?php

namespace BoltSystem\Yii2LokiLog\log\error;

use Yii;
use yii\helpers\ArrayHelper;

class ErrorLogLoki extends \app\models\ErrorLog
{
    protected static $instance;

    protected static function generateId() {

        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        $id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        return $id;
    }

    public static function RegisterError($code = '0', $description = '', $level = 'notice', $meta = [])
    {
        $url = 'unknown';

        if (key_exists('url', $meta)) {
            $url = $meta['url'];
        } else {
            if ((php_sapi_name() === 'cli')) {
                $url = property_exists(Yii::$app->request, 'url') ? Yii::$app->request->url : 'console application ' . implode(' ', $_SERVER['argv']);
            } else {
                if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
                    $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                }
            }
        }
        $errId = self::generateId();
        $err   = [
            'id'          => $errId,
            'level'       => strval($level),
            'code'        => strval($code),
            'description' => strval($description),
            'user_id'     => static::getUserID(),
            'url'         => $url,
            'file'        => isset($meta['file']) ?? $meta['file'],
            'line'        => isset($meta['line']) ?? $meta['line'],
            'log_type'    => 'error',
        ];
        if (self::mapPHPLevel($level) == static::LEVEL_NOTICE || self::mapPHPLevel($level) == static::LEVEL_WARNING){
            Yii::warning($err, $level);
        } else {
            Yii::error($err, $level);
        }
        return $errId;
    }

    public static function RegisterErrorByErrorException($exception, $definedLevel = false)
    {
        $code = self::GetProtectedProperty($exception, 'statusCode');
        if ($code == null) {
            $code = self::GetProtectedProperty($exception, 'code') ?: '-1';
        }

        $message = self::GetProtectedProperty($exception, 'message');
        if ($message == null) {
            $message = 'An internal server error occurred.';
        }

        $excArray = self::ConvertExceptionToArray($exception);

        $level = $definedLevel ?: self::mapPHPLevel($excArray['type']);

        $url = 'unknown';

        $isConsole = Yii::$app->request->isConsoleRequest;

        if (($isConsole)) {
            $url = 'console application ' . implode(' ', $_SERVER['argv']);
        } else {
            if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
                $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }
        }

        $errId     = self::generateId();
        $errorLine = [
            'id'           => $errId,
            'level'        => $level,
            'code'         => strval($code),
            'message'      => strval($message),
            'userId'       => static::getUserID(),
            'url'          => $url,
            'file'         => self::GetProtectedProperty($exception, 'file') ?: '',
            'line'         => self::GetProtectedProperty($exception, 'line') ?: '',
            'trace'        => json_encode($excArray['stack-trace'] ?? null) ?: '',
            'request_data' => $isConsole ? ArrayHelper::toArray(Yii::$app->request, [
                \yii\web\Request::class => [
                    'params',
                ],
            ]) : ArrayHelper::toArray(Yii::$app->request, [
                \yii\web\Request::class => [
                    'hostName',
                    'url',
                    'isSecureConnection',
                    'referrer',
                    'origin',
                    'userAgent',
                    'userIP',
                    'method',
                    'headers',
                    'cookies',
                    'isAjax',
                    'rawBody',
                    'bodyParams',
                    'queryParams',
                    'queryString',
                ],
            ]),
            'log_type'    => 'error',
            ];

        if (self::mapPHPLevel($level) == static::LEVEL_NOTICE || self::mapPHPLevel($level) == static::LEVEL_WARNING){
            Yii::warning($errorLine, $level);
        } else {
            Yii::error($errorLine, $level);
        }
        return $errId;
    }

    public static function RegisterErrorByPHPError($error_last = null)
    {
        if (!is_null($error_last)) {
            $code    = $error_last['type'];
            $message = $error_last['message'];
            $level   = ErrorLogLoki::mapPHPLevel($error_last['type']);
            $meta    = [
                'file' => $error_last['file'],
                'line' => $error_last['line'],
            ];

            ErrorLogLoki::RegisterError($code, $message, $level, $meta);
        }

        return 0;
    }
}
