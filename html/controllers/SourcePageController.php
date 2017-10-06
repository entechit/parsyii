<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\SourcepageModel;

class SourcepageController extends Controller
{

    public $model;


   //  function __construct(){
    //    $model = new ParsModel();
        //$db = Yii::$app->db;

   // }

 
    public function actionGetlistpages()
    {
        
        $this->model = new SourcepageModel();

        $temp_ss_ids = Yii::$app->request->post('ParsModel');

         if ($temp_ss_ids['ss_id']!==0){
            $this->model->ss_id = $temp_ss_ids['ss_id'];
            $this->model->ss_url = $temp_ss_ids['ss_url'];

         }

         ?>
            <script type="text/javascript">alert('actionGetlistpages')</script>
         <?php
         $data_sps = $this->model->GetDataSP($this->model);

        return $this->render('v_sourcepage', ['model' => $this->model, 'data_sps'=>$data_sps]);

    }


}