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
use app\models\Page_cms;
use app\models\Customer;
use app\models\Source_site;
use app\models\Source_page;

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
        $customer ='';
        $customer_id ='';
        
        if (isset($_GET['customer']))
        {
          $customer_id = $_GET['customer'];
          $customer = Customer::findOne($customer_id);                 
        }
        //  
        $query = source_site::FindFull($customer_id);
        $model = new source_site();
        $customers = Customer::find()->all();
        $cms = Cms::find()->all();
        return $this->render('source_site', ['model' => $model, 'query'=> $query, 'customer' =>$customer, 'customers' =>$customers, 'cms' =>$cms] );
    }
    
    public function actionSource_page()
    {
        $ss ='';
        $ss_id ='';
        //
        if (isset($_GET['sp_ss_id']))
        {
          $ss_id = $_GET['sp_ss_id'];
          $ss = source_site::findOne($ss_id);                 
        }
        //  
        
        $sites = source_site::find()->all();
        $page_cms = Page_cms::find()->all();
        
        //
        $query = source_page::FindFull($ss_id);
        $model = new source_page();
        
        return $this->render('source_page', ['model' => $model, 'query'=> $query, 'ss' =>$ss, 'sites' => $sites, 'page_cms' => $page_cms] );
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
    //
    //  source_site
    //
    public function actionSource_site_create()
    {
        if (empty($_POST['source_site']['ss_id']))
          $model = new Source_site();       
        else
          $model = Source_site::findOne($_POST['source_site']['ss_id']);                      
        //
        $model->ss_url = $_POST['source_site']['ss_url'];
        $model->ss_dc_id = $_POST['source_site']['ss_dc_id'];
        $model->ss_descript = $_POST['source_site']['ss_descript'];
        $model->ss_cust_id = $_POST['source_site']['ss_cust_id'];
        $model->save();
       return $this->render('source_site', ['model' => $model] );
    }
    
    public function actionSource_site_del()
    {
        $model = new Source_site();       
        $model->deleteAll('ss_id = '.$_GET['id']);
        $query = Source_site::FindFull('');      
        return '....loading';
    }
    //
    //  source_page
    //
    public function actionSource_page_create()
    {
        if (empty($_POST['source_page']['sp_id']))
          $model = new Source_page();       
        else
          $model = Source_page::findOne($_POST['source_page']['sp_id']);                      
        //
        $model->sp_url = $_POST['source_page']['sp_url'];
        $model->sp_ss_id = $_POST['source_page']['sp_ss_id'];
        $model->sp_dp_id = $_POST['source_page']['sp_dp_id'];
        $model->sp_parsed = $_POST['source_page']['sp_parsed'];
        $model->sp_errors = $_POST['source_page']['sp_errors'];
        $model->save();
       return $this->render('source_page', ['model' => $model] );
    }
    
    public function actionSource_page_del()
    {
        $model = new Source_page();       
        $model->deleteAll('sp_id = '.$_GET['id']);
        $query = Source_page::FindFull('');      
        return '....loading';
    }      

}