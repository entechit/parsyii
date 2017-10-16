<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\data\SqlDataProvider;

/**
 * ContactForm is the model behind the contact form.
 */
class SourceSiteForm extends Model
{
    public $ss_id;
    public $ss_url;
    public $ss_descript;
    public $ss_dc_id;

    public $db;


    public $cb_find_internal_url;
    public $cb_type_source_page;
    public $cb_download_page;
    public $cb_pars_source_page;
    public $rb_url_source;
    public $cb_download_img;


    // Формируем переменную коннекта к базе данных
    function __construct(){
        $db = Yii::$app->db;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [
                ['ss_url', 'ss_format'], 'required', 'message'=>'Поле URL не может быть пустым.'],
            // email has to be a valid email address
                ['ss_url', 'url', 'message'=>'Некорректно указан URL'],

        ];
    }

    // выгребаем пока что все из таблицы источников сайта
    public function getdata_ss(){

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT ss.*, dc.dc_name '.
                'FROM source_site ss Left join dir_cms dc on ss.ss_dc_id = dc.dc_id ' .
                'ORDER BY ss.ss_url',
            'pagination' => [
                'pagesize' => 10,
            ],
        ]);
        return $dataProvider;
    }

       // выгребаем пока что все из таблицы типов CMS
    public function getdata_dc(){
        $posts = Yii::$app->db->createCommand('SELECT * FROM dir_cms order by dc_name')->queryAll();
        return ArrayHelper::map($posts,'dc_id','dc_name'); 
    }

    
    // принимает массив из POST и зжапихивает в базу
    public function add_sourcesite($val){
            Yii::$app->db->createCommand()->insert(
           'source_site',[
            'ss_url'      => $val['ss_url'],
            'ss_descript' => $val['ss_descript'],
            'ss_dc_id' => $val['ss_dc_id']
              ])->execute();
    }

}
