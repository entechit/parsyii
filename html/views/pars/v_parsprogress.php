<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;

$this->title = 'Результат парсинга';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (empty($model->ss_id)): ?>
        <h3>Анализ не может быть выполнен, т.к. не выбран URL для анализа.</h3>;
    <?php endif; ?>

</div>
