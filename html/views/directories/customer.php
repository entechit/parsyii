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


$this->title = 'Заказчики';
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url'=> ['/directories/']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs(
   '$("document").ready(function(){
        $("#new_customer").on("pjax:end", function() {
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
   $("#customer-cust_id").val(id);  
   $("#customer-cust_name").val($(\'*[data-key="\'+id+\'"]\').find(\'td:eq(1)\').text());  
   $("#modal").modal("show")
        .find("#modalContent")
        .load($(this).attr("value"));
   return false;
 };
 //
 function add_click()
 {
   document.getElementById(\'modalHeader\').innerHTML = \'Добавить\';
   $("#customer-cust_id").val("");
   $("#customer-cust_name").val("");
 }
</script>
';
?>

    
    
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>    
</div>

<?php Pjax::begin(['id' => 'new_customer', 'enablePushState' => false]); ?>    
<?php
Modal::begin([
'id' => 'modal',
'header' => '<h2><span id="modalHeader">Добавить</span> Заказчика</h2>',
'toggleButton' => ['label' => 'Добавить',
                   'tag' => 'button',
                   'class' => 'btn btn-success',
                   'onclick' => 'add_click()'
                   ],


]);
?>
<?php $form = ActiveForm::begin(['action' =>['directories/customer_create'],'id' => 'edit-form', 'options' => ['data-pjax' => true ]] ); ?>

 <?= $form->field($model, 'cust_id')->hiddenInput()->label(false) ?>
 <?= $form->field($model, 'cust_name')->textInput(['autofocus' => true])->label('Название') ?>


 <div class="form-group">
 <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'edit-button']) ?>
 </div>

 <?php ActiveForm::end(); ?>
 <?php
 Modal::end();
Pjax::end(); ?>
<?php Pjax::begin(['id' => 'cust_list']);


        $dataProvider = new ActiveDataProvider([
          'query' => $query,
          'pagination' => [
            'pageSize' => 20,
          ],
        ]);       
        //
        $dataProvider->setSort([
          'attributes' => [
            'cust_id',
            'cust_name',
            'sitesCount' => [
                'asc' => ['sitesCount' => SORT_ASC],
                'desc' => ['sitesCount' => SORT_DESC],
            ],
          ]
        ]);
        
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
    [
        'attribute' => 'cust_id',
        'label' => 'Id',
    ],
    [
        'attribute' => 'cust_name',
        'label' => 'Название',
    ],    
    [
        'attribute' => 'sitesCount',
        'label' => 'Количество сайтов',
        'value' => function ($data) {
            return Html::a(Html::encode($data->sitesCount), Url::to(['source_site', 'customer' => $data->cust_id]));
        },
        'format' => 'raw',
    ],
         
         [
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
        ],
         
    ],
]);

?>
<?php Pjax::end(); ?>
