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
use yii\helpers\ArrayHelper;


$this->title = 'Результаты анализа сайтов';
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url'=> ['/directories/']];
$title = $this->title;
if (isset($_GET['ss']))
  $title .= ' (Источник: '. $ss['ss_url']. ' )';
$this->params['breadcrumbs'][] = $title;

$this->registerJs(
   '$("document").ready(function(){
        $("#new_source_site").on("pjax:end", function() {
            $.pjax.reload({container:"#source_site_list"});  //Reload GridView
            $("#modal").modal("hide");
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();
        });
        //
       
        $("#source_site_list").on("pjax:end", function() {
          if (need_update)
          {          
            $.pjax.reload({container:"#source_site_list", push: false, replace: false});  //Reload GridView
            need_update = 0;
           } 
        });
       
       
    });'
);


echo '
<script type="text/javascript">
 var need_update=0;
 function edit_click(id)
 {
   document.getElementById(\'modalHeader\').innerHTML = \'Редактировать\';
   $("#source_site-ss_id").val(id);  
   $("#source_site-ss_url").val($(\'*[data-key="\'+id+\'"]\').find(\'td:eq(1)\').text());
   $("#source_site-ss_descript").val($(\'*[data-key="\'+id+\'"]\').find(\'td:eq(4)\').text());
   var cust_name = $(\'*[data-key="\'+id+\'"]\').find(\'td:eq(2)\').text();
   var cmst_name = $(\'*[data-key="\'+id+\'"]\').find(\'td:eq(3)\').text();
   $("#source_site-ss_cust_id").find("option:contains("+cust_name+")").prop("selected", true);
   $("#source_site-ss_dc_id").find("option:contains("+cmst_name+")").prop("selected", true);
   
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
 
 function del_click()
 {
   need_update =1;  
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
<?php $form = ActiveForm::begin(['action' =>['directories/source_page_create'],'id' => 'edit-form', 'options' => ['data-pjax' => true ]] ); ?>

 <?= $form->field($model, 'sp_id')->hiddenInput()->label(false) ?>
 <?= $form->field($model, 'sp_url')->textInput(['autofocus' => true])->label('Url');
 /*
    //
    $items = ArrayHelper::map($customers,'cust_id','cust_name');
    $params = [
        'prompt' => 'Укажите заказчика'
    ];
    echo $form->field($model, 'ss_cust_id')->dropDownList($items,$params)->label('Заказчик');
    //
    $items = ArrayHelper::map($cms,'dc_id','dc_name');
    $params = [
        'prompt' => 'Укажите Cms'
    ];
    echo $form->field($model, 'ss_dc_id')->dropDownList($items,$params)->label('Cms');
    */
    //
  ?>


 <div class="form-group">
 <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'edit-button']) ?>
 </div>

 <?php ActiveForm::end(); ?>
 <?php
 Modal::end();
Pjax::end(); ?>


<?php Pjax::begin(['id' => 'source_site_list', 'enablePushState' => false]);


        $dataProvider = new ActiveDataProvider([
          'query' => $query,
          'pagination' => [
            'pageSize' => 20,
          ],
        ]);       
        //
        /*
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
            'pagesCount' => [
                'asc' => ['pagesCount' => SORT_ASC],
                'desc' => ['pagesCount' => SORT_DESC],
            ],            
            'ss_dateadd',
          ]
        ]);
        */
        
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
    [
        'attribute' => 'sp_id',
        'label' => 'Id',
    ],
    [
        'attribute' => 'sp_ss_id',
        'label' => 'Код сайта',
    ],    
    [
        'attribute' => 'sp_url',
        'label' => 'url',
    ],
    [
        'attribute' => 'sp_dp_id',
        'label' => 'Тип страницы',
    ],
    [
        'attribute' => 'sp_parsed',
        'label' => 'Страница проанализирована',
    ],    
    [
        'attribute' => 'sp_datetimeadd',
        'label' => 'Дата добавления',
    ],
  
  /*
     [
        'attribute' => 'pagesCount',
        'label' => 'Количество страниц',
        'value' => function ($data) {
            return Html::a(Html::encode($data->pagesCount), Url::to(['source_page', 'ss' => $data->ss_id]));
        },
        'format' => 'raw',
    ],
    */
    [
        'attribute' => 'sp_errors',
        'label' => 'Ошибки',
    ]   	
    ,    
 
         /*
         [
            'class' => 'yii\grid\ActionColumn',
            'header'=>'Действия', 
            'headerOptions' => ['width' => '60'],
            'template' => '{update} {delete}',
         
            'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/directories/source_site_del', 'id' => $key], [
                            'title' => Yii::t('yii', 'Delete'),
                            'data-confirm'=>"Хотите удалить?",
                            'data-pjax' => '1',
                            'onclick'=>"return del_click()",
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
         */
    ],
]);

?>
<?php Pjax::end(); ?>
