<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;
//use yii\db\ActiveRecord;

/*
  Базовый класс для парсинга сайтов. Что он умеет:
*/

class ParsModel extends Model
{
    public $is_proxy;

    public $parslog; // текстовое представление результатов парсинга

    public $db;
    public $ss_id;        // id задания из таблицы SourceSite

    public $current_page_body; // здесь сидит текст анализируемой страницы

    public $current_page_xpath; // структура узлов???

    public $curr_sp_id;  // указатель на текущую анализируемую страницу в source_page. Если она = 0 разбор закончен

    public $sp_url; // адрес текущей страницы

    public $sp_dp_id; // тип текущей страницы

    PUblic $ri_img_path; // путь по которому сохранены картинки


    /* следующие переменные под вопросом */
    public $current_page_DOM; // здесь сидит DOM объект???

    public $ss_url;


    public $dc_id;             // код CMS 

    public $dp_id;          // id шаблона страницы, которым как мы считаем нужно парсить
    

    public $cb_find_internal_url; //искать внутренние ссылки на страницах
    public $rb_url_source; // где искать ссылки rb_seek_url_onsite / rb_seek_url_sitemap
    public $cb_type_source_page; // нужно ли типизировать страницы
    public $cb_pars_source_page; // выполнить парсинг
    public $cb_download_img; // скачать картинки

    public $ss_descript;  //
    

    // Формируем переменную коннекта к базе данных
    function __construct(){
       // $db = Yii::$app->db;
        $this->curr_sp_id = -1;
        $this->parslog = '';
        $this->ri_img_path = '../parsdata/';
        $this->is_proxy = true;
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
        $this->cb_download_img = $ss_params["cb_download_img"]; // скачать картинки
    

          $this->addlog("cb_pars_source_page=".$this->cb_pars_source_page);
          $this->addlog("cb_type_source_page=".$this->cb_type_source_page);
          $this->addlog("cb_find_internal_url=".$this->cb_find_internal_url);
          $this->addlog("rb_url_source=".$this->rb_url_source);
          $this->addlog("cb_download_img=".$this->cb_download_img);

        // если нужно чета делать кроме как загрузить уже найденные ссылки
        if (($this->cb_pars_source_page == 1) or 
            ($this->cb_type_source_page == 1) or 
              (($this->cb_find_internal_url == 1) and ($this->rb_url_source == 'rb_seek_url_onsite'))
            )
        {


          $this->addlog("Нам есть что считать");

            $this->fetch_source_page(); // сдвигаем указатель на страницу
            while  ($this->curr_sp_id!=0){

            $this->addlog("Итеррация: ". $this->sp_url);
            

                $this->get_page(); // вытягиваем страницу для анализа

                if ($this->cb_type_source_page == '1' and empty($this->sp_dp_id)) // если страницу нужно типизировать
                { 
                    $this->choose_pattern(); // пока не работает
                }

                if ($this->rb_url_source == 'rb_seek_url_onsite'){  // если нужно выгрести ссылки на текущей странице 
                    $this->seek_urls();  // пока не работает
                }

                if ($this->cb_pars_source_page == '1') // если таки нужно выдрать данные
                {
                    $this->get_content();
                }

                $this->fetch_source_page(); // сдвигаем указатель на страницу
            }
        }

        if ($this->cb_download_img == '1') // скачивать картинки
            {
                $this->addlog("Нужно скачать картинки");            
                $this->ri_img_path .= $this->ss_id;
                $this->get_img(); // скачиваем картинки РАБОТАЕТ!
            };
        $this->addlog("Анализ закончен");
    }



    // № 1. загружает по ссылке страницу в переменную $current_page_body и созданный DOM объект в current_page_DOM
    function get_page()
    {
        $this->current_page_body = $this->file_get_contents_proxy($this->sp_url); 
        $current_page_DOM = new \DOMDocument();
        @$current_page_DOM->loadHTML($current_page_body); 
        $this->current_page_xpath = new \DomXPath($current_page_DOM);
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
        
        if (!empty($row['sp_id'])){  // если есть следующая страница для разбора
            $this->curr_sp_id = $row['sp_id'];  // ставим указатель на текущую страницу
            $this->sp_url = $row['sp_url'];  // текущий URL
            $this->sp_dp_id = $row['sp_dp_id'];
            $this->addlog("fetch_source_page(): ".$this->sp_url);

        } else {  // разбор окончен, больше страниц нет
            $this->curr_sp_id = 0;
        };
    }



