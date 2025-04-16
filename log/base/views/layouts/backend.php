<?php

use app\assets\AppAsset;
use app\assets\DateTimePickerAsset;
use app\helpers\Params;
use app\models\helpers\BackendView;
use app\models\User;
use app\widgets\Widgets;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

/** @var \yii\web\View $this */
/** @var string $content */
AppAsset::register($this);
DateTimePickerAsset::register($this);

$dashboardLink = Yii::$app->homeUrl;

$currentUser = User::getCurrent();

if ($currentUser) {
    $userLogo = $currentUser->getMainImage([29, 29]);
} else {
    $userLogo = '';
}

$fileTranslete = ROOT_YII_DIR . 'messages/' . \Yii::$app->language . '/main.php';

$LOCALIZATION = require ROOT_YII_DIR . 'messages/ru-RU/main.php';

if (file_exists($fileTranslete)) {
    $LOCALIZATION = require $fileTranslete;
}
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

    <script src="https://maps.googleapis.com/maps/api/js?key=<?=\Yii::$app->params['googleMapsApiKey']; ?>&callback=initMap" async defer></script>

    <script>
        window['SYSTEM_SETTINGS_SCHEME'] = <?=require __DIR__ . '/schemas/system_settings.php'; ?>;
        window['REDACTOR_WIDGETS'] = JSON.parse(
            <?php if(Params::widgetsParserVer() == 'v2'): ?>
                <?= Widgets::getSchemas(); ?>
            <?php else: ?>
                <?= require __DIR__ . '/schemas/redactor_widgets.php'; ?>
            <?php endif; ?>
        );

        window.LOCALIZATION = <?=json_encode($LOCALIZATION); ?>
    </script>

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
        <div class="nav-menu">
            <div class="list-menu">
                <?php $menu = require ROOT_YII_DIR . 'models/helpers/Menu/BackendMenu.php';
        ?>

                <?php foreach ($menu as $title => $_menu): ?>
                    <?php $settingMenu = [
                        'connect' => false,
                        'type_id' => 0,
                        'image'   => false,
                    ];

                    if (isset($_menu['menu'])) {
                        $settingMenu = array_merge($settingMenu, $_menu);
                        $_menu       = $_menu['menu'];
                    }

                    $activeMain = BackendView::getActiveSubmenu($_menu);
                    ?>

                    <?php if ($settingMenu['connect']): ?>
                        <div class="block-menu with-connect" data-menu-type="<?=$settingMenu['type_id']?>">
                            <div class="header">
                                <?=$title?>
                            </div>
                            <div class="connect-btn">
                                <a href="<?=$settingMenu['connect']?>">
                                    <span>
                                        Подключить
                                    </span>
                                    <div class="badge">+</div>
                                </a>
                            </div>
                            <ul class="menu">
                                <?php foreach ($_menu as $key => $_submenu): ?>
                                    <?php if ($_submenu === false) {
                                        continue;
                                    }?>
                                    <li class="item <?=$key === $activeMain ? 'active' : ''?>">
                                        <a href="<?=Url::to($_submenu['link'])?>" class="title-block">
                                            <span class="icon">
                                                <i class="<?=$_submenu['image']?>"></i>
                                            </span>
                                            <span class="title">
                                                <?=$_submenu['title']?>
                                            </span>
                                            <span class="arrow"></span>
                                            <span class="triangle"></span>
                                        </a>

                                        <ul class="submenu">
                                            <li class="head">
                                                <a href="#">
                                                    <?=$_submenu['title']?>
                                                </a>
                                            </li>
                                            <?php if (isset($_submenu['submenu'])): ?>
                                                <?php $activeSub = BackendView::getActiveSubmenu($_submenu['submenu']); ?>
                                                <?php foreach ($_submenu['submenu'] as $key2 => $_submenu2): ?>
                                                    <li <?=$key2 === $activeSub ? 'class="active"' : ''?>>
                                                        <?=Html::a($_submenu2['title'], $_submenu2['link']); ?>
                                                    </li>
                                                <?php endforeach?>
                                            <?php endif?>
                                        </ul>
                                    </li>
                                <?php endforeach?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <div class="block-menu <?=($activeMain !== false || count($menu) == 1 ? 'open' : '')?>" data-menu-type="<?=$settingMenu['type_id']?>">
                            <div class="header">
                                <?=$settingMenu['image'] !== false ? '<i class="' . $settingMenu['image'] . '"></i>' : ''?>

                                <?=$title?>

                                <?php if (isset($settingMenu['quantity'])): ?>
                                    <div class="quantity <?=intval($settingMenu['quantity']) == 0 ? 'empty' : ''?>">
                                        <span>
                                            <?=$settingMenu['quantity']?>
                                        </span>
                                    </div>
                                <?php endif?>
                                <i class="fa fa-angle-down" aria-hidden="true"></i>
                            </div>
                            <div class="connect-btn">
                                <a href="/backend/market-category/tree#connect_<?=$settingMenu['type_id']?>">
                                    <span>
                                        Подключить
                                    </span>
                                    <div class="badge">+</div>
                                </a>
                            </div>
                            <ul class="menu">
                                <?php foreach ($_menu as $key => $_submenu): ?>
                                    <?php if ($_submenu === false) {
                                        continue;
                                    }?>
                                    <li class="item <?=$key === $activeMain ? 'active' : ''?>">
                                        <a href="<?=Url::to($_submenu['link'] ?? '')?>" class="title-block">
                                            <?php if (isset($_submenu['image'])): ?>
                                                <span class="icon">
                                                    <i class="<?=$_submenu['image']?>"></i>
                                                </span>
                                            <?php endif?>
                                            <span class="title">
                                                <?=$_submenu['title'] ?? ''?>
                                            </span>
                                            <span class="arrow"></span>
                                            <span class="triangle"></span>
                                        </a>

                                        <ul class="submenu">
                                            <li class="head">
                                                <a href="#">
                                                    <?=$_submenu['title'] ?? ''?>
                                                </a>
                                            </li>
                                            <?php if (isset($_submenu['submenu'])): ?>
                                                <?php $activeSub = BackendView::getActiveSubmenu($_submenu['submenu'], Yii::$app->request->url); ?>
                                                <?php foreach ($_submenu['submenu'] as $key2 => $_submenu2): ?>
                                                    <li <?=$key2 === $activeSub ? 'class="active"' : ''?>>
                                                        <?=Html::a($_submenu2['title'], $_submenu2['link']); ?>
                                                    </li>
                                                <?php endforeach?>
                                            <?php endif?>
                                        </ul>
                                    </li>
                                <?php endforeach?>
                            </ul>
                        </div>
                    <?php endif?>
                <?php endforeach?>
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