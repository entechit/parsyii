<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;

/*
  Базовый класс для парсинга сайтов. Что он умеет:
*/

class ParsModel extends Model
{
    
    public $ss_id;        // id задания из таблицы SourceSite

    public $current_page_body; // здесь сидит текст анализируемой страницы
    public $dc_id;             // код CMS 
    public $url_list_pointer;  // указатель на текущиую анализируемую страницу в source_page
    public $dp_id;          // id шаблона страницы, которым как мы считаем нужно парсить
    


    public $ss_descript;  //
    public $db;

    // Формируем переменную коннекта к базе данных
    function __construct(){
        $db = Yii::$app->db;
    }


    // основная управляющая функция
    // на входе анализирует ss_id - код сайта который парсим
    function main_pars_f(){

    }

    // держит курсор для source_page.sp_id
    // при вызове сдвигает на 1 позицию указатель $url_list_pointer
    function fetch_source_page(){

    }


    // загружает по ссылке страницу в переменную $current_page_body
    function get_page(){

    }

    // в переменной $current_page_body находит ссылки и запихивает уникальные в таблицу найденных ссылок
    function seek_urls(){

    }

    // подбирает наиболее подходящую схему для парсинга. Запихивает результат в source_page.sp_dp_id   И переменную $dp_id
    function choose_pattern(){

    }

    // основная функция парсинга, которая пытается вытянуть данные по всем известным ей шаблонам
    // и занести результаты в таблицу result_data
    function get_content(){

    }


}