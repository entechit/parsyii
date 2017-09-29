<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\ParsModel;

class ParsController extends Controller
{

    public $model;

   //  function __construct(){
    //    $model = new ParsModel();
        //$db = Yii::$app->db;

   // }

 
    public function actionPars1()
    {
        $this->model = new ParsModel();
        if (Yii::$app->request->post('source-site-action-button')!==null): // нажата кнопка запустить анализ 
                
                $url_fields=Yii::$app->request->post('SourceSiteForm');
                $this->model->main_pars_f($url_fields);
        endif;

        return $this->render('v_parsprogress', ['model' => $this->model,]);
      //  return $this->render('v_parsprogress');

    }


}