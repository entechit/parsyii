<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;
use app\models\Cms;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;


$this->title = 'Cms';
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url'=> ['/directories/']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(
   '$("document").ready(function(){
        $("#new_cms").on("pjax:end", function() {
            $.pjax.reload({container:"#cms_list"});  //Reload GridView   
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();
        });
        //
        $("#new_cms").on("pjax:begin", function() {
alert(4);
        });      

    });'
);


echo '
<script type="text/javascript">
 function edit_click(id)
 {
   document.getElementById(\'modalHeader\').innerHTML = \'Редактировать\';
   $("#cms-dc_id").val(id);  
   $("#cms-dc_name").val($(\'*[data-key="\'+id+\'"]\').find(\'td:eq(1)\').text());  
   $("#modal").modal("show")
        .find("#modalContent")
        .load($(this).attr("value"));
   return false;
 };
 //
 function add_click()
 {
   document.getElementById(\'modalHeader\').innerHTML = \'Добавить\';
   $("#cms-dc_id").val("");
   $("#cms-dc_name").val("");
 }
</script>
';
?>

    
    
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>    
</div>

<?php Pjax::begin(['id' => 'new_cms', 'enablePushState' => false]); ?>    
<?php
Modal::begin([
'id' => 'modal',
'header' => '<h2><span id="modalHeader">Добавить</span> Cms</h2>',
'toggleButton' => ['label' => 'Добавить',
                   'tag' => 'button',
                   'class' => 'btn btn-success',
                   'onclick' => 'add_click()'
                   ],


]);
?>
<?php $form = ActiveForm::begin(['action' =>['directories/cms_create'],'id' => 'edit-form', 'options' => ['data-pjax' => true ]] ); ?>

 <?= $form->field($model, 'dc_id')->hiddenInput()->label(false) ?>
 <?= $form->field($model, 'dc_name')->textInput(['autofocus' => true])->label('Название') ?>


 <div class="form-group">
 <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'edit-button']) ?>
 </div>

 <?php ActiveForm::end(); ?>
 <?php
 Modal::end();
Pjax::end(); ?>

<?php
$dataProvider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 20,
    ],
]);
?>

<?php Pjax::begin(['id' => 'cms_list']);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
    [
        'attribute' => 'dc_id',
        'label' => 'Id',
    ],

    [
        'attribute' => 'dc_name',
        'label' => 'Название',
    ],    
    [
        'attribute' => 'sitesCount',
        'label' => 'Количество сайтов',
    ],
    [
        'attribute' => 'customersCount',
        'label' => 'Количество заказчиков',
    ]
    
    
         ,
         [
            'class' => 'yii\grid\ActionColumn',
            'header'=>'Действия', 
            'headerOptions' => ['width' => '60'],
            'template' => '{update} {delete}',
            'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/directories/cms_del', 'id' => $key], [
                            'title' => Yii::t('yii', 'Delete'),
                            'data-pjax' => '#cms_list',
                        ]);
                    },
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['#'], [
                            'title' => Yii::t('yii', 'Update'),
                            'data-pjax' => '#model-grid',
                            'onclick'=>"return edit_click($key)",
                        ]
                      );
                    },
                    
                ],                    
        ],
    ],
]);

?>
<?php Pjax::end(); ?>
