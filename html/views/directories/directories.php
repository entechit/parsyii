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

<?php
echo Html::a('Cms', ['/directories/cms'], ['class' => 'btn .btn-link']);

?>