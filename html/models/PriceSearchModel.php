<?php
namespace app\models;
use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
//************************************
//  Базовый класс для парсинга сайтов.
//************************************

class PriceSearchModel extends Model
{
//    public $is_proxy;

    // Формируем переменную коннекта к базе данных
    function __construct(){
     //   $this->sp_id = -1;
     
    }

    //*************************************************************
    // основная управляющая функция
    // на входе анализирует ss_id - код сайта который парсим
    //*************************************************************
    function main_price_search($prc_params)
    {
       
    }




}