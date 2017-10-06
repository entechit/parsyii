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


   <?php $form = ActiveForm::begin([
                'id' => 'goto-sourcepage', 
                'action'=>'/sourcepage/getlistpages' ]);  // вызываем экшн контроллера
   ?> 
   		<?php echo "<h3>".$model->ss_url."</h3>"; ?>
    
     	<?= $form->field($model, 'ss_id')->hiddenInput(['id'=> "ss_id"])->label(''); ?>
        <?= $form->field($model, 'ss_url')->hiddenInput(['id'=> "ss_url"])->label(''); ?>

      	<?= Html::submitButton('перейти к списку найденных страниц', ['class' => 'btn btn-primary', 'name' => 'goto-sourcepage']); ?>

    <?php ActiveForm::end(); ?>

    <div>
      <?php echo $parslog; ?>
    </div>


</div>