    // в переменной $current_page_body находит уникальные ссылки и запихивает  в таблицу найденных ссылок
    function seek_urls(){

    }

    /* Типизирует страницу 
    подбирает наиболее подходящую схему для парсинга. Запихивает результат в source_page.sp_dp_id И переменную $dp_id
    */
    function choose_pattern()
    {

        $expression = '/html/body/div[1]/div[2]/div[2]/div/div[1]/div[2]/form/div[2]/div[8]/b';
        $expression = sprintf('count(%s) > 0', $expression);
        if $this->current_page_xpath->evaluate($expression);
        
        return 
    }

    // основная функция парсинга, которая пытается вытянуть данные по всем известным ей шаблонам
    // и занести результаты в таблицу result_data
    function get_content(){
        $nodes = $this->current_page_xpath->query(".//*[contains(@class, 'img')]/img");
        foreach ($nodes as $i => $node) {
          //      $src = $node->nodeValue;
        } 
       // return $src;
    }

    //*************************************************************
    // вытягивает страницу в переменную current_page_body
    function file_get_contents_proxy($url)
    {
        set_time_limit(60); // ставим 60 сек на обработку каждой страницы

        $auth = base64_encode('sava:123'); 
        if ($this->is_proxy)
        {
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
                ),
                 "ssl"=>array(
                    "verify_peer"=>false,
                "verify_peer_name"=>false,
                ), 
            ); 
        } else {
            $opts = array( 
                 "ssl"=>array(
                    "verify_peer"=>false,
                    "verify_peer_name"=>false,
                ), 
            );
        }

        $this->addlog("Download Content file_get_contents_proxy(): ".$url);
        $ctx = stream_context_create($opts); 
            // возвращаем содержимое страницы с прокси параметрами или нет
        return file_get_contents($url,false,$ctx); 
    }


    //***********************  РАБОТАЕТ **************************************
    /* на вход принимает ss_id - задание. 
        переменная - base_path: то, где создаем папку с архивом картинк
        в архиве делаем папку с SS_ID - идентификатор задания
    К этому моменту в таблицу result_img уже сложены ссылки:
        1. Идем по циклу по всем картинкам
        2. Картинку скачиваем и сохраняем в папке base_path/ss_id
        3. Именуем картинку result_img.ri_id.jpg 
    */
    function get_img(){
        $counter_img = 0;
        $this->addlog("Вынимаем картинки");

        if( !is_dir( $this->ri_img_path)){ 
            $this->addlog("Создан новый каталог: ".$this->ri_img_path);
            mkdir( $this->ri_img_path, 0777, true );
        };

        $row = (new \yii\db\Query())
            ->select(['ri_id', 'ri_source_url', 'ri_ss_id'])
            ->from('result_img')
            ->where('ri_img_name is null and ri_ss_id = :ri_ss_id')
            ->addParams(['ri_ss_id'=>$this->ss_id]);
        

           

        // цикл по одной записи. Выполняет запрос при первой итеррации
        foreach ($row->each() as $img_row) {

            $file_name =$img_row['ri_id'].'.jpg';
            // сохраняем на диск
            $res = file_put_contents($this->ri_img_path.'/'.$file_name, $this->file_get_contents_proxy($img_row['ri_source_url']));

            // сохраняем результат в базу
            Yii::$app->db->createCommand()
                         ->update('result_img', 
                                ['ri_img_name' => $file_name,
                                'ri_img_path' => $this->ri_img_path, ], 
                                'ri_id = '.$img_row['ri_id']) 
                         ->execute();
            ++ $counter_img;
        }
        $this->addlog("Обработано (скачано) картинок: ".$counter_img);
    }



    //*************************************************
    // формирует запись итогов работы парсера
    function addlog($txt)
    {
        $this->parslog .= $txt.'<br>';
       // error_log($this->parslog);
    }

}


