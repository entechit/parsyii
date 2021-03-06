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
use app\models\Source_pageSearch;


$this->title = 'Результаты анализа сайтов';
$this->params['breadcrumbs'][] = ['label' => 'Справочники', 'url'=> ['/directories/']];
$title = $this->title;
if (isset($_GET['sp_ss_id']))
  $title .= ' (Сайт: '. $ss['ss_url']. ' )';
$this->params['breadcrumbs'][] = $title;

$this->registerJs(
   '$("document").ready(function(){
        $("#new_source_page").on("pjax:end", function() {
            $.pjax.reload({container:"#source_page_list"});  //Reload GridView
            $("#modal").modal("hide");
            $("body").removeClass("modal-open");
            $(".modal-backdrop").remove();
        });
        //
       
        $("#source_page_list").on("pjax:end", function() {
          if (need_update)
          {          
            $.pjax.reload({container:"#source_page_list", push: false, replace: false});  //Reload GridView
            need_update = 0;
           } 
        });
       
       
    });'
);


echo '
<script type="text/javascript">
 var need_update=0;
 function edit_click(id, new_page)
 {
   
   if(new_page)
   {
     $("#source_page-sp_id").val("");
      document.getElementById(\'modalHeader\').innerHTML = \'Копировать\';
   }
   else
   {
     $("#source_page-sp_id").val(id);
     document.getElementById(\'modalHeader\').innerHTML = \'Редактировать\';
   }  
   //
   $("#source_page-sp_url").val($(\'*[data-key="\'+id+\'"]\').find(\'td:eq(3)\').text());
  
   var site_name = $(\'*[data-key="\'+id+\'"]\').find(\'td:eq(2)\').text();
   var page_name = $(\'*[data-key="\'+id+\'"]\').find(\'td:eq(5)\').text();
   $("#source_page-sp_ss_id").find("option:contains("+site_name+")").prop("selected", true);
   $("#source_page-sp_dp_id").find("option:contains("+page_name+")").prop("selected", true);
  
   var sp_parsed = $(\'*[data-key="\'+id+\'"]\').find(\'td:eq(6)\').text();  
   $("#source_page-sp_parsed [value="+sp_parsed+"]").attr("selected", "selected");
   
   $("#source_page-sp_errors").val($(\'*[data-key="\'+id+\'"]\').find(\'td:eq(8)\').text());
   
   $("#modal").modal("show")
        .find("#modalContent")
        .load($(this).attr("value"));
   return false;
 };
 //
 function add_click()
 {
   document.getElementById(\'modalHeader\').innerHTML = \'Добавить\';
   $("#source_page-ss_id").val("");
   $("#source_page-ss_url").val("");
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

<?php Pjax::begin(['id' => 'new_source_page', 'enablePushState' => false]); ?>    
<?php
Modal::begin([
'id' => 'modal',
'header' => '<h2><span id="modalHeader">Добавить</span> результат анализа сайта</h2>',
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
 
    //
    $items = ArrayHelper::map($sites,'ss_id','ss_url');
    $params = [
        'prompt' => 'Укажите сайт'
    ];
    echo $form->field($model, 'sp_ss_id')->dropDownList($items,$params)->label('Сайт');
    // 
    $items = ArrayHelper::map($page_cms,'dp_id','dp_name');
    $params = [
        'prompt' => 'Укажите тип страницы'
    ];
    echo $form->field($model, 'sp_dp_id')->dropDownList($items,$params)->label('Тип страницы');
   //
    $items = [0 => 'нет', 1 => 'да'];
    $params = [];
    echo $form->field($model, 'sp_parsed')->dropDownList($items,$params)->label('Проанализирована');
    //
    echo $form->field($model, 'sp_errors')->textarea(['rows' => '4'])->label('Ошибки');  
    
  ?>


 <div class="form-group">
 <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'name' => 'edit-button']) ?>
 </div>

 <?php ActiveForm::end(); ?>
 <?php
 Modal::end();
Pjax::end(); ?>


<?php Pjax::begin(['id' => 'source_page_list', 'enablePushState' => false]);

$searchModel = new Source_pageSearch();
$dataProvider = $searchModel->search(Yii::$app->request->get());
/*
        $dataProvider = new ActiveDataProvider([
          'query' => $query,
          'pagination' => [
            'pageSize' => 20,
          ],
        ]);
  */      
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
    'filterModel' => $searchModel,
     
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
        'attribute' => 'ss_url',
        'label' => 'Сайт',       
        //'format' => 'raw',
    ],
    [
        'attribute' => 'sp_url',
        'label' => 'url',
    ],
    [
        'attribute' => 'sp_dp_id',
        'label' => 'Тип страницы id',
    ],
    [
        'attribute' => 'dp_name',
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

    [
        'attribute' => 'sp_errors',
        'label' => 'Ошибки',
    ]   	
    ,    
 
         
         [
            'class' => 'yii\grid\ActionColumn',
            'header'=>'Действия', 
            'headerOptions' => ['width' => '60'],
            'template' => '{update} {copy} {delete}',
         
            'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/directories/source_page_del', 'id' => $key], [
                            'title' => Yii::t('yii', 'Delete'),
                            'data-confirm'=>"Хотите удалить?",
                            'data-pjax' => '1',
                            'onclick'=>"return del_click()",
                        ]);
                    },
                    'copy' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-copy"></span>', ['#'], [
                            'title' => Yii::t('yii', 'Copy'),
                            'data-pjax' => '#model-grid',
                            'onclick'=>"return edit_click($key, 1)",
                        ]
                      );
                    },                   
                    'update' => function ($url, $model, $key) {
                        return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['#'], [
                            'title' => Yii::t('yii', 'Update'),
                            'data-pjax' => '#model-grid',
                            'onclick'=>"return edit_click($key, 0)",
                        ]
                      );
                    },
                    
                ],                
        ],         
    ],
]);

?>
<?php Pjax::end(); ?>
