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
    
    public $db;
    public $ss_id;        // id задания из таблицы SourceSite

    public $current_page_body; // здесь сидит текст анализируемой страницы

    public $current_page_xpath; // структура узлов???

    public $curr_sp_id;  // указатель на текущую анализируемую страницу в source_page. Если она = 0 разбор закончен

    public $sp_url; // адрес текущей страницы

    public $sp_dp_id; // тип текущей страницы


    /* следующие переменные под вопросом */
    public $current_page_DOM; // здесь сидит DOM объект???

    public $ss_url;


    public $dc_id;             // код CMS 

    public $dp_id;          // id шаблона страницы, которым как мы считаем нужно парсить
    

    public $cb_find_internal_url; //искать внутренние ссылки на страницах
    public $rb_url_source; // где искать ссылки rb_seek_url_onsite / rb_seek_url_sitemap
    public $cb_type_source_page; // нужно ли типизировать страницы
    public $cb_pars_source_page; // выполнить парсинг

    public $ss_descript;  //
    

    // Формируем переменную коннекта к базе данных
    function __construct(){
        $db = Yii::$app->db;
        $this->curr_sp_id = -1;
    }


    // основная управляющая функция
    // на входе анализирует ss_id - код сайта который парсим
    function main_pars_f($ss_params){
        $this->ss_id = $ss_params["ss_id"];
        $this->ss_url = $ss_params["ss_url"];  // имя базового url (с примечанием)

        $this->cb_find_internal_url = $ss_params["cb_find_internal_url"]; // парсинг страниц для поиска ссылок
            $this->rb_url_source = $ss_params["rb_url_source"];  // откуда брать источник для парсинга.
        
        $this->cb_type_source_page = $ss_params["cb_type_source_page"]; // необходимо типизировать страницы
        $this->cb_pars_source_page = $ss_params["cb_pars_source_page"]; // необходимо выдрать все известные теги
    
        // если нужно чета делать кроме как загрузить уже найденные ссылки
        if ($this->cb_pars_source_page == 1 or 
            $this->cb_type_source_page == 1 or (
            $this->cb_find_internal_url = 1 and $this->rb_url_source == 'rb_seek_url_onsite'))
        {

            while  ($this->curr_sp_id!=0){
                $this->fetch_source_page; // сдвигаем указатель на страницу
                $this->get_page(); // вытягиваем страницу для анализа

                if ($this->cb_type_source_page == 1 and empty($this->sp_dp_id)) // если страницу нужно типизировать
                { 
                    $this->choose_pattern(); // пока не работает
                }

                if ($this->rb_url_source == 'rb_seek_url_onsite'){  // если нужно выгрести ссылки на текущей странице 
                    $this->seek_urls();  // пока не работает
                }

                if ($this->cb_pars_source_page == 1) // если таки нужно выдрать данные
                {
                    $this->get_content();
                }
            }
        }
    }



    // № 1. загружает по ссылке страницу в переменную $current_page_body и созданный DOM объект в current_page_DOM
    function get_page()
    {
        $this->file_get_contents_proxy($this->sp_url);
        //$this->file_get_contents($this->sp_url);
        $current_page_DOM = new DomDocument();
        @$current_page_DOM->loadHTML($current_page_body); 
        $current_page_xpath = new DomXPath($current_page_DOM);
    }


    // При вызове выбирает следующую страницу source_page.sp_id
    function fetch_source_page()
    {
        $row = (new \yii\db\Query())
            ->select(['sp_id', 'sp_url', 'sp_dp_id'])
            ->from('source_page')
            ->where('sp_ss_id = :sp_ss_id and sp_id>:curr_sp_id and sp_parsed=0')
            ->addParams([':sp_ss_id' => $this->ss_id, 
                        ':curr_sp_id' =>  $this->curr_sp_id ])
            ->limit(1)
            ->orderBy(['sp_id' => SORT_ASC])
            ->one();

        if (!empty($row[sp_id])){  // если есть следующая страница для разбора
            $this->curr_sp_id = $row[sp_id];  // ставим указатель на текущую страницу
            $this->sp_url = $row[sp_url];  // текущий URL
            $this->sp_dp_id = $row[sp_dp_id];

        } else {  // разбор окончен, больше страниц нет
            $this->curr_sp_id = 0;
        };
    }



    // в переменной $current_page_body находит ссылки и запихивает уникальные в таблицу найденных ссылок
    function seek_urls(){

    }

    /* Типизирует страницу 
    подбирает наиболее подходящую схему для парсинга. Запихивает результат в source_page.sp_dp_id   И переменную $dp_id
    */
    function choose_pattern()
    {
        $expression = sprintf('count(%s) > 0', $expression);
        return $xpath->evaluate($expression);
    }

    // основная функция парсинга, которая пытается вытянуть данные по всем известным ей шаблонам
    // и занести результаты в таблицу result_data
    function get_content(){
        $nodes = $xpath->query(".//*[contains(@class, 'img')]/img");
        foreach ($nodes as $i => $node) {
                $src = $node->nodeValue;
        } 
        return $src;
    }

    // вытягивает страницу в переменную current_page_body
    function file_get_contents_proxy($proxy_url)
    {
        
        $auth = base64_encode('sava:123'); 

        $opts = array( 
            'http' => array ( 
                'method'=>'GET', 
                'proxy'=>'entecheco.com:3128', 
                'request_fulluri' => true, 
                'header'=> array("Proxy-Authorization: Basic $auth", "Authorization: Basic $auth") 

            ), 
            'https' => array ( 
                'method'=>'GET', 
                'proxy'=>'entecheco.com:3128', 
                'request_fulluri' => true, 
                'header'=> array("Proxy-Authorization: Basic $auth", "Authorization: Basic $auth") 
            ) 
        ); 
        $ctx = stream_context_create($opts); 
        $this->current_page_body = file_get_contents($url,false,$ctx); 
    }


}