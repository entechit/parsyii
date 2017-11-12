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

        <div class="row">
        
            <div class="col-lg-6">
            <h3>Шаг № 1. Для анализа сайта введите его полный Url в поле.</h3>

                <?php $form = ActiveForm::begin(['id' => 'source-site-form']) ; ?>

            
                    <?= $form->field($model, 'ss_url')->textInput(['autofocus' => true]) ->label('URL анализируемого сайта ') ; ?>

         
                    <?= $form->field($model, 'ss_dc_id')->dropdownList(
                         $data_dcs,
                        ['options' =>[ '1' => ['Selected' => true]]]) -> label('CMS на котором построен сайт') ?>


                    <?= $form->field($model, 'ss_descript')->label('Примечание') ; ?>

                    <?= Html::submitButton('Дoбавить сайт для анализа', ['class' => 'btn btn-primary', 'name' => 'source-site-add-button']) ?>

                <?php ActiveForm::end(); ?>

            </div>
            <div class="col-lg-6">
              <h3>Шаг № 2. Действие! Выберите, что будем делать.</h3>
                <br>
                <h4 id = "c_selected_url"></h4>
                <?php $form2 = ActiveForm::begin([
                        'id' => 'source-site-form-action', 
                        'action'=>'/pars/pars'   // вызываем экшн контроллера
                        ]); ?>

                <?= $form->field($model, 'ss_id')->hiddenInput(['id'=> "ss_id"])->label(false); ?>
                <?= $form->field($model, 'ss_url')->hiddenInput(['id'=> "ss_url"])->label(false); ?>

                <?=  $form2->field($model, 'cb_find_internal_url')->checkbox(['label' => 'Сформировать список ссылок из источника:', 'labelOptions' => [
                        'style' => 'padding-left:20px;' ],
                        'disabled' => false,
                    ]); ?>

                <?php $model->rb_url_source = 'rb_seek_url_onsite'; ?>
                    <?= $form2->field($model, 'rb_url_source')
                        ->radioList([
                            'rb_seek_url_onsite' => 'Искать ссылки на сайте',
                            'rb_seek_url_sitemap' => 'Загрузить sitemap.xml',
                            'rb_seek_url_price' => 'Дополнить по прайсу', ],
                            ['class'=>'c_sourcesite_rb'])
                        ->label(''); ?>
            


                    <?= $form2->field($model, 'cb_download_page')->checkbox(['label' => 'сохранить в файл первую не типизированную страницу', 'labelOptions' => [
                        'style' => 'padding-left:20px;' ],
                        'disabled' => false,
                    ]); ?>


                    <?= $form2->field($model, 'cb_type_source_page')->checkbox(['label' => 'типизировать найденные страницы', 'labelOptions' => [
                        'style' => 'padding-left:20px;' ],
                        'disabled' => false,
                    ]); ?>

                    <?= $form2->field($model, 'cb_pars_source_page')->checkbox(['label' => 'распарсить найденные страницы', 'labelOptions' => [
                        'style' => 'padding-left:20px;' ],
                        'disabled' => false,
                    ]); ?>

                    <?= $form2->field($model, 'cb_download_img')->checkbox(['label' => 'скачать найденные изображения', 'labelOptions' => [
                        'style' => 'padding-left:20px;' ],
                        'disabled' => false,
                    ]); ?>

                    <?= Html::submitButton('Выполнить анализ', ['class' => 'btn btn-primary', 'name' => 'source-site-action-button']); ?>

                    <?php ActiveForm::end(); ?>

            </div>
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
                        'rowOptions' => function ($model, $key, $index, $grid) {
                            return ['data'=>[
                                    'id' => $model['ss_id'],
                                    'ss_url' => $model['ss_url'],
                                    'ss_descript' => $model['ss_descript']],
                                    'onclick' => 'select_ss_id(this)',  
                                    'class' => 'ss_grid_hover'
                                ];},
                    ]); ?>
                <?php \yii\widgets\Pjax::end(); ?>
            <?php else: ?>
                <p>Нет данных для отображения</p>
            <?php endif; ?>

        </div>


</div>
<script type="text/javascript">
    function select_ss_id(that){
         $.ajax({  
         cache: false,
         type:"POST",
         data: null, 
         dataType: "text",
          success: function(){  
            $("#c_selected_url").html('URL: '+ $(that).data('ss_url') + '('+$(that).data('ss_descript')+')')
            $("#ss_url").val($(that).data('ss_url') + '('+$(that).data('ss_descript')+')')

            $("#ss_id").val($(that).data('id'))
            },   
      });  
    }
</script>