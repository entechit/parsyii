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

    public $sp_id;  // указатель на текущую анализируемую страницу в source_page. Если она = 0 разбор закончен

    public $sp_url; // адрес текущей страницы

    public $sp_dp_id; // тип текущей страницы

    PUblic $ri_img_path; // путь по которому сохранены картинки

    public $dc_id;             // код CMS 

    public $counter_dl_img; // количество скачаных картинок
    public $counter_dl_pages; // количество скачаных страниц
    public $counter_add_pages; // количество добавленных в набор страниц
    public $counter_type_pages; // количество типизированных страниц
    public $counter_steps;




    /* следующие переменные под вопросом */
  //  public $current_page_DOM; // здесь сидит DOM объект???

    public $ss_url;

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
        $this->sp_id = -1;
        $this->parslog = '';
        $this->ri_img_path = '../parsdata/';
        $this->is_proxy = true;

        $this->counter_dl_img = 0; // количество скачаных картинок
        $this->counter_dl_pages = 0; // количество скачаных страниц
        $this->counter_add_pages = 0; // количество добавленных в набор страниц
        $this->counter_type_pages = 0; // количество типизированных страниц
        $this->counter_steps = 0; // сделано шагов по справочнику страниц




    }


    // основная управляющая функция
    // на входе анализирует ss_id - код сайта который парсим
    function main_pars_f($ss_params){
        $this->ss_id = $ss_params["ss_id"];

        $row_ss = (new \yii\db\Query())->from('source_site')->where(['ss_id' => $this->ss_id])->one();
       
        $this->ss_url = $row_ss['ss_url'];  
        $this->dc_id = $row_ss['ss_dc_id'];  

        //$this->ss_url = $ss_params["ss_url"];  // имя базового url (с примечанием)

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
            while  ($this->sp_id!=0){

                try {
                    $this->get_page(); // вытягиваем страницу для анализа
                } catch ( yii\base\ErrorException $e) {
                    $this->mark_error_sp($e);
                    continue;
                };
                
                if ($this->cb_type_source_page == '1' and empty($this->sp_dp_id)) // нужно типизировать
                { 
                    $this->choose_pattern(); // требует тестирования
                }

                if (($this->cb_find_internal_url == 1) and ($this->rb_url_source == 'rb_seek_url_onsite'))
                {  // если нужно выгрести ссылки на текущей странице 
                    $this->seek_urls();  // пока не работает
                }

                if ($this->cb_pars_source_page == '1') // если таки нужно выдрать данные
                {
                    $this->get_content();
                }

                $this->fetch_source_page(); // сдвигаем указатель на страницу
            }
        }

        if ($this->cb_download_img == '1') // чекбокс - скачать картинки
        {
            $this->addlog("Нужно скачать картинки");
            $this->ri_img_path .= $this->ss_id;
            $this->get_img(); // скачиваем картинки РАБОТАЕТ!
        };
        // выводим на экран статистику
        $this->addlog("Выполнено шагов по страницам: ".$this->counter_steps); 

        $this->addlog("Скачано страниц: ".$this->counter_dl_pages); 
        $this->addlog("Типизировано страниц: ".$this->counter_type_pages); 
        $this->addlog("Добавлено новых URL в набор: ".$this->counter_add_pages); 
        $this->addlog("Скачано картинок:  ".$this->counter_dl_img); 

        $this->addlog("Анализ закончен");

    }


    // ************************************************
    // № 1. загружает по ссылке страницу в переменную $current_page_body и созданный DOM объект в current_page_DOM
    function get_page()
    {
        $this->current_page_body = $this->file_get_contents_proxy($this->sp_url); 

//file_put_contents($this->sp_id.'.txt', $this->current_page_body, FILE_APPEND);
//die;

        $current_page_DOM = new \DOMDocument();
        // нам не нужны проблемы с пробелами
        $current_page_DOM->preserveWhiteSpace = false;
        @$current_page_DOM->loadHTML($this->current_page_body); 
        $this->current_page_xpath = new \DomXPath($current_page_DOM);

        ++ $this->counter_dl_pages;
    }

    //*******************************************************
    //   ДЕЛАЕТ 1 ШАГ 
    // При вызове выбирает следующую страницу source_page.sp_id
    function fetch_source_page()
    {
        $row = (new \yii\db\Query())
            ->select(['sp_id', 'sp_url', 'sp_dp_id'])
            ->from('source_page')
            ->where('sp_ss_id = :sp_ss_id and sp_id>:sp_id and sp_parsed=0 and sp_error is null')
            ->addParams([':sp_ss_id' => $this->ss_id, 
                        ':sp_id' =>  $this->sp_id ])
            ->limit(1)
            ->orderBy(['sp_id' => SORT_ASC])
            ->one();
        
        if (!empty($row['sp_id'])){  // если есть следующая страница для разбора
            $this->sp_id = $row['sp_id'];  // ставим указатель на текущую страницу
            $this->sp_url = $row['sp_url'];  // текущий URL
            $this->sp_dp_id = $row['sp_dp_id'];

            ++ $this->counter_steps;

        } else {  // разбор окончен, больше страниц нет
            $this->sp_id = 0;
        };
    }


    //************************************************************************************************
    // в переменной $current_page_body находит уникальные ссылки и запихивает в таблицу найденных ссылок
    function seek_urls()
    {
        $nodes = $this->current_page_xpath->query('.//a/@href');
        foreach ($nodes as $node) 
        {
            $res_url = trim($node->nodeValue);
            // var_dump($node->nodeValue);
            // die;

            // массив расширений, на которые если заканчивается - нам не нужны
            $exts = array('.jpg','.png', '.JPG','.PNG'); 
            if (($res_url == '#') or (in_array(substr($res_url,-4,4), $exts)))
            {
                continue;
                //$this->addlog("seek_urls() найдена ссылка: ".$node->nodeValue);
            } elseif (substr($res_url,0,2) == '//'){
                    // оставляем как есть
            } elseif (substr($res_url,0,1) == '/') // дописываем домен
            {
                $res_url = $this->ss_url.$res_url;
            } elseif (substr($res_url,0,strlen($this->ss_url)) != $this->ss_url) // если внешняя ссылка
            {
                continue;
            };
          
            // если мы сюда дошли, значит есть ссылка для сохранения
            if (!empty($res_url))
            {
                try {
                    Yii::$app->db->createCommand()
                             ->insert('source_page', 
                                ["sp_ss_id" => $this->ss_id,
                                "sp_url" => $res_url,]) 
                             ->execute();

                    ++ $this->counter_add_pages;

                }  catch(\yii\db\Exception $e) {
                    //
                };
            };
        };
    }



    //*****************************************************************
    /* Типизирует страницу 
    подбирает наиболее подходящую схему для парсинга. Запихивает результат в source_page.sp_dp_id 
    И переменную $sp_dp_id

    запускает цикл по таблице pars_rule с условием pars_rule.pr_dt_id = 1 (детектор страницы) и
    dir_page_cms.dp_dc_id = ss_dc_id.
    Как только pars_rule.pr_selector присутсвует на странице - присваиваем странице source_page.sp_dp_id значение dir_page_cms.dp_id 
    */
    function choose_pattern()
    {
    //   $this->addlog("COME IN choose_pattern()");

      if (!empty($this->sp_dp_id)) return; // если уже есть определение страницы - выходим

    //  $this->addlog("EXECUTE choose_pattern()");

      // Цикл по типизаторам текущего CMS
      $row = (new \yii\db\Query())
            ->select(['pars_rule.pr_selector', 'dir_page_cms.dp_id'])
            ->from('pars_rule')
            ->join('LEFT JOIN', 'dir_page_cms', 'pars_rule.pr_dp_id = dir_page_cms.dp_id')
            ->where('dir_page_cms.dp_dc_id = :dp_dc_id and pars_rule.pr_dt_id = 1') // 1 - это id тега поля-типизатора
            ->addParams([':dp_dc_id' => $this->dc_id, ])
            ->all();


      foreach ($row as $pars_cond) 
      {
        
        if (!empty($pars_cond['pr_selector']))
        {
          
           $expression = sprintf('count(%s) > 0', $pars_cond['pr_selector']);
            

          if ($this->current_page_xpath->evaluate($expression)) // если нашли первое совпадение
          {
         //   $this->addlog("choose_pattern() НАШЛИ совпадения");
            // сохраняем результат в базу
            Yii::$app->db->createCommand()
                         ->update('source_page', 
                                ['sp_dp_id' => $pars_cond['dp_id'],], 
                                'sp_id = '.$this->sp_id) 
                         ->execute();
            ++ $this->counter_type_pages;
            $this->sp_dp_id = $pars_cond['dp_id'];
            break;
          };    
        };
      };
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

       // $this->addlog("Download Content file_get_contents_proxy(): ".$url);
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
      
        if( !is_dir( $this->ri_img_path)){ 
        //    $this->addlog("Создан новый каталог: ".$this->ri_img_path);
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
        
            ++ $this->counter_dl_img;
        }
    }



    //*************************************************
    // формирует запись итогов работы парсера
    function addlog($txt)
    {
        $this->parslog .= $txt.'<br>';
        //error_log($this->parslog);
    }

    // в таблице source_page помечает страницу как ошибочную, чтобы исключить дальнейшую обработку
    function mark_error_sp($e)
    {
        Yii::$app->db->createCommand()
                         ->update('source_page', 
                                ['sp_error' => Substr($e,0,250), ], 
                                'sp_id = '.$this->sp_id) 
                         ->execute();
    }


    //*********************************************************************
    // основная функция парсинга, которая пытается вытянуть данные по всем известным ей шаблонам
    // и занести результаты в таблицу result_data
    /*
    
    Выбираем ВСЕ pars_rule которые соответствуют dir_page_cms

    */
    //*********************************************************************
    function get_content(){
        $nodes = $this->current_page_xpath->query(".//*[contains(@class, 'img')]/img");
        foreach ($nodes as $i => $node) {
          //      $src = $node->nodeValue;
        } 
       // return $src;
    }


}