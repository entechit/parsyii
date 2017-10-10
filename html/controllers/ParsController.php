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

 
    public function actionPars()
    {

        $this->model = new ParsModel();
        if (Yii::$app->request->post('source-site-action-button')!==null): // нажата кнопка запустить анализ 
                
            $url_fields=Yii::$app->request->post('SourceSiteForm');

            $this->model->main_pars_f($url_fields);
            
            if (!empty($url_fields['ss_id'])){
                $session = Yii::$app->session;
                $session->set('ss_id', $url_fields['ss_id']);
            };

        endif;

        return $this->render('v_parsprogress', ['model' => $this->model, 'parslog'=>$this->model->parslog,]);

    }
}