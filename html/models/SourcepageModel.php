<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;

/*
  Базовый класс для парсинга сайтов. Что он умеет:
*/

class SourcepageModel extends Model
{
    
    public $ss_id;    // id задания из таблицы SourceSite
    public $ss_url;    // id задания из таблицы SourceSite
    public $db;
    public $session;

    // Формируем переменную коннекта к базе данных
    function __construct(){
        $db = Yii::$app->db;
        $this->session = Yii::$app->session;
    }

    // основная управляющая функция
    // на входе анализирует ss_id - код сайта который парсим
    // выгребаем пока что все из таблицы источников сайта
    public function GetDataSP($sp_params){
        
        If (!empty($sp_params->ss_id)){
            $this->ss_id = $sp_params->ss_id;
        };

        $dataProvider = new SqlDataProvider([
         'sql' => 'SELECT sp.* '.
                'FROM source_page sp where sp.sp_ss_id = ' . $this->session->get('ss_id').
                ' ORDER BY sp.sp_url ',
            'pagination' => [
                'pagesize' => 20,
            ],
        ]);
        return $dataProvider;
    }
}