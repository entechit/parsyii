<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\ContactForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Сайт Донор';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-contact">
    <h1><?= Html::encode($this->title) ?></h1>


        <p>
            Шаг № 1. Для анализа сайта введите его полный Url в поле.
        </p>

        <div class="row">
            <div class="col-lg-5">

                <?php $form = ActiveForm::begin(['id' => 'contact-form']) ; ?>

                    <?= $form->field($model, 'ss_url')->textInput(['autofocus' => true]) ->label('URL анализируемого сайта ') ; ?>

                    <?= $form->field($model, 'ss_format')->dropdownList(
                        [
                        'PrestaShop' => 'PrestaShop',
                        'WebAsyst Shop-Script' => 'WebAsyst Shop-Script',
                        'OpenCart' => 'OpenCart'
                        ],
                        ['options' =>[ 'PrestaShop' => ['Selected' => true]]]) -> label('CMS на котором построен сайт') ?>

                        <?= $form->field($model, 'ss_descript')->label('Примечание') ; ?>

                    <div class="form-group">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'contact-button']) ?>
                    </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>

        <div class="row">
            <div class="col-lg-5">
                <?= Html::a('Отобразить таблицу существующих сайтов', ['/site/getsourcesitelist'], ['class'=>'btn btn-primary']) ?>
                <!--?= Html::button('Отобразить таблицу существующих сайтов', ['class' => 'teaser']) ?-->
            </div>
        </div>

        <div class="row">
            <?= if (isset($res)) ?>
            
            <?= else: ?>
                <p>Нет данных для отображения</p>
            <?= endif; ?>

        </div>


</div>