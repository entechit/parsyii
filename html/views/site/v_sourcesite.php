<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;

$this->title = 'Сайт Донор';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>


        <p>
            Шаг № 1. Для анализа сайта введите его полный Url в поле.
        </p>

        <div class="row">


            <?php $form = ActiveForm::begin(['id' => 'source-site-form']) ; ?>

            <div class="col-lg-6">
                <?= $form->field($model, 'ss_url')->textInput(['autofocus' => true]) ->label('URL анализируемого сайта ') ; ?>
            </div>
         
            <div class="col-lg-6">
                <?= $form->field($model, 'ss_dc_id')->dropdownList(
                     $data_dcs,
                     ['options' =>[ '1' => ['Selected' => true]]]) -> label('CMS на котором построен сайт') ?>
            </div>

            <div class="col-lg-12">
                <?= $form->field($model, 'ss_descript')->label('Примечание') ; ?>
            </div>
                

            <div class="form-group col-lg-6">
                <?= Html::submitButton('Дoбавить сайт для анализа', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>


        <div class="row">
            <!--https://nix-tips.ru/yii2-razbiraemsya-s-gridview.html-->

            <?php if (isset($data_sss)): ?>
                <?php \yii\widgets\Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $data_sss,
                        'columns' => [
                           //['class' => 'yii\grid\SerialColumn'],
                            ['attribute'=>'ss_id',
                                'label'=>'ИД',],
                            ['attribute'=>'ss_url',
                                'label'=>'URL Сайта источника',],    
                            ['attribute'=>'dc_name',
                                'label'=>'CMS на которой построен сайт',
                                'filter' => $data_dcs],    
                            ['attribute'=>'ss_dateadd',
                                'format' =>  'date',
                                'label'=>'Дата добавления',],    
                            ['attribute'=>'ss_descript',
                                'label'=>'Примечание',],    
                            ['class' => 'yii\grid\ActionColumn'],
                        ],
                        'layout'=>"{sorter}\n{pager}\n{summary}\n{items}",
                    ]); ?>
                <?php \yii\widgets\Pjax::end(); ?>
            <?php else: ?>
                <p>Нет данных для отображения</p>
            <?php endif; ?>

        </div>


</div>