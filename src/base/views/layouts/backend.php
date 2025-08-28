<?php
/* @var $content string */

use app\assets\AppAsset;
use app\models\helpers\BackendView;
use app\models\User;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use Yii;

AppAsset::register($this);

$dashboardLink = Yii::$app->homeUrl;

$currentUser = User::getCurrent();

if ($currentUser) {
    $userLogo = $currentUser->getMainImage([29, 29]);
} else {
    $userLogo = '';
}

$menu = [
    'Логи' => [
        'image' => 'fa fa-th',
        'menu'  => [
            [
                'title'   => 'Действия',
                'link'    => ['/logs-action/index'],
                'image'   => 'fa fa-laptop',
            ],
            [
                'title'   => 'Ошибки',
                'link'    => ['/logs-error/index'],
                'image'   => 'fa fa-laptop',
            ],
            [
                'title'   => 'События',
                'link'    => ['/logs-event/index'],
                'image'   => 'fa fa-laptop',
            ],
        ],
    ],
    'Вернуться' => [
        'image' => 'fa fa-th',
        'link'    => ['/backend'],
    ],
];

?>
<?php $this->beginPage() ?>
    <!DOCTYPE html>
    <html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?=Html::csrfMetaTags()?>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body>
    <?php $this->beginBody() ?>
    <div class="page-wrapper">
        <?php if ($currentUser): ?>
            <header class="header">
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
            </header>
        <?php endif; ?>

        <div class="main-content-wrapper">
            <nav class="nav-menu">
                <?php
                $menuItems = [];
                foreach ($menu as $label => $item) {
                    if (isset($item['menu'])) {
                        $subItems = [];
                        foreach ($item['menu'] as $subItem) {
                            $subItems[] = [
                                'label' => Html::tag('i', '', ['class' => $subItem['image']]) . ' ' . $subItem['title'],
                                'url' => Url::to($subItem['link']),
                            ];
                        }
                        $menuItems[] = [
                            'label' => Html::tag('i', '', ['class' => $item['image']]) . ' ' . $label,
                            'items' => $subItems,
                        ];
                    } else {
                        $menuItems[] = [
                            'label' => Html::tag('i', '', ['class' => $item['image']]) . ' ' . $label,
                            'url' => Url::to($item['link']),
                        ];
                    }
                }
                echo \yii\widgets\Menu::widget([
                    'options' => ['class' => 'sidebar-menu'],
                    'encodeLabels' => false,
                    'items' => $menuItems,
                ]);
                ?>
            </nav>

            <div class="content-wrapper">
                <div class="container-fluid">
                    <?= Breadcrumbs::widget([
                        'links' => $this->params['breadcrumbs'] ?? [],
                    ]) ?>

                    <?= $content ?>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <p class="pull-left">&copy; <?=Yii::t('app', 'My Company')?> <?=date('Y')?></p>
                <p class="pull-right"><?=Yii::powered()?></p>
            </div>
        </footer>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>