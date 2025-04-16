<?php
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title                   = $title;
$this->params['breadcrumbs'][] = $this->title;

if (!isset($emptyText)) {
    $emptyText = Yii::t('main', 'BASE_INDEX_EMPTYTEXT_Ничего не найдено');
}

function render_toolbar_item($item)
{
    if ($item['type'] == 'link') {
        $href = "";

        if (!isset($item['options'])) {
            $item['options'] = [];
        }

        if (is_string($item['href'])) {
            $href = trim($item['href']);
        } else {
            $href = trim($item['href'](Yii::$app->request->url));
        }

        return '<li>' . Html::a($item['title'], $href, $item['options']) . '</li>';
    }

    if ($item['type'] == 'dropdown') {
        $menu = [];

        foreach ($item['options']['list'] as $_child_item) {
            $menu[] = render_toolbar_item($_child_item);
        }

        return '
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">' . $item['title'] . ' <b class="caret"></b></a>
                <ul class="dropdown-menu">
                    ' . implode("\n", $menu) . '
                </ul>
            </li>
        ';
    }
};

$breadcrumbs = Yii::$app->session->getFlash('breadcrumbs', false, true);
?>
    <div class="index-view index-page">

        <h1>
            <?= Html::encode($this->title) ?>
        </h1>

        <?php if ($breadcrumbs && is_array($breadcrumbs) && !empty($breadcrumbs)) : ?>
            <div class="row">
                <?= Breadcrumbs::widget([
                    'links'    => $breadcrumbs,
                    'options'  => ['class' => 'breadcrumb', 'style' => 'position: inherit; display: block; margin: 10px;'],
                    'homeLink' => false,
                ]); ?>
            </div>
        <?php endif ?>

        <?php if (isset($toolbar) && is_array($toolbar)) : ?>
            <nav class="navbar navbar-default" role="navigation">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>

                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav">
                            <?php foreach ($toolbar as $item) : ?>
                                <?= render_toolbar_item($item) ?>
                            <?php endforeach ?>
                        </ul>
                    </div>
                </div>
            </nav>
        <?php endif ?>

        <?php if (isset($headerWidgetsList) && is_array($headerWidgetsList)) : ?>
            <?php foreach ($headerWidgetsList as $headerWidget => $widgetParams) : ?>
                <?php if (is_array($widgetParams)) : ?>
                    <?= $headerWidget::getRender($widgetParams); ?>
                <?php elseif (is_string($widgetParams)) : $widget = $widgetParams; ?>
                    <?= $widget::getRender(); ?>
                <?php endif ?>
            <?php endforeach ?>
        <?php endif ?>

        <div class="gridview-wrapper">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel'  => $searchModel,
                'columns'      => $columns,
                'emptyText'    => $emptyText,
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'rowOptions'   => function ($model) {
                    if ($model->hasAttribute('deleted') && $model->deleted == 1) {
                        return ['class' => 'danger'];
                    }
                    return null;
                },
            ]);
            ?>
        </div>
    </div>
<?php

$js = <<< JS
    $("td.deleted").parents('tr').addClass( 'danger' );
JS;
$this->registerJs($js);
?>