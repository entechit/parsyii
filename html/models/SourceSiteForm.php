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
    public $ss_url;
    public $ss_dc_id;
    public $ss_descript;
    public $db;

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
            [['ss_url', 'ss_format'], 'required', 'message'=>'Поле URL не может быть пустым.'],
            // email has to be a valid email address
            ['ss_url', 'url'],

        ];
    }

    // выгребаем пока что все из таблицы источников сайта
    public function getdata_ss(){

        $dataProvider = new SqlDataProvider([
            'sql' => 'SELECT ss.*, dc.dc_name '.
                'FROM source_site ss Left join dir_cms dc on ss.ss_dc_id = dc.dc_id ' .
                'ORDER BY ss.ss_url',
            'pagination' => [
                'pagesize' => 1,
            ],
        ]);
       // $posts = Yii::$app->db->createCommand('SELECT * FROM source_site')->queryAll();
        return $dataProvider;
    }

       // выгребаем пока что все из таблицы типов CMS
    public function getdata_dc(){
        $posts = Yii::$app->db->createCommand('SELECT * FROM dir_cms order by dc_name')->queryAll();
        return ArrayHelper::map($posts,'dc_id','dc_name'); 
    }

}
