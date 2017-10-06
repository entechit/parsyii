<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;

$this->title = 'Результат список найденных ссылок для анализа';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (empty($model->ss_id)): ?>
        <h3>Анализ не может быть выполнен, т.к. не выбран URL для анализа.</h3>;
    <?php 
    else:
    	echo "<h3>".$model->ss_url."</h3>";
    endif; 
    ?>

</div>
<div class="row">
	  <?php if (isset($data_sps)): ?>
                <?php \yii\widgets\Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $data_sps,
                        'columns' => [
                           //['class' => 'yii\grid\SerialColumn'],
                            ['attribute'=>'sp_id',
                                'label'=>'ИД',],
                            ['attribute'=>'sp_url',
                                'label'=>'URL Страницы',],    
                            ['class' => 'yii\grid\ActionColumn'],
                        ],
                        'layout'=>"{sorter}\n{pager}\n{summary}\n{items}",
                    ]); ?>
                <?php \yii\widgets\Pjax::end(); ?>
            <?php else: ?>
                <p>Нет данных для отображения</p>
            <?php endif; ?>
</div>