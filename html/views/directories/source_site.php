<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\grid\GridView;

use app\models\Cms;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;


$this->title = 'Источники сайтов';
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url'=> ['/directories/']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(
   '$("document").ready(function(){
        $("#new_source_site").on("pjax:end", function() {
            $.pjax.reload({container:"#cust_list"});  //Reload GridView   
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();
        });
        //
        $("#new_cms").on("pjax:begin", function() {
        });      

    });'
);


echo '
<script type="text/javascript">
 function edit_click(id)
 {
   document.getElementById(\'modalHeader\').innerHTML = \'Редактировать\';
   $("#source_site-ss_id").val(id);  
   $("#source_site-ss_url").val($(\'*[data-key="\'+id+\'"]\').find(\'td:eq(1)\').text());  
   $("#modal").modal("show")
        .find("#modalContent")
        .load($(this).attr("value"));
   return false;
 };
 //
 function add_click()
 {
   document.getElementById(\'modalHeader\').innerHTML = \'Добавить\';
   $("#source_site-ss_id").val("");
   $("#source_site-ss_url").val("");
 }
</script>
';
?>

    
    
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>    
</div>

<?php Pjax::begin(['id' => 'new_source_site', 'enablePushState' => false]); ?>    
<?php
Modal::begin([
'id' => 'modal',
'header' => '<h2><span id="modalHeader">Добавить</span> источник сайта</h2>',
'toggleButton' => ['label' => 'Добавить',
                   'tag' => 'button',
                   'class' => 'btn btn-success',
                   'onclick' => 'add_click()'
                   ],


]);
?>
<?php $form = ActiveForm::begin(['action' =>['directories/source_site_create'],'id' => 'edit-form', 'options' => ['data-pjax' => true ]] ); ?>

 <?= $form->field($model, 'ss_id')->hiddenInput()->label(false) ?>
 <?= $form->field($model, 'ss_url')->textInput(['autofocus' => true])->label('Название') ?>


 <div class="form-group">
 <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'edit-button']) ?>
 </div>

 <?php ActiveForm::end(); ?>
 <?php
 Modal::end();
Pjax::end(); ?>


<?php Pjax::begin(['id' => 'source_site_list']);


        $dataProvider = new ActiveDataProvider([
          'query' => $query,
          'pagination' => [
            'pageSize' => 20,
          ],
        ]);       
        //        
        $dataProvider->setSort([
          'attributes' => [
            'ss_id',
            'ss_url',
            'cust_name' => [
                'asc' => ['cust_name' => SORT_ASC],
                'desc' => ['cust_name' => SORT_DESC],
            ],
            'dc_name' => [
                'asc' => ['dc_name' => SORT_ASC],
                'desc' => ['dc_name' => SORT_DESC],
            ],
            'ss_descript',
            'ss_dateadd',
          ]
        ]);
        
        
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
    [
        'attribute' => 'ss_id',
        'label' => 'Id',
    ],
    [
        'attribute' => 'ss_url',
        'label' => 'url',
    ],
    [
        'attribute' => 'cust_name',
        'label' => 'Заказчик',
    ],
    [
        'attribute' => 'dc_name',
        'label' => 'Cms',
    ],    
    [
        'attribute' => 'ss_descript',
        'label' => 'Описание',
    ],
    [
        'attribute' => 'ss_dateadd',
        'label' => 'Дата добавления',
    ]
    	
    ,
    /*
    [
        'attribute' => 'sitesCount',
        'label' => 'Количество сайтов',
        'value' => function ($data) {
            return Html::a(Html::encode($data->sitesCount), Url::to(['source_site', 'customer' => $data->cust_id]));
        },
        'format' => 'raw',
    ],
    */
         
         [/*
            'class' => 'yii\grid\ActionColumn',
            'header'=>'Действия', 
            'headerOptions' => ['width' => '60'],
            'template' => '{update} {delete}',
         
            'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/directories/customer_del', 'id' => $key], [
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
                */
        ],
         
    ],
]);

?>
<?php Pjax::end(); ?>
