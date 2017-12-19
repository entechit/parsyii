<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SourceSiteForm;
use app\models\ParsModel;
use app\models\Cms;

class DirectoriesController extends Controller
{

    public function actionIndex()
    {
        return $this->render('directories', []);
    }

    public function actionCms()
    {
        $query = Cms::FindFull();
        $model = new Cms();
        return $this->render('cms', ['model' => $model, 'query'=> $query]);
    }
    
    public function actionCms_create()
    {
        if (empty($_POST['Cms']['dc_id']))
        {
          $model = new Cms();       
          $model->dc_name = $_POST['Cms']['dc_name'];
        }  
        else
        {
            $model = Cms::findOne($_POST['Cms']['dc_id']);
            $model->dc_name = $_POST['Cms']['dc_name'];            
        }        
        $model->save();
        return $this->render('cms', ['model' => $model]);
    }
    
    public function actionCms_del()
    {
        $model = new Cms();       
        $model->deleteAll('dc_id = '.$_GET['id']);
        $query = Cms::FindFull();
        return $this->render('cms', ['model' => $model, 'query'=> $query]);
        
    } 

}