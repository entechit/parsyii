<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
//use yii\helpers\Url;

$this->title = 'Справочники';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>    
</div>

<div class="list-group" style="width: 200px;">
<?php
echo Html::a('Cms', ['/directories/cms'], ['class' => 'list-group-item list-group-item-action']);
echo Html::a('Заказчики', ['/directories/customer'], ['class' => 'list-group-item list-group-item-action']);
echo Html::a('Источники сайтов', ['/directories/source_site'], ['class' => 'list-group-item list-group-item-action']);
?>
</div>