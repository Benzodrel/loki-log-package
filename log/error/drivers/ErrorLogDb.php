<?php


namespace BoltSystem\Yii2Logs\log\error\drivers;

use yii\base\BaseObject;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\web\HttpException;

class ErrorLogDb extends \app\models\base\BaseModel
{
    public static function settingForIndex()
    {
        return [
            'export' => false,
            'create' => false,
            'title'  => 'Лог ошибок',
            'url'    => '/backend/logs-error',
            'titles' => [
                'btn-create' => 'Создать',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logs_error}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['date_create'], 'safe'],
            [['description', 'meta', 'url'], 'string'],
            [['level', 'code'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'level'       => 'Уровень',
            'code'        => 'Код',
            'description' => 'Описание',
            'meta'        => 'META',
            'user_id'     => 'User ID',
            'date_create' => 'Дата создания',
            'url'         => 'URL',
        ];
    }

    public function metaFields()
    {
        return [
            'url'          => '',
            'file'         => '',
            'line'         => '',
            'trace'        => '',
            'request_data' => null,
        ];
    }

    public function customFields()
    {
        return [
            'url',
            'file',
            'line',
            'trace',
            'request_data',
        ];
    }

    public function getUrl()
    {
        return $this->getMetaFieldValue('url');
    }

    public function setUrl($value)
    {
        $this->setMetaFieldValue('url', $value);
    }

    public function getFile()
    {
        return $this->getMetaFieldValue('file');
    }

    public function setFile($value)
    {
        $this->setMetaFieldValue('file', $value);
    }

    public function getLine()
    {
        return $this->getMetaFieldValue('line');
    }

    public function setLine($value)
    {
        $this->setMetaFieldValue('line', $value);
    }

    public function getTrace()
    {
        return $this->getMetaFieldValue('trace');
    }

    public function setTrace($value)
    {
        $this->setMetaFieldValue('trace', $value);
    }

    public function getRequest_data()
    {
        return $this->getMetaFieldValue('request_data');
    }

    public function setRequest_data($value)
    {
        $this->setMetaFieldValue('request_data', $value);
    }

    public const LEVEL_NOTICE         = 'notice';
    public const LEVEL_WARNING        = 'warning';
    public const LEVEL_EXCEPTION      = 'exception';
    public const LEVEL_HTTP_EXCEPTION = 'http exception';
    public const LEVEL_FATAL          = 'fatal';

    public static function getLevelsArray()
    {
        return [
            static::LEVEL_NOTICE         => Yii::t('main', 'Информация'),
            static::LEVEL_WARNING        => Yii::t('main', 'Оповещение'),
            static::LEVEL_EXCEPTION      => Yii::t('main', 'Исключение'),
            static::LEVEL_HTTP_EXCEPTION => Yii::t('main', 'HTTP Исключение'),
            static::LEVEL_FATAL          => Yii::t('main', 'Фатальная ошибка'),
        ];
    }

    public static function getErrorLevelTitle(string $level): string
    {
        return static::getLevelsArray()[$level] ?? 'Неизвестно';
    }

    public static function mapPHPLevel($level)
    {
        switch ($level) {
            case E_NOTICE:
                return static::LEVEL_NOTICE;
            case E_USER_NOTICE:
                return static::LEVEL_NOTICE;
            case E_DEPRECATED:
                return static::LEVEL_NOTICE;
            case E_USER_DEPRECATED:
                return static::LEVEL_NOTICE;
            case E_WARNING:
                return static::LEVEL_WARNING;
            case E_CORE_WARNING:
                return static::LEVEL_WARNING;
            case E_COMPILE_WARNING:
                return static::LEVEL_WARNING;
            case E_USER_WARNING:
                return static::LEVEL_WARNING;
            case E_ERROR:
                return static::LEVEL_FATAL;
            case E_PARSE:
                return static::LEVEL_FATAL;
            case E_CORE_ERROR:
                return static::LEVEL_FATAL;
            case E_COMPILE_ERROR:
                return static::LEVEL_FATAL;
            case E_USER_ERROR:
                return static::LEVEL_FATAL;
            case E_RECOVERABLE_ERROR:
                return static::LEVEL_FATAL;

            default:
                return static::LEVEL_EXCEPTION;
        }
    }

    public static function getUserID()
    {
        if ((php_sapi_name() === 'cli')) {
            return 0;
        }

        return Yii::$app->user->id ?: 0;
    }

    public static function RegisterError($code = '0', $description = '', $level = self::LEVEL_NOTICE, $meta = [])
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

        $err = new ErrorLogDb([
            'level'       => strval($level),
            'code'        => strval($code),
            'description' => strval($description),
            'user_id'     => static::getUserID(),
            'url'         => $url,
            'meta'        => [
                'file' => isset($meta['file']) ? $meta['file'] : '',
                'line' => isset($meta['line']) ? $meta['line'] : '',
            ],
        ]);

        if (!$err->save()) {
            $e = $err->getErrors();
            var_dump($e);
            die();
        }

        return $err->id;
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

        $err = new ErrorLogDb([
            'level'       => strval($level),
            'code'        => strval($code),
            'description' => strval($message),
            'user_id'     => static::getUserID(),
            'url'         => $url,

            'meta' => [
                //            'url'   => $url,
                'file'         => self::GetProtectedProperty($exception, 'file') ?: '',
                'line'         => self::GetProtectedProperty($exception, 'line') ?: '',
                'trace'        => ($excArray['stack-trace'] ?? null) ?: '',
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
            ],
        ]);

        if (!$err->save()) {
            $e = $err->getErrors();
            var_dump($e);
            die();
        }

        return $err->id;
    }

    public static function RegisterErrorByPHPError($error_last = null)
    {
        if (!is_null($error_last)) {
            $name = Yii::t('yii', 'Error') . ' (#' . $error_last['type'] . ')';

            $code    = $error_last['type'];
            $message = $error_last['message'];
            $level   = ErrorLogDb::mapPHPLevel($error_last['type']);
            $meta    = [
                'file' => $error_last['file'],
                'line' => $error_last['line'],
            ];

            return ErrorLogDb::RegisterError($code, $message, $level, $meta);
        }

        return 0;
    }

    public static function ConvertExceptionToArray($exception)
    {
        if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
            $exception = new HttpException(500, 'There was an error at the server.');
        }

        $reflect = new \ReflectionClass($exception);

        $array = [
            'name'    => ($exception instanceof \Exception || $exception instanceof \ErrorException) ? $reflect->getShortName() : 'Exception',
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
        ];

        if ($exception instanceof HttpException) {
            $array['status'] = $exception->statusCode;
        }

        if (YII_DEBUG) {
            $array['type'] = get_class($exception);
            if (!$exception instanceof UserException) {
                $array['file']        = $exception->getFile();
                $array['line']        = $exception->getLine();
                $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof \yii\db\Exception) {
                    $array['error-info'] = $exception->errorInfo;
                }
            }
        }

        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = self::ConvertExceptionToArray($prev);
        }

        return $array;
    }

    public static function GetProtectedProperty($obj, $prop)
    {
        $reflection = new \ReflectionClass($obj);
        $propExists = property_exists($obj, $prop);

        if ($propExists) {
            $property = $reflection->getProperty($prop);
            $property->setAccessible(true);

            return $property->getValue($obj);
        }

        return null;
    }
}