<?php


namespace BoltSystem\Yii2Logs\log\error\handlers;


use Yii;
use yii\helpers\Url;
use yii\web\ErrorHandler as BaseErrorHandler;

class ErrorHandler extends BaseErrorHandler
{
    /**
     * @var array Used to specify which errors this handler should process.
     *
     * Default is ['fatal' => true, 'catchable' => E_ALL | E_STRICT ]
     *
     * E_ALL | E_STRICT is a default from set_error_handler() documentation.
     *
     * Set
     *     'catchable' => false
     * to disable catchable error handling with this ErrorHandler.
     *
     * You can also explicitly specify, which error types to process, i. e.:
     *     'catchable' => E_ALL & ~E_NOTICE & ~E_STRICT
     */
    public $error_types;

    /**
     * @var boolean Used to specify display_errors php ini setting
     */
    public $display_errors = false;

    /**
     * @var string Used to reserve memory for fatal error handler.
     */
    private $_memoryReserve;
    /**
     * @var \Exception from HHVM error that stores backtrace
     */
    private $_hhvmException;

    private $bypassCodesList = [
        400,
        401,
        402,
        403,
        404,
        405,
    ];

    protected function _handleError($code, $message, $errID)
    {
        $errObj = [
            'status'     => 'ERROR',
            'errors'     => [$message],
            'error_code' => $code,
            'error_id'   => $errID,
        ];

        if (
            Yii::$app->request->getIsAjax() ||
            strpos(Yii::$app->request->url, '/api/') === 0
        ) {
            ob_clean();

            Yii::$app->response->statusCode = in_array($code, $this->bypassCodesList) ? $code : 500;
            Yii::$app->response->format     = 'json';
            Yii::$app->response->content    = json_encode($errObj);

            Yii::$app->response->headers->set('Access-Control-Allow-Origin', '*');

            Yii::$app->response->send();

            exit;
        }

        if (strlen($errID) < 36) {
            header('Location: ' . Url::to(['/backend/error-log/page-corrupted', 'id' => $errID]));
            exit;
        }
        header('Location: ' . Url::to(['/logs-error/page-corrupted', 'id' => $errID, 'err_obj' => $errObj]));
    }

    public function handleFatalError()
    {
        $e = error_get_last();

        if (is_null($e)) {
            return;
        }

        $code = $e['type'];

        $message = $e['message'];
        $level   = Yii::$app->errorLog::mapPHPLevel($e['type']);
        $meta    = ['file' => $e['file'], 'line' => $e['line']];

        $errID = Yii::$app->errorLog::RegisterError($code, $message, $level, $meta);

        if (!$errID) {
            return;
        }

        $this->_handleError($code, Yii::t('app', 'Fatal error #{error_id}', ['error_id' => $errID]), $errID);
    }

    public function handleError($code, $message, $file, $line)
    {
        if (!(error_reporting() & $code)) {
            return;
        }

        try {
            $e = error_get_last();

            if (is_null($e)) {
                return;
            }

            $type = Yii::$app->errorLog::mapPHPLevel($code); //ErrorLog::LEVEL_EXCEPTION;

            if (!is_null($e)) {
                $type = Yii::$app->errorLog::mapPHPLevel($e['type']);
            }

            $errID = Yii::$app->errorLog::RegisterError($code, $message, $type, ['file' => $file, 'line' => $line]);

            if (!$errID) {
                return;
            }

            if ($type == 'warning' || $type == 'notice') {
                return;
            }

            $this->_handleError($code, $message, $errID);
        } catch (\Throwable$e) {
            echo 'Error when error: ' . $e->getMessage();

            exit();
        }
    }

    public function handleException($exception)
    {
        try {
            $level = false;

            if ($exception instanceof \yii\web\HttpException || $exception instanceof \yii\web\NotFoundHttpException) {
                $level = 'http exception';
            }

            $errID = Yii::$app->errorLog::RegisterErrorByErrorException($exception, $level);

            if (!$errID) {
                return;
            }
            $code = Yii::$app->errorLog::GetProtectedProperty($exception, 'statusCode');

            $this->_handleError($code, $exception->getMessage(), $errID);
        } catch (\Exception$e) {
            // an other exception could be thrown while displaying the exception
            $this->handleFallbackExceptionMessage($e, $exception);
        } catch (\Throwable$e) {
            // additional check for \Throwable introduced in PHP 7
            $this->handleFallbackExceptionMessage($e, $exception);
        }
    }
}