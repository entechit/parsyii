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
use app\models\Customer;
use app\models\Source_site;

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
    
    public function actionCustomer()
    {
        $query = Customer::FindFull();
        $model = new Customer();       
        //echo $query->createCommand()->getRawSql();
        //echo $dataProvider->totalCount;        
        return $this->render('customer', ['model' => $model, 'query'=> $query]);
    }
    
    public function actionSource_site()
    {
        //print_r($_GET); echo '1';
        $customer ='';
        if (isset($_GET['customer']))
          $customer = $_GET['customer'];
        //  
        $query = source_site::FindFull($customer);
        $model = new source_site();
        return $this->render('source_site', ['model' => $model, 'query'=> $query]);
    }
    //  Редактирование данных
    //  CMS
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
    //
    //  Customer
    //
    public function actionCustomer_create()
    {
        if (empty($_POST['Customer']['cust_id']))
        {
          $model = new Customer();       
          $model->cust_name = $_POST['Customer']['cust_name'];
        }  
        else
        {
            $model = Customer::findOne($_POST['Customer']['cust_id']);
            $model->cust_name = $_POST['Customer']['cust_name'];            
        }        
        $model->save();        
        return $this->render('customer', ['model' => $model]);
    }
    
    public function actionCustomer_del()
    {
        $model = new Customer();       
        $model->deleteAll('cust_id = '.$_GET['id']);
        $query = Customer::FindFull();
        return $this->render('customer', ['model' => $model, 'query'=> $query]); 
    }

}