<?php

use app\helpers\Params;
use app\models\helpers\BackendView;
use app\models\User;
use app\widgets\Widgets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var \yii\web\View $this */

$dashboardLink = Yii::$app->homeUrl;

$currentUser = User::getCurrent();

if ($currentUser) {
    $userLogo = $currentUser->getMainImage([29, 29]);
} else {
    $userLogo = '';
}

$fileTranslete = ROOT_YII_DIR . 'messages/' . \Yii::$app->language . '/main.php';

?>
<?php $this->beginPage()?>
    <!DOCTYPE html>
    <html lang="<?=\Yii::$app->language?>">

    <head>
        <meta charset="<?=Yii::$app->charset?>" />

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <?=Html::csrfMetaTags()?>

        <title><?=Html::encode($this->title)?></title>

        <?php $this->head()?>

        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">

        <meta name="theme-color" content="#003153">
        <link rel="shortcut icon" href="/favicon.ico">
    </head>

    <body class="open-menu">
    <div class="overlay"></div>

    <?php $this->beginBody()?>

    <?php if ($currentUser): ?>
        <div class="head-top">
            <div class="burger"></div>
            <div class="logo-block">
                <a href="/backend">
                    <img src="/theme/backend/img/logo.png" alt="" class="logo">
                </a>
                <div class="burger-block hide">
                    <div class="burger" data-action="burger.toggle">
                        <span class="icon"></span>
                    </div>
                </div>
            </div>
            <div class="action-block">
                <div class="notifications" data-widget="push-notifications"></div>
                <?php if (Yii::$app->session->get('mimicryStatus', false)): ?>
                    <a href="/backend/user/mimicry-back?ref= <?=Yii::$app->request->url?>">
                        <div class="item back-to">
                            <div class="icon icon-action-undo">&nbsp;</div>
                            <div class="text">
                                <span>
                                    <span class="title"><?=Yii::t('main', 'Вернуться к') . ': '?></span>
                                    <span class="name"><?=Yii::$app->session->get('mimicryUserName')?></span>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endif?>
                <a href="/backend/profile" class="user item<?=(strpos(Yii::$app->request->url, '/backend/profile') !== false ? ' active' : '')?>">
                    <span class="icon" style="background-color: #708096;<?=(trim($userLogo) ? ' background-image: url(' . $userLogo . ');' : '')?>">
                        <?=(trim($userLogo) ? '' : substr($currentUser->username, 0, 1))?>
                    </span>
                    <span class="text">
                        <?=$currentUser->title?>
                    </span>
                </a>
                <a href="/backend/main/logout" class="item">
                    <i class="icon-logout"></i>
                </a>
            </div>
        </div>
        <div class="wrap <?=Yii::$app->controller->rootClass?>">
            <div class="container">
                <?=Breadcrumbs::widget([
                    'homeLink' => [
                        'label' => Yii::t('app', 'Dashboard'),
                        'url'   => $dashboardLink,
                    ],
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ])?>
                <?=$content?>
            </div>
        </div>
    <?php else: ?>
        <div class="wrap <?=Yii::$app->controller->rootClass?>">
            <div class="container">
                <?=$content?>
            </div>
        </div>
    <?php endif?>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; <?=Yii::t('app', 'My Company')?> <?=date('Y')?></p>
            <p class="pull-right"><?=Yii::powered()?></p>
        </div>
    </footer>

    <?php $this->endBody()?>

    <script>
        (function() {
            var _library = window.common_widgets;

            var _widget = new _library.PushNotifications('[data-widget="push-notifications"]', {});
        })();
    </script>
    </body>

    </html>
<?php $this->endPage()?>