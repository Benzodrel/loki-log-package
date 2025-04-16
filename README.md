# Настройки подключения к Loki:
   1 - Добавить в config/params.json переменные
```
   "log": "loki",
   "log": "db",
    "loki": {
        "enabled" : true,
        "lokiUrl" : "http://loki:3100/loki/api/v1/push",
        "user": "loki",
        "password": "..."
    },
   ```
В зависимости от желаемого метода логирования (База данных Mysql, Loki) <br>

2 - Добавить в config/main.php код:
```
if (isset($params['log']) && $params['log'] === 'loki') {
   $config['components']['log'] = [
   'traceLevel'    => 3,
   'flushInterval' => 100,
   'targets'       => [
   [
   'class'   => BoltSystem\Yii2Logs\log\target\CustomLokiLogTarget::class,
   'enabled' => $params['loki']['enabled'],
   'prefix' => function ($message) {

                    $code    = $message[0]['code']??'-';
                    $id      = $message[0]['id']??'-';

                    $request = Yii::$app->getRequest();
                    $ip = $request instanceof yii\web\Request ? $request->getUserIP() : '-';

                    $user = Yii::$app->has('user', true) ? Yii::$app->get('user') : null;
                    if ($user && ($identity = $user->getIdentity(false))) {
                        $userID = $identity->getId();
                    } else {
                        $userID = '-';
                    }

                    return "[$ip][$userID][$id][$code]";
                },
                'lokiPushUrl'      => $params['loki']['lokiUrl'],
                'lokiAuthUser'     => $params['loki']['user'], // HTTP Basic Auth User
                'lokiAuthPassword' => $params['loki']['password'], // HTTP Basic Auth Password

                'levels'  => ['error', 'warning', 'info', 'profile'],
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
```
Добавить в config/main.php 
```
'bootstrap' => ['log'];
```

Добавить в config/main.php 

```
'bootstrap' => ['Log', 'errorLog', 'actionLog', 'eventLog', 'profiler'];
```
и так же в components 
```
 'errorLog' => [
            'class' => BoltSystem\Yii2Logs\log\error\ErrorLog::class,
        ],
        'Log' => [
            'class' => BoltSystem\Yii2Logs\log\action\Log::class,
        ],
        'profiler' => [
            'class' => BoltSystem\Yii2Logs\log\Profiler::class,
        ],
        'eventLog' => [
            'class' => BoltSystem\Yii2Logs\log\event\EventLog::class,
        ],
```