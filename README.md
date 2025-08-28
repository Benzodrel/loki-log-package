# Установка пакета
```
composer require bolt-system/yii2-logs
```

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
   'class'   => boltSystem\yii2Logs\src\target\CustomLokiLogTarget::class,
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
```php
 'errorLog' => [
            'class' => boltSystem\yii2Logs\src\error\ErrorLog::class,
        ],
        'Log' => [
            'class' => boltSystem\yii2Logs\src\action\Log::class,
        ],
        'profiler' => [
            'class' => boltSystem\yii2Logs\src\Profiler::class,
        ],
        'eventLog' => [
            'class' => boltSystem\yii2Logs\src\event\EventLog::class,
        ],
```
В components
```
'errorHandler' => [
'class'      => BoltSystem\Yii2Logs\log\error\handlers\ErrorHandler::class,
],
```
Так же аналогично можно подключить логирование консольных команд за исключением errorHandler, класс для него BoltSystem\Yii2Logs\src\error\handlers\ErrorHandlerConsole::class,

Используйте файл log_params чтобы передать правила доступа контроллеров и мапинг классов для action лога
Пример файла log_params.php.example, файл закинуть в папку config и в main.php добавить
```
$log_params     = require __DIR__ . '/log_params.php';
```
и в $config добавить строку
```

```

# Миграции таблиц:
В библиотеке содержатся файлы миграций таблиц для соответствующих логов:<br>
Для их использования используйте консольные команды:
```bash
php yii migrate --migrationPath=@event-migrations
php yii migrate --migrationPath=@error-migrations
php yii migrate --migrationPath=@action-migrations
```

