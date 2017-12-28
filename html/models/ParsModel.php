<?php
namespace app\models;
use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;

//************************************
//  Базовый класс для парсинга сайтов.
//************************************

class ParsModel extends Model
{

    public $is_proxy;
    public $parslog;      // текстовое представление результатов парсинга
  
    public $ss_id;        // id задания из таблицы SourceSite
    public $cust_id;        // id клиента
    public $ss_url;
    public $current_page_body;  // здесь сидит текст анализируемой страницы
    public $current_page_xpath; // структура узлов
    public $current_page_DOM; // 
    public $sp_id;        // указатель на текущую анализируемую страницу в source_page. Если она = 0 разбор закончен
    public $sp_url;       // адрес текущей страницы
    public $sp_dp_id;     // тип текущей страницы
    PUblic $ri_img_path;  // путь по которому сохранены картинки
    public $ri_src_path;
    public $dc_id;        // код CMS 
    public $HTTP_status;  // статус ответа загружаемой страницы
    public $sp_seek_urls;

    public $counter_dl_img;     // количество скачаных картинок
    public $counter_dl_pages;   // количество скачаных страниц
    public $counter_add_pages;  // количество добавленных в набор страниц
    public $counter_type_pages; // количество типизированных страниц
    public $counter_steps;

    public $cb_find_internal_url; //искать внутренние ссылки на страницах
    public $rb_url_source;        // где искать ссылки rb_seek_url_onsite / rb_seek_url_sitemap
    public $cb_type_source_page;  // нужно ли типизировать страницы
    public $cb_download_page;     // скачать в файл первую не типизированую страницу
    public $cb_pars_source_page;  // выполнить парсинг
    public $cb_download_img;      // скачать картинки
    public $cb_export_data;       // Выгрузить данные


    public $mode_get_node; // режим в котором работает функция get_node()  'result' / 'urls'

    public $pr_parentchild;  // признак того, что селектор описывает родительский (один на всю страницй) или дочерний набор
    public $parentchild_series; // счетчик № набора в квери 
    public $is_trace;
    public $trace_cats;  // категории трассировки

    // переменные работы с прайсом
    public $pager_page_n; // счетчик страниц в результате поиска
    public $searchmask; // маска поисковой строки
    public $price_id;  // текущее значение строки прайса

    public $counter_made_price_row; // обработано строк прайса 
    public $counter_add_price_pages; // найдено ссылок для строк прайса 
    public $url_per_price; // Количество вариантов сохраняемых карточек, если найдено несколько ссылок
    public $url_per_price_counter; // счетчик ссылок на одну и туже позицию прайса

    public $nodes_name_value; // массив в котором храним 3 значения: dt_id, dt_name, dt_value

    // выгрузка в CSV
    public $outputs_csv; // массив - основа для выгрузки в csv файл
    public $outputs_csv_pattern; // шаблон строки массива с заполненными дефолтами
    public $outputs_csv_index; // указатель на текущий формируемый элемент массива
    public $outputs_csv_nparam; // количество выгружаемых параметров
    public $outputs_csv_file; // имя ипуть результирующего файла
    public $exp_fields; // поля для экспорта
    public $export_start_id; // стартовый номер для нумерации
    public $export_ec_id; // куда смотрим для нумерации
    public $export_ec_datastruct; // Признак структуры результирующего файла s - одно данное - одна строка / m - все что угодно

    public $result_csv_path;
    public $ec_id;


//require_once Yii::app()->basePath . '/models/PriceSearchModel.php';

    // Формируем переменную коннекта к базе данных
    function __construct(){
        $this->sp_id = -1;
        $this->sp_seek_urls;
        $this->parslog = '';
        $this->ri_img_path     = '../parsdata/';
        $this->result_csv_path = '../parsdata/';
        $this->ri_src_path     = '../source_page/';
        $this->is_proxy = true;

        $this->is_trace = true;
        $this->trace_cats = array('marker','value','pre_func');  // pre_func memory - контроль памяти  value - контроль значений marker - показываем точку в программе


        $this->counter_dl_img = 0;      // количество скачаных картинок
        $this->counter_dl_pages = 0;    // количество скачаных страниц
        $this->counter_add_pages = 0;   // количество добавленных в набор страниц
        $this->counter_type_pages = 0;  // количество типизированных страниц
        $this->counter_steps = 0;       // сделано шагов по справочнику страниц

        $this->mode_get_node = '';

        $this->pr_parentchild = '';
        $this->parentchild_series = 0;
        $this->nodes_name_value =  array('dt_id'=>'','dt_name'=>'','rd_val'=>'', 'dt_rd_field'=>'');

    }

    //*************************************************************
    // основная управляющая функция
    // на входе анализирует ss_id - код сайта который парсим
    //*************************************************************
   // include "PriceSearchModel.php";

    function main_pars_f($ss_params)
    {

        $this->clear_trace();

        $this->ss_id = $ss_params["ss_id"];

        $row_ss = (new \yii\db\Query())->from('source_site')->where(['ss_id' => $this->ss_id])->one();
       
        $this->ss_url = $row_ss['ss_url'];  
        $this->dc_id  = $row_ss['ss_dc_id'];  
        $this->cust_id  = $row_ss['ss_cust_id'];  

        $this->cb_find_internal_url = $ss_params["cb_find_internal_url"]; // парсинг страниц для поиска ссылок
        $this->rb_url_source        = $ss_params["rb_url_source"];        // откуда брать источник для парсинга.   
        $this->cb_type_source_page  = $ss_params["cb_type_source_page"];  // необходимо типизировать страницы
        $this->cb_download_page     = $ss_params["cb_download_page"];     // скачать страницу
        $this->cb_pars_source_page  = $ss_params["cb_pars_source_page"];  // необходимо выдрать все известные теги
        $this->cb_download_img      = $ss_params["cb_download_img"];      // скачать картинки
        
        $this->cb_export_data      = $ss_params["cb_export_data"];      // экспортировать данные в формат заказчика
    
        /* 
          если загружаем по прайсу, то изначально формируем ссылки на страницы с условием поиска, 
          а только потом из них выгребаем ссылки на карточки, которые нужно будет слинковать с пунктами прайса
        */
        if (($this->cb_find_internal_url == 1) and ($this->rb_url_source == 'rb_seek_url_price')){
            // делаем в другом модуле чтобы не перегружать
$this->add_trace('PRICE !!!!!', 'marker', __FUNCTION__);
          $this->mode_get_node = 'urls';
          
          $this->price_main_f();

         
        }

        $this->mode_get_node = 'result';

        // если нужно чета делать кроме как загрузить уже найденные ссылки
        if (($this->cb_pars_source_page == 1) or 
            ($this->cb_type_source_page == 1) or 
              (($this->cb_find_internal_url == 1) and ($this->rb_url_source == 'rb_seek_url_onsite'))
            )
        {

            $this->addlog("Нам есть что считать");

            $this->fetch_source_page(); // сдвигаем указатель на страницу

            while  ($this->sp_id!=0) 
            { 
                if ($this->get_page() == 'continue'){
                    $this->fetch_source_page(); // сдвигаем указатель на страницу
                    continue; // вытягиваем страницу для анализа   
                } 
                
$this->add_trace('3. ID : '.$this->sp_id.'   URL : '.$this->sp_url, 'value', __FUNCTION__);
                
                if ($this->cb_type_source_page == '1' and empty($this->sp_dp_id)) 
                { 
//$this->add_trace('start choose_pattern() = '.memory_get_usage(), 'memory', __FUNCTION__);
                    $this->choose_pattern(); // типизирует
//$this->add_trace('end   choose_pattern() = '.memory_get_usage(), 'memory', __FUNCTION__);
                }
 
                if (($this->cb_find_internal_url == 1) and ($this->rb_url_source == 'rb_seek_url_onsite')
                    and $this->sp_seek_urls==0)
                {   
$this->add_trace('4. Seek_url  ID : '.$this->sp_id.'   URL : '.$this->sp_url, 'value', __FUNCTION__);

$this->add_trace('start seek_urls() = '.memory_get_usage(), 'memory', __FUNCTION__);
                    $this->seek_urls();  // гребет ссылки на текущей странице
$this->add_trace('end   seek_urls() = '.memory_get_usage(), 'memory', __FUNCTION__);
                }

                if ($this->cb_pars_source_page == '1') 
                {
                    set_time_limit(0); // ставим анлим на обработку каждой страницы
                    $this->get_content(); // вынимаем данные
                }

                $this->fetch_source_page(); // сдвигаем указатель на страницу
            }
        }

        if ($this->cb_download_page == '1')
        {
            $this->addlog("Нужно скачать страницу");
            $this->download_origin_page(); // скачивает HTML страницу в оригинале
        };

        if ($this->cb_download_img == '1') // чекбокс - скачать картинки
        {
            $this->addlog("Нужно скачать картинки");
            $this->ri_img_path .= $this->ss_id;
            $this->get_img(); // скачивает картинки
        };

$this->add_trace('EXPORT -1 this->cb_export_data = '.$this->cb_export_data, 'value', __FUNCTION__);            
        if ($this->cb_export_data == '1') { // выгрузка данных
$this->add_trace('EXPORT 0 ', 'marker', __FUNCTION__);            
            $this->export_main_f();
        }

        // выводим на экран статистику

        $this->addlog("Обработано строчек прайса: ".$this->counter_made_price_row); 
        $this->addlog("Найдено страниц описания к прайсу: ".$this->counter_add_price_pages); 
        
        $this->addlog("Выполнено шагов по страницам: ".$this->counter_steps); 
        $this->addlog("Скачано страниц: ".      $this->counter_dl_pages); 
        $this->addlog("Типизировано страниц: ". $this->counter_type_pages); 
        $this->addlog("Добавлено новых URL в набор: ".$this->counter_add_pages); 
        $this->addlog("Скачано картинок:  ".    $this->counter_dl_img); 

        $this->addlog("Анализ закончен");

    }

    //*******************************************************
    //   ДЕЛАЕТ 1 ШАГ 
    // При вызове выбирает следующую страницу source_page.sp_id
    function fetch_source_page()
    {
        $row = (new \yii\db\Query())
            ->select(['sp_id', 'sp_url', 'sp_dp_id', 'sp_seek_urls'])
            ->from('source_page')
            ->where('sp_ss_id = :sp_ss_id and sp_id>:sp_id and sp_parsed=0 and sp_errors is null')
            ->addParams([':sp_ss_id' => $this->ss_id, 
                        ':sp_id' =>  $this->sp_id ])
            ->limit(1)
            ->orderBy(['sp_id' => SORT_ASC])
            ->one();
        
        if (!empty($row['sp_id'])){  // если есть следующая страница для разбора
            $this->sp_id = $row['sp_id'];  // ставим указатель на текущую страницу
            $this->sp_url = $row['sp_url'];  // текущий URL
            $this->sp_dp_id = $row['sp_dp_id'];
            $this->sp_seek_urls = $row['sp_seek_urls'];


$this->add_trace('1. ID : '.$this->sp_id.'   URL : '.$this->sp_url, 'value', __FUNCTION__);

            ++ $this->counter_steps;

        } else {  // разбор окончен, больше страниц нет
            $this->sp_id = 0;
        };
    }


 // ************************************************
    // № 1. загружает по ссылке страницу в переменную $current_page_body и созданный DOM объект в current_page_DOM
    function get_page()
    {

    /* BEGIN блок условий, когда страницу качать не нужно*/

    // если нужно только найти ссылки, а с это йстраницы уже ссылки выьраны - страницу не качаем
    if ( ($this->cb_find_internal_url == 1)
        and ($this->rb_url_source == 'rb_seek_url_onsite')
        and ($this->sp_seek_urls==1)
        and ($this->cb_type_source_page == 0)
        and ($this->cb_download_page == 0)
        and ($this->cb_pars_source_page == 0) ) {
        return 'continue';
    }
    /* END блок условий, когда страницу качать не нужно*/


      $res = '';
      try {
        $this->current_page_body = $this->file_get_contents_proxy($this->sp_url); 
        $this->current_page_DOM = new \DOMDocument();
        $this->current_page_DOM->preserveWhiteSpace = false;
        @$this->current_page_DOM->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $this->current_page_body); 
        $this->current_page_xpath = new \DomXPath($this->current_page_DOM);

$this->add_trace('2. ID : '.$this->sp_id.' HTTP-Status : '. $this->HTTP_status .'   URL : '.$this->sp_url , 'value', __FUNCTION__);
        // если ответ в заголовке не 200 ОК значит страница с ошибкой  
       
        If (strpos($this->HTTP_status, '200') === false){
          $this->mark_error_sp($this->HTTP_status);
          $res = 'continue';

$this->add_trace('2.1 ID : '.$this->sp_id.' HTTP-Status : '. $this->HTTP_status .'   URL : '.$this->sp_url, 'value', __FUNCTION__ );


        } else {
          ++ $this->counter_dl_pages;  

$this->add_trace('2.2 ID : '.$this->sp_id.' HTTP-Status : '. $this->HTTP_status .'   URL : '.$this->sp_url, 'value', __FUNCTION__ );
        };

      } catch (yii\base\ErrorException $e) {
          $this->mark_error_sp($e);
          $res = 'continue';
$this->add_trace('2.3 ID : '.$this->sp_id.' HTTP-Status : '. $this->HTTP_status .'   URL : '.$this->sp_url, 'value', __FUNCTION__ );

      };
     
      return $res;
    }

    //*************************************************************************************
    // в текущей странице находит уникальные ссылки и запихивает в таблицу найденных ссылок
    //*************************************************************************************
    function seek_urls()
    {
$this->add_trace('0 SP_ID : '.$this->sp_id, 'marker', __FUNCTION__);

$this->add_trace('1 = '.memory_get_usage(), 'memory', __FUNCTION__);        

        $nodes = $this->current_page_xpath->query('//a/@href');

$this->add_trace('2  = '.memory_get_usage(), 'memory', __FUNCTION__);        

        
        foreach ($nodes as $node) 
        {
        
//$this->add_trace('seek_urls 1 ID : '.$this->sp_id.' NODE : '.$node->nodeValue );
$this->add_trace('3 = '.memory_get_usage(), 'memory', __FUNCTION__);

            $res_url = trim($node->nodeValue);
$this->add_trace('4 = '.memory_get_usage(), 'memory', __FUNCTION__);
            $res_arr = $this->adjust_URL($res_url); // дописываем домен
$this->add_trace('5 = '.memory_get_usage(), 'memory', __FUNCTION__);
            $res_url = $res_arr[0];
$this->add_trace('6 = '.memory_get_usage(), 'memory', __FUNCTION__);
//$this->add_trace('seek_urls 2 ID : '.$this->sp_id.' res_url : '.$res_url);          
            // если мы сюда дошли, значит есть ссылка для сохранения
            if (!empty($res_url))
            {
$this->add_trace('7 = '.memory_get_usage(), 'memory', __FUNCTION__);
                try {
                    Yii::$app->db->createCommand()
                             ->insert('source_page', 
                                ["sp_ss_id" => $this->ss_id,
                                "sp_url" => $res_url,]) 
                             ->execute();
$this->add_trace('8 = '.memory_get_usage(), 'memory', __FUNCTION__);
                    ++ $this->counter_add_pages;
$this->add_trace('9 = '.memory_get_usage(), 'memory', __FUNCTION__);                    

                }  catch(\yii\db\Exception $e) {
$this->add_trace('10 = '.memory_get_usage(), 'memory', __FUNCTION__);
                };
            };
        };

        /*$nodes=null;
        unset($nodes);
        $res_url = null;
        unset($res_url);*/
$this->add_trace('11 = '.memory_get_usage(), 'memory', __FUNCTION__);
        // помечаем, что из этой страницы уже выняты все ссылки
          Yii::$app->db->createCommand()
                         ->update('source_page', 
                                ['sp_seek_urls' => '1',], 
                                'sp_id = '.$this->sp_id) 
                         ->execute();

$this->add_trace('12 = '.memory_get_usage(), 'memory', __FUNCTION__);
    }

    //*****************************************************************
    /* Типизирует страницу 
    подбирает наиболее подходящую схему для парсинга. Запихивает результат в source_page.sp_dp_id 
    И переменную $sp_dp_id

    запускает цикл по таблице pars_rule с условием pars_rule.pr_dt_id = 1 (детектор страницы) и
    dir_page_cms.dp_dc_id = ss_dc_id.
    Как только pars_rule.pr_selector присутсвует на странице - присваиваем странице source_page.sp_dp_id значение dir_page_cms.dp_id 
    ***************************************************************/
    function choose_pattern()
    {
      $prev_dp_id = -1;
      $counter_cond = 0;
      $counter_vin = 0;

      if (!empty($this->sp_dp_id)) return; // если уже есть определение страницы - выходим



      // Цикл по типизаторам текущего CMS


      $row = Yii::$app->db->createCommand('SELECT pars_rule.pr_selector, pr_count_sub.pr_dp_id dp_id, pr_count_sub.pr_count, pars_rule.pr_id, pars_rule.pr_pre_function, pars_rule.pr_post_function
            from  
            (select count(pars_rule.pr_id) pr_count, pars_rule.pr_dp_id 
            from pars_rule 
            LEFT JOIN  dir_page_cms on pars_rule.pr_dp_id = dir_page_cms.dp_id
            where dir_page_cms.dp_dc_id = :dp_dc_id and pars_rule.pr_dt_id = 1
            group by pars_rule.pr_dp_id
            ) pr_count_sub
            LEFT JOIN  pars_rule on pars_rule.pr_dp_id = pr_count_sub.pr_dp_id
            where pars_rule.pr_dt_id = 1
            order by pr_count_sub.pr_count desc, pr_count_sub.pr_dp_id desc')
           ->bindValue(':dp_dc_id',$this->dc_id)
           ->queryAll();


      foreach ($row as $pars_cond) 
      {
       

        if (empty($pars_cond['pr_selector'])) continue;
                

        if (($prev_dp_id != $pars_cond['dp_id']) 
            and ($counter_vin == $counter_cond) 
            and  ($prev_dp_id != -1))
        {
            break;
        }

        if ($prev_dp_id != $pars_cond['dp_id'])   
        {
            $counter_cond = 1;
            $counter_vin = 0;
        }

        if ($prev_dp_id == $pars_cond['dp_id'])   ++ $counter_cond;


        // вызываем функцию предобработки
        if (!empty($pars_cond['pr_pre_function'])) {
            $pre_func = $pars_cond['pr_pre_function'];
             if ( method_exists($this,$pre_func))
                     $this->$pre_func();
        }

                  
        $expression = sprintf('count(%s) > 0', $pars_cond['pr_selector']);
        if ($this->current_page_xpath->evaluate($expression)) // если есть хоть одно совпадение
        { 

/*if ($pars_cond['dp_id']==27){
$this->add_trace('choose_pattern dp_id = 27 pr_id =  '.$pars_cond['pr_id'],'value', __FUNCTION__);    
};  
if ($pars_cond['dp_id']==26){
$this->add_trace('choose_pattern dp_id = 26  pr_id =  '.$pars_cond['pr_id'],'value', __FUNCTION__);    
};*/          

        // вызываем функцию ПОСТобработки
            if (!empty($pars_cond['pr_post_function'])){
                $post_func = $pars_cond['pr_post_function'];
                if ( method_exists($this,$post_func))
                     $this->$post_func();
            }


            ++ $counter_vin;            
        };    
        
        $prev_dp_id = $pars_cond['dp_id'];
    };


    if ($counter_vin == $counter_cond)
    {

            // сохраняем результат в базу
        Yii::$app->db->createCommand()
                     ->update('source_page', 
                             ['sp_dp_id' => $prev_dp_id,], 
                              'sp_id = '.$this->sp_id) 
                     ->execute();
        ++ $this->counter_type_pages;
          
            $this->sp_dp_id = $prev_dp_id;
        }
    }


    //*************************************************************
    // вытягивает страницу в переменную current_page_body
    function file_get_contents_proxy($url)
    {
        $this->HTTP_status = '';
        $auth = base64_encode('sava:123'); 
        if ($this->is_proxy)
        {
            $opts = array( 
                'http' => array ( 
                    'method'=>'GET', 
                    'proxy'=>'proxy.home.entecheco.com:3128', 
                    'request_fulluri' => true, 
                    'header'=> array("Proxy-Authorization: Basic $auth", "Authorization: Basic $auth") 
                ), 
                'https' => array ( 
                    'method'=>'GET', 
                    'proxy'=>'proxy.home.entecheco.com:3128', 
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

        $ctx = stream_context_create($opts); 

       
        ini_set('max_execution_time', 0);
            // возвращаем содержимое страницы с прокси параметрами или нет
        $res = file_get_contents($url,false,$ctx); 
        $this->HTTP_status = $http_response_header[0];
 
        return $res;
    }


    //***********************  РАБОТАЕТ **************************************
    /* на вход принимает ss_id - задание. 
        переменная - ri_img_path + SS_ID: то, где создаем папку с архивом картинок
    К этому моменту в таблицу result_img уже сложены ссылки:
        1. Идем по циклу по всем картинкам
        2. Картинку скачиваем и сохраняем в папке base_path/ss_id
        3. Именуем картинку result_img.ri_id.jpg 
    */
    function get_img()
    {
        if( !is_dir( $this->ri_img_path)){ 
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

            try {
                $res = file_put_contents($this->ri_img_path.'/'.$file_name, $this->file_get_contents_proxy($img_row['ri_source_url']));
            } catch ( yii\base\ErrorException $e) {
                $this->mark_error_ri($e, $img_row['ri_id']);
                continue;
            };

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

    //***************************************
    // формирует запись итогов работы парсера
    //***************************************
    function addlog($txt)
    {
        $this->parslog .= $txt.'<br>';
        //error_log($this->parslog);
    }

    //******************************************************
    // в таблице source_page помечает страницу как ошибочную
    //******************************************************
    function mark_error_sp($e)
    {

        Yii::$app->db->createCommand()
                         ->update('source_page', 
                                ['sp_errors' => Substr($e,0,395), ], 
                                'sp_id = '.$this->sp_id) 
                         ->execute();
    }

    //*****************************************************
    // в таблице result_img помечает страницу как ошибочную
    //*****************************************************
    function mark_error_ri($e, $ri_id)
    {
        Yii::$app->db->createCommand()
                     ->update('result_img', 
                             ['ri_error' => Substr($e,0,250), ], 
                              'ri_id = '.$ri_id) 
                     ->execute();
    }

    //*********************************************************************
    // основная функция парсинга, тянет данные по всем известным шаблонам
    // и занести результаты в таблицу result_data
    // Выбираем ВСЕ pars_rule которые соответствуют dir_page_cms
    //*********************************************************************
    function get_content()
    {
      // цикл по корневым правилам
      $rules_rows_parent = (new \yii\db\Query())
            ->select(['pars_rule.*', 'dir_tags.dt_rd_field', 'dir_tags.dt_is_img',])
            ->from('pars_rule')
            ->join('LEFT JOIN', 'dir_tags', 'pars_rule.pr_dt_id = dir_tags.dt_id')
            ->where('pars_rule.pr_dp_id = :pr_dp_id and (pars_rule.pr_id_parent is null or pars_rule.pr_id_parent="") and pars_rule.pr_dt_id <> 1')
            ->addParams([':pr_dp_id'=>$this->sp_dp_id]);

      foreach ($rules_rows_parent->each() as $rules_row_parent) 
      {    

        // вызываем функцию предобработки
        if (!empty($rules_row_parent['pr_pre_function'])) {
            $pre_func = $rules_row_parent['pr_pre_function'];
            if ( method_exists($this,$pre_func))
                     $this->$pre_func();
        }


$this->add_trace('1 this->sp_url = '.$this->sp_url,'marker', __FUNCTION__);
        $this->pr_parentchild = $rules_row_parent['pr_parentchild'];

        if ($rules_row_parent['pr_nodetype']=='q') // набор элементов
        {
$this->add_trace('2 Query ','marker', __FUNCTION__);
          $this->get_query($this->current_page_xpath, $rules_row_parent);
        } 
        elseif($rules_row_parent['pr_nodetype']=='n') // одиночный элемент
        { 
$this->add_trace('3 Node ','marker', __FUNCTION__);                        
          $this->get_node($this->current_page_xpath, $rules_row_parent );
        }
      };

        // если мы в режиме выемки данных, то помечаем страницу как просканированную
        if ($this->mode_get_node == 'result'){
              Yii::$app->db->createCommand()
                         ->update('source_page', 
                                ['sp_parsed' => '1',], 
                                'sp_id = '.$this->sp_id) 
                         ->execute();
        };
    }

    //**********************************************
    // вынимает набор данных
    function get_query($node, $selector, $context = NULL)
    {

        $this->nodes_name_value =  array('dt_id'=>'','dt_name'=>'','rd_val'=>'');
        
        if ($context !== NULL) {
          if ($this->pr_parentchild == 'c') ++ $this->parentchild_series;
          $res_nodes = $node->query($selector['pr_selector'], $context);  
        }else {
          $this->parentchild_series = 0;
          $res_nodes = $node->query($selector['pr_selector']);
        };


        If ($res_nodes === false) { 
$this->add_trace("0 get_query Xpath Ошибка построения query",'marker', __FUNCTION__);      
            return; 
        }

 
        foreach ($res_nodes as $res_node) // идем внутри полученного набора элементов
        {
            $rules_rows_sub = (new \yii\db\Query())
                  ->select(['pars_rule.*', 'dir_tags.dt_rd_field', 'dir_tags.dt_is_img',])
                  ->from('pars_rule')
                  ->join('LEFT JOIN', 'dir_tags', 'pars_rule.pr_dt_id = dir_tags.dt_id')
                  ->where('pars_rule.pr_dp_id = :pr_dp_id and pars_rule.pr_id_parent = :pr_id and (pars_rule.pr_dt_id <> 1 or pars_rule.pr_dt_id is null)')
                  ->addParams([':pr_dp_id' => $this->sp_dp_id,
                               ':pr_id'    => $selector['pr_id'],]);


$this->add_trace('1 Query selector[pr_selector]'.$selector['pr_selector'],'value', __FUNCTION__);

            if (($this->pr_parentchild == 'c') and ($context == NULL)) {
              ++ $this->parentchild_series;  
            }  

            foreach ($rules_rows_sub->each() as $rules_row_sub) 
            {    
                 // всегда при следующем шаге внутри квери очищаем массив пары имя-значение
              

                if ($rules_row_sub['pr_nodetype']=='q')
                {

                  $this->get_query($this->current_page_xpath, $rules_row_sub,  $res_node );    
               
                } elseif ($rules_row_sub['pr_nodetype']=='n') 
                {
$this->add_trace(" 2 N Xpath =".$rules_row_sub['pr_selector']."PR_ID = ".$rules_row_sub['pr_id'],'marker', __FUNCTION__);
                    $this->get_node($this->current_page_xpath, $rules_row_sub, $res_node );
                }

                         // вызываем функцию ПОСТобработки
                if (!empty($rules_row_sub['pr_post_function'])){
                     $post_func = $rules_row_sub['pr_post_function'];
                    if ( method_exists($this,$post_func))
                     $this->$post_func();
                }
            }
        }
    }

    //**********************************************
    // вынимает конкретные данные и пишет в базу
    function get_node($node, $selector, $context = Null)
    {
        // var_dump($context); 
        // var_dump($this->parslog); 
        // var_dump($node);die;

//var_dump($this->parslog);die;

        // инциализация для получения параметров img
        $alt = '';
        $title = '';
$this->add_trace("1 N Xpath =".$selector['pr_selector'],'value', __FUNCTION__);
        if ($context !== NULL) {
          $res_nodes = $node->query($selector['pr_selector'], $context);   
        }else {
          $res_nodes = $node->query($selector['pr_selector']); 
        };

        if ( $res_nodes->length == 0) return;
        
        
        $res_node = $res_nodes->Item(0); 


$this->add_trace("2 N Xpath =".$selector['pr_selector'],'value', __FUNCTION__);

            if ($selector['dt_is_img']==1){
                $res_srcS = $node->query('./@src', $res_node);  
                $res_altS = $node->query('./@alt', $res_node);  
                $res_titleS = $node->query('./@title', $res_node);  

                $res_hrefS = $node->query('./@href', $res_node);  


                if ( $res_srcS->length > 0) $val = $res_srcS->Item(0)->nodeValue;

                if ( $res_hrefS->length > 0) $val = $res_hrefS->Item(0)->nodeValue;

                if ( $res_altS->length > 0)  $alt = $res_altS->Item(0)->nodeValue;

                if ( $res_titleS->length > 0)  $title = $res_titleS->Item(0)->nodeValue;

$this->add_trace("2.1. IMG N res_srcS =".$res_srcS->Item(0)->nodeValue,'value', __FUNCTION__);
          
//$this->add_trace('Get Alt and Title: this->sp_id: '.$this->sp_id.' content : '.$full);   
            } else {
                $node = $res_node;

                If ($selector['pr_html']=='1'){
                  $val = $this->getDomElementInnerHtml($res_node);
                } else {
            /*      if($selector['pr_name_value'] == 'n') {
                        $child = $res_node->getElementsByTagName('div')->item(0);
                        if ($child) {
                          $res_node->removeChild($child);
                        }
                    }*/
                    $val = trim($res_node->nodeValue);
                }
                   

$this->add_trace("3 val =".$val  . 'MODE_GET_NODE = '.$this->mode_get_node,'value', __FUNCTION__);
            };

        // если это картинка, то вынимаем параметры alt и title

        if ($selector['dt_is_img']){
          $res_arr = $this->adjust_URL($val); // дописываем домен
          $val = $res_arr[0];  
        };
        

        if (!empty($val)){
$this->add_trace("4 val =".$val,'value', __FUNCTION__);
          if ($this->mode_get_node == 'result'){
$this->add_trace("4.1 val =".$val  . 'MODE_GET_NODE = '.$this->mode_get_node,'value', __FUNCTION__);

            // "обработка пары имя параметра - значение"
            if (!empty($selector['pr_name_value'])){

$this->add_trace("4.1.0 pr_name_value =".$selector['pr_name_value'],'value', __FUNCTION__);

                if($selector['pr_name_value'] == 'n'){
$this->add_trace("4.1.0.1 pr_name_value =".$selector['pr_name_value'],'value', __FUNCTION__);

                     $this->nodes_name_value['dt_name'] = $val;
                     $this->nodes_name_value['dt_id'] = $this->name_value_subst($val);

$this->add_trace("4.1.0.2 nodes_name_value[dt_id] =".$this->nodes_name_value['dt_id'],'value', __FUNCTION__);

                } elseif ($selector['pr_name_value'] == 'v') {
$this->add_trace("4.1.0.3 pr_name_value =".$selector['pr_name_value'],'value', __FUNCTION__);
                     $this->nodes_name_value['rd_val'] = $val;
                };

                if ((!empty($this->nodes_name_value['dt_id']  )) and 
                    (!empty($this->nodes_name_value['dt_name'])) and 
                    (!empty($this->nodes_name_value['rd_val'] ))) {
                        
                        $selector['pr_dt_id'] = $this->nodes_name_value['dt_id'];
                        $val = $this->nodes_name_value['rd_val'];
$this->add_trace("4.1.0.4 NO EXIT =".$this->nodes_name_value['dt_id']."---".$this->nodes_name_value['dt_name']. "---" . $this->nodes_name_value['rd_val'],'value', __FUNCTION__);

                        $selector['dt_rd_field'] = $this->nodes_name_value['dt_rd_field'];

                    } else {
$this->add_trace("4.1.0.5 EXIT =".$this->nodes_name_value['dt_id']."---".$this->nodes_name_value['dt_name']. "---" . $this->nodes_name_value['rd_val'],'value', __FUNCTION__);
                        return;
                    };
            }
            
$this->add_trace("4.1.1 dt_id =".$selector['pr_dt_id'],'value', __FUNCTION__);
              Yii::$app->db->createCommand()
                     ->insert('result_data', 
                             ["rd_ss_id" => $this->ss_id,
                             "rd_sp_id" =>  $this->sp_id,
                             "rd_dt_id" => $selector['pr_dt_id'],
                             $selector['dt_rd_field'] => $val,
                             "rd_parentchild_seria"=> ($this->pr_parentchild == 'p'?'0':$this->parentchild_series),
                             ]) 
                    ->execute();

            if ($selector['dt_is_img']==1) {
                  Yii::$app->db->createCommand()
                     ->insert('result_img', 
                             ["ri_ss_id" => $this->ss_id,
                             "ri_rd_id" =>  Yii::$app->db->getLastInsertID(),
                             "ri_alt" =>  substr($alt,0,200),
                             "ri_title" =>  substr($title,0,200),
                             "ri_source_url" => $val,]) 
                    ->execute();
            };



            $this->nodes_name_value =  array('dt_id'=>'','dt_name'=>'','rd_val'=>'');

          } elseif ($this->mode_get_node == 'urls') {  // записываем ссылку к прайсу
$this->add_trace("4.2 val =".$val  . 'MODE_GET_NODE = '.$this->mode_get_node,'value', __FUNCTION__);
            $this->insert_price_ulr_list($val);
          };
        };
    }
    //*********************************
    /*
      Кооректирует URL ссылку дописывая если не хватает base_url
      page_type  html / img / other
    */
    function adjust_URL($source_url) 
    {
      
      $res_url = trim($source_url);
      $page_type = 'html';

$this->add_trace('1 ID : '.$this->sp_id.' res_url : '.$res_url,'value', __FUNCTION__);

      // 1. Определяем тип страницы 
      $exts = array('.jpg','.png', '.gif', 'webp', '.svg'); 
      if (in_array(strtolower(substr($res_url,-4,4)), $exts))
      {
        $page_type = 'img';
$this->add_trace('2.1 ID : '.$this->sp_id.' res_url : '.$res_url,'value', __FUNCTION__);
      } 
      elseif (( substr($res_url,0,1) == '#') or (empty($res_url)))
      {
$this->add_trace('2.2 ID : '.$this->sp_id.' res_url : '.$res_url,'value', __FUNCTION__);
        $res_url = ''; 
        $page_type = 'other';
      };


      // 2. Нужно ли дописывать домен
      if (substr($res_url,0,2) == '//')
      {
$this->add_trace('3.1 ID : '.$this->sp_id.' res_url : '.$res_url,'value', __FUNCTION__);
        // $res_url = $source_url;

      } elseif (substr($res_url,0,1) == '/') // дописываем домен
      {
        $res_url = $this->ss_url.$res_url;

$this->add_trace('3.2 ID : '.$this->sp_id.' res_url : '.$res_url,'value', __FUNCTION__); 

      } elseif (substr($res_url,0,4) != 'http') // начинается с непонятно чего даже без слеша
      {
        $res_url = $this->ss_url.'/'.$res_url;
$this->add_trace('3.2 ID : '.$this->sp_id.' res_url : '.$res_url,'value', __FUNCTION__);
      }
      elseif ((substr($res_url,0,strlen($this->ss_url)) != $this->ss_url) and ($page_type!='img')) // если внешняя ссылка
      {
        $res_url = ''; 
        $page_type = 'other';
      };
          
      return array($res_url, $page_type);
    }

    /***********************************************
      скчачивает первую нетипизированную страницу
    */
    function download_origin_page() {

       $rows = (new \yii\db\Query())
            ->select(['sp_id', 'sp_url', 'sp_dp_id'])
            ->from('source_page')
            ->where('sp_ss_id = :sp_ss_id and sp_id>0 and sp_dp_id is null and sp_errors is null')
            ->addParams([':sp_ss_id' => $this->ss_id])
            ->orderBy(['sp_id' => SORT_ASC])
            ->all();
      
      foreach ($rows as $row) {

        $this->sp_url = $row['sp_url'];
        $this->sp_id = $row['sp_id'];
        $res = $this->get_page();

        if ($res != 'continue') {
          file_put_contents($this->ri_src_path.$row['sp_id'].'.html', $this->current_page_body, FILE_APPEND);
          $this->addlog(" На диск сохранен файл:  ".$row['sp_id'].'.html  ссылка: '. $this->sp_url );    
          break;
        };

      };  
    }

/*****************************************************/
// очистка трейсау t_trace
    function clear_trace(){
        If (!$this->is_trace) return;
        Yii::$app->db->createCommand('Delete from t_trace')
            ->execute();
     }

    // пишет сообщение в трейс таблицу t_trace
    function add_trace($trace_text, $cat = null, $func = null){


        If (!$this->is_trace) return;

        // если не соотвествует категория трейса или не пусто
        if (!((in_array($cat, $this->trace_cats)) or empty($cat))) return;

        Yii::$app->db->createCommand()
            ->insert('t_trace', 
                     ["trace_comment" => addslashes(Substr($trace_text,0,400)),
                     "trace_group" => $cat,
                     "trace_func" => $func,
                 ]) 
            ->execute();
     }


     /****************************************************/
     // Получает на вход объект DOM, возвращает текст HTML
    function getDomElementInnerHtml_($element) { 

        $newdoc = new \DOMDocument('1.0', 'UTF-8');
        $cloned = $element->cloneNode(TRUE);
        $newdoc->appendChild($newdoc->importNode($cloned,TRUE));
        return $newdoc->saveHTML();

    }

    function getDomElementInnerHtml($element) { 
            $innerHTML = ""; 
            $children  = $element->childNodes;

            foreach ($children as $child) { 
                $innerHTML .= $element->ownerDocument->saveHTML($child);
            }

            return $innerHTML; 
    }

/************************************************************************/
// **********************  PRICE  ***************************************/
/************************************************************************/
/*
          // 1. Крутим цикл по прайсу

          // 1.1. На каждой позиции прайса строим страницу поиска и запихиваем ее в Source_page

          // 1.2. в результате поиска крутим цикл по страницам (пейджер)
          // 1.2.1. Выбираем нулевую страницу
          // 1.2.2. Выгребаем через настроенный селектор CMS ссылки на карточки
          // 1.2.3. Добавлем ссылки в Source_page
          // 1.2.4. Добавляем связку в таблицу link_price_source_page
          // 1.2.5.           
          // дальше наш обычный парсинг

        Шаги для описания страницы поиска каталога:
        1. Каталог должен быть описан в dir_cms. Описать блок if в price_settings()
        2. Должна быть заведена страница поиска в dir_page_cms
        3. Страницу поиска описать  price_settings() в IF  переменную $this->sp_dp_id
        4. В parse_rule описать 2 записи селектора:
           - квери для получения набора ссылок: pr_dp_id = $this->sp_dp_id
                                                 pr_dt_id = -1
                                                 pr_nodetype = 'q'

           - узел для получения значения ссылки: pr_dp_id = $this->sp_dp_id
                                                 pr_dt_id = -2
                                                 pr_nodetype = 'n'
        5. В блок if в price_settings() настроить маску для URL строки поиска  $this->searchmask
        6. Присвоить переменной $this->pager_page_n значение первой страницы результатов поиска
*/

    // установка настроечных параметров на каталоги
    function price_settings(){

        $this->counter_made_price_row = 0; // обработано строк прайса 
        $this->counter_add_price_pages = 0; // найдено ссылок для строк прайса 
        $this->url_per_price_counter = 0; // инит счетчика ссылок на 1 позицию
        $res = false;


        if ($this->dc_id == 142) //'http://amazon.co.jp
        {
            $this->searchmask = "https://www.amazon.co.jp/s/field-keywords=%s&p=%s";
            $this->sp_dp_id = 52; // страница с результатами поиска на Hotline
            $this->pager_page_n = 0;
            $res = true;
            $this->url_per_price = 1; // Количество вариантов сохраняемых карточек, если найдено несколько ссылок
        };


        if ($this->dc_id == 71) //'http://hotline.ua'
        {
            $this->searchmask = "http://hotline.ua/sr/?q=%s&p=%s";
            $this->sp_dp_id = 16; // страница с результатами поиска на Hotline
            $this->pager_page_n = 0;
            $res = true;
            $this->url_per_price = 2; // Количество вариантов сохраняемых карточек, если найдено несколько ссылок
        };

        return $res;
    }

    // основная
    function price_main_f(){
        // выбираем прайс заказчика, позиции у которых нет привязанных ссылок в линковочной таблице
        // Mode - пусто - имя + артикул
        // articul - только по артикулу


        if (!$this->price_settings()) return; // если каталог не описан

        // выбираем строки прайса заказчика, у которых еще нет связанных описаний
        $price_rows = (new \yii\db\Query())
            ->select(['sp.price_id','sp.price_cust_id', 'sp.price_maufacturer', 'sp.price_cat', 'sp.price_subcat', 'sp.price_modelname', 'sp.price_modelcode'])
            ->from('source_price sp')
            ->join('LEFT JOIN', 'link_price_source_page lpsp', 'lpsp.lpsp_price_id = sp.price_id')
            ->where('sp.price_cust_id = :price_cust_id and lpsp.lpsp_id is null')
            ->addParams([':price_cust_id' => $this->cust_id,])
            ->orderBy(['price_id' => SORT_ASC])
            ->all();

        // цикл по прайсу
        foreach ($price_rows as $price_row) {
            $this->url_per_price_counter = 0; // Количество вариантов сохраняемых карточек, если найдено несколько ссылок


$this->add_trace('1 Cust_id : '.$this->cust_id.'   Наименование : '.$price_row['price_modelname'],'value', __FUNCTION__);
            $this->price_id = $price_row['price_id'];

           // формируем ссылку поиска
            if (empty($price_row['price_modelcode'])){
                $search_string = $price_row['price_modelname'];
            } else {
                $search_string = $price_row['price_modelcode'];
            };

$this->add_trace('2 search_string = '.$search_string,'value', __FUNCTION__);
            // формируем строку поиска по маске конкретного каталога 
            $search_url=sprintf($this->searchmask, $this->space2plus($search_string), $this->pager_page_n);
$this->add_trace('3 search_url = '.$search_url,'value', __FUNCTION__);
            // инициализируем переменную ссылкой
            $this->sp_url = $search_url;
$this->add_trace('4 insert_price_ulr_list this->sp_url = '.$this->sp_url,'value', __FUNCTION__);
            // выгребаем страницу
            $this->get_page();
$this->add_trace('5 insert_price_ulr_list this->sp_url = '.$this->sp_url,'value', __FUNCTION__);
            // выгрбаем через селекторы все ссылки 
            $this->get_content();
$this->add_trace('7 insert_price_ulr_list this->sp_url = '.$this->sp_url,'value', __FUNCTION__);
            ++ $this->counter_made_price_row;
        };
    }


    //  втыкаем ссылки в линковочную таблицу и таблицу ссылок
    function insert_price_ulr_list($val){

        ++ $this->url_per_price_counter;

$this->add_trace('6 insert_price_ulr_list VAL = '.$val,'value', __FUNCTION__);
        $res_url = $this->adjust_URL($val);
$this->add_trace('6.1 insert_price_ulr_list res_url = '.$res_url[0],'value', __FUNCTION__);
        if (!empty($res_url[0]) and ($this->url_per_price_counter<=$this->url_per_price))
        {
            try {
                Yii::$app->db->createCommand()
                         ->insert('source_page', 
                                 ["sp_ss_id" => $this->ss_id,
                                "sp_url" => $res_url[0],]) 
                         ->execute();


                Yii::$app->db->createCommand()
                     ->insert('link_price_source_page', 
                             ["lpsp_price_id" => $this->price_id,
                              "lpsp_sp_id" =>  Yii::$app->db->getLastInsertID(),]) 
                    ->execute();

                    ++ $this->counter_add_price_pages;

                }  catch(\yii\db\Exception $e) {
                    //
                };
        };
    }


    // формирует условие поиска, заменяя пробелы плюсами 
    function space2plus($incstr){
        $res = str_replace('  ', ' ', $incstr);
        $res = str_replace(' ', '+', $res);
        return $res;
    }

/*************************************************************************/
/************ ФУНКЦИЯ ПОДМЕНЫ пары параметр - значение Тегами ************/
// Принимаем на вход имя параметра, на выходе отдаем его ID в таблице dir_tags
/*************************************************************************/

    function name_value_subst($parname){
        
        $res = null;
        $this->nodes_name_value['dt_rd_field']= 'rd_short_data';
        // заменяем двоеточие на пробел
        $parname = str_ireplace(':', ' ', $parname);
        $parname = trim(str_ireplace('  ', ' ', $parname));

        $dt_vals = (new \yii\db\Query())
            ->select(['dt_id', 'dt_rd_field',])
            ->from('dir_tags')
            ->where('LOWER(trim(dt_name)) = :dt_name')
            ->addParams([':dt_name' => mb_strtolower($parname,'utf8'),])
            ->one();

$this->add_trace('Tab_analyse 1  LOW parname = '.mb_strtolower($parname,'utf8'),'value', __FUNCTION__);
$this->add_trace('Tab_analyse 2  parname = '.$parname,'value', __FUNCTION__);

        if (!empty($dt_vals['dt_id'])){
            //var_dump($dt_vals);die;
            // возвращаем 
            $res = $dt_vals['dt_id'];
            $this->nodes_name_value['dt_rd_field'] = $dt_vals['dt_rd_field'];
        } else {
            // нужно добавлять и возвращать новый dt_id
            Yii::$app->db->createCommand()
                     ->insert('dir_tags', 
                             ["dt_name" => $parname,
                             "dt_rd_field" => $this->nodes_name_value['dt_rd_field'],]) 
                    ->execute();
            $res = Yii::$app->db->getLastInsertID();
        };

        return $res;
    }


    /********************************************************************/
    /********************   Экспорт в CSV   *****************************/
    /********************************************************************/
    /*
      Получаем выборку страниц для выгрузки
      Запускаем цикл для формирования заголовка CSV (distinct) в массив this->outputs_csv = array();
      Запускаем такой же цикл для формирования строки данных - записываем значение через функцию.
      текущий указатель на строку массива храним в outputs_csv_index 
      Обработать дефолтные значения 
    */ 
    
    function export_main_f(){
        $fname='';
        // В цикле выбираем варианты экспорта, которые будем выполнять
        $exp_source_format  = Yii::$app->db->createCommand('SELECT ec.ec_id, ec.ec_file_prefix,'.
            ' ec.ec_datastruct, t.ec_id root_id, t.ec_id_start_n start_id '.
                ' from export_customer ec '.
                ' INNER JOIN export_customer t on ec.ec_root_id_ec_id=t.ec_id '.
                        ' where exists (Select 1 from export_link_tag_field eltf '.
                                ' where eltf.eltf_ec_id = ec.ec_id) and ec.ec_cust_id = :cust_id')
            ->bindValue(':cust_id',$this->cust_id)
            ->queryAll();

        foreach ($exp_source_format as  $value) {
                                            $this->add_trace('1 value[ec_id] = '.$value['ec_id'],'value', __FUNCTION__);
            // формируем массив шаблона выгрузки 
            $this->outputs_csv = array();
            $this->outputs_csv_pattern = array();
            $this->outputs_csv_index = 0;
            

            // Получаем ID экспорта
            $this->ec_id = $value['ec_id'];
            $this->export_start_id =  $value['start_id'];
            $this->export_ec_id = $value['root_id'];
            $this->export_ec_datastruct = $value['ec_datastruct'];;

            // имя получаемого файла
            $this->outputs_csv_file = $this->result_csv_path.$value['ec_file_prefix'].$this->cust_id.'_'.$this->ss_id.'_'.$this->ec_id.'.csv';
            if (is_file($this->outputs_csv_file))
                    unlink($this->outputs_csv_file);

            $fname = $fname .'  '.$this->outputs_csv_file;

            // определяем поля экспорта
            $this->exp_fields = (new \yii\db\Query())
              ->select(['eltf.eltf_dt_id','trim(ecf.ecf_field) ecf_field', 'trim(dt.dt_name) dt_name', 'ecf.ecf_datatype'])
              ->from('export_link_tag_field eltf')
              ->join('left join', 'dir_tags dt', 'dt.dt_id = eltf.eltf_dt_id')
              ->join('left join', 'export_cms_field ecf', 'ecf.ecf_id = eltf.eltf_ecf_id')
              ->where('eltf.eltf_ec_id = :ec_id and dt.dt_id is not null')
              ->addParams([':ec_id' => $this->ec_id,])
              ->all();   
          
 //           if ($this->ec_id == 5)  // отладка
           $this->export_define_data();
        };
        $this->addlog(" На диск сохранен файл:  ".$fname);
    }
    
    /***************************************/

    function export_define_data()
    {
                                                $this->add_trace('2.1 ','marker', __FUNCTION__);
        $this->make_output_header();  // заголовок
                                                $this->add_trace('2.2 ','marker', __FUNCTION__);
 
        $exp_source_page = (new \yii\db\Query())
            ->select(['rd_sp_id', 'max(rd_parentchild_seria) sub_items', 'sp.sp_url'])
            ->from('result_data rd')
            ->join('left join','source_page sp', 'rd.rd_sp_id = sp.sp_id')
            ->where('rd.rd_ss_id = :rd_ss_id')
            ->addParams([':rd_ss_id' => $this->ss_id,])
            ->groupBy('rd_sp_id')
            ->orderBy(['rd.rd_sp_id' => SORT_ASC])
            ->all();
        
        // идем по страницам источникам
        foreach ($exp_source_page as $root_value) {
                                                    $this->add_trace('2.3 root_value[rd_sp_id] = '.$root_value['rd_sp_id'],'value', __FUNCTION__);
            // внутри делаем выборку всех характеристик с одной страницы
            $exp_parent_items = (new \yii\db\Query())
                ->select(['rd.*', 'ri.ri_img_path', 'dt.dt_is_img','dt.dt_rd_field', 'ri.ri_img_name', 'ri_img_path'])
                ->distinct()
                ->from('result_data rd')
                ->join('left join','result_img ri', 'rd.rd_id = ri.ri_rd_id')
                ->join('left join','dir_tags dt', 'rd.rd_dt_id = dt.dt_id')
                ->join('inner join','export_link_tag_field eltf', 'eltf.eltf_dt_id = rd.rd_dt_id')
                ->where('rd.rd_sp_id = :rd_sp_id '.
                    ' and rd.rd_parentchild_seria = 0 '.
                    ' and eltf.eltf_ec_id = :eltf_ec_id and eltf.eltf_id is not null')
                ->addParams([':rd_sp_id' => $root_value['rd_sp_id'],
                             ':eltf_ec_id' => $this->ec_id,])
                ->all();

            // если серийных данных нет, только корневые 1:1

            if  (($root_value['sub_items']==0) and ($this->export_ec_datastruct == 's')){

                foreach ( $exp_parent_items as $p_value) {
                    $this->exp_append();
                    $this->put_element_to_output(0, $this->get_id($root_value['sp_url'], 0)); // втыкаем ID товара
                    if ($p_value['dt_is_img']=='1') // если изображение
                    {
                                                        $this->add_trace('2.5 p_value rd_dt_id = '.$p_value['rd_dt_id'],'value', __FUNCTION__); 
                                                        $this->add_trace('2.5.1 p_value ec_id= = '.$this->ec_id,'value', __FUNCTION__); 
                        $this->put_element_to_output($p_value['rd_dt_id'], $p_value['ri_img_path'].'/'.$p_value['ri_img_name']);
                    } else {
                        $this->put_element_to_output($p_value['rd_dt_id'], $p_value[$p_value['dt_rd_field']]);
                    }
                }
            } else if (($root_value['sub_items']==0) and ($this->export_ec_datastruct == 'm')) {  // есть только корневые 

                $this->exp_append();
                $this->put_element_to_output(0,  $this->get_id($root_value['sp_url'], 0)); // втыкаем ID товара
                                                        $this->add_trace('2.3.4. sub_items = '.$root_value['sub_items'],'value', __FUNCTION__);
                foreach ( $exp_parent_items as $p_value) {
                                                        $this->add_trace('2.4 p_value[rd_id] = '.$p_value['rd_id'],'value', __FUNCTION__);
                    if ($p_value['dt_is_img']=='1') // если изображение
                    {
                                                        $this->add_trace('2.5 p_value rd_dt_id = '.$p_value['rd_dt_id'],'value', __FUNCTION__); 
                                                        $this->add_trace('2.5.1 p_value ec_id= = '.$this->ec_id,'value', __FUNCTION__); 
                        $this->put_element_to_output($p_value['rd_dt_id'], $p_value['ri_img_path'].'/'.$p_value['ri_img_name']);
                    } else {
                        $this->put_element_to_output($p_value['rd_dt_id'], $p_value[$p_value['dt_rd_field']]);
                    };
                };

            } elseif ($this->export_ec_datastruct == 'm') { // если и корневые и дочерние крутим цикл в цикле

                for ($i = 1; $i <= $root_value['sub_items']-1; $i++){

                    $this->exp_append();
                    $this->put_element_to_output(0,  $this->get_id($root_value['sp_url'], $i));

                    /* BEGIN блок выгрузки нулевых значений*/
                   reset($exp_parent_items);
                   foreach ( $exp_parent_items as $p_value) {
                                                        $this->add_trace('2.6 p_value[rd_id] = '.$p_value['rd_id'],'value', __FUNCTION__);
                        if ($p_value['dt_is_img']=='1') // если изображение
                        {
                            $this->put_element_to_output($p_value['rd_dt_id'], $p_value['ri_img_path'].'/'.$p_value['ri_img_name']);
                        } else {
                            $this->put_element_to_output($p_value['rd_dt_id'], $p_value[$p_value['dt_rd_field']]);
                        };
                    };  
                    /* END блок выгрузки нулевых значений*/


                    $exp_child_items = (new \yii\db\Query())
                        ->select(['rd_id', 'dt_is_img', 'rd_dt_id', 'ri_img_path', 'ri_img_name', 'dt_rd_field', 'rd_short_data', 'rd_long_data'])
                        ->from('result_data rd')
                        ->join('left join','result_img ri', 'rd.rd_id = ri.ri_rd_id')
                        ->join('left join','dir_tags dt', 'rd.rd_dt_id = dt.dt_id')
                        ->where('rd.rd_ss_id = :rd_ss_id and rd.rd_sp_id = :rd_sp_id and rd_parentchild_seria=:rd_parentchild_seria')
                        ->addParams([':rd_ss_id' => $this->ss_id,
                                 ':rd_sp_id' => $root_value['rd_sp_id'], 
                                 ':rd_parentchild_seria' => $i,])
                        ->orderBy(['rd.rd_parentchild_seria' => SORT_ASC])
                        ->all();

                    foreach ( $exp_child_items as $c_value) {
                                                    $this->add_trace('2.7 c_value[rd_id] = '.$c_value['rd_id'],'value', __FUNCTION__);
                        if ($c_value['dt_is_img']=='1') // если изображение
                        {
                            $this->put_element_to_output($c_value['rd_dt_id'], $c_value['ri_img_path'].'/'.$c_value['ri_img_name']);
                        } else {
                          $this->put_element_to_output($c_value['rd_dt_id'], $c_value[$c_value['dt_rd_field']]);
                        };
                    };
                }; /* end for*/
            }
        };
        $this->exp_cycle();
    }

    /***************************************/
    // формируем заголовок массива выгрузки
    function make_output_header(){
                                            $this->add_trace('3','marker', __FUNCTION__); 
        $this->outputs_csv_nparam = 0;

        $exp_fields = (new \yii\db\Query())
            ->select(['ecf.ecf_field', 'ed.ed_value'])
            ->from('export_cms_field ecf')
            ->join('left join', 'export_defaults ed', 'ed.ed_ecf_id = ecf.ecf_id')
            ->where('ecf.ecf_ec_id = :ec_id and (ed.ed_ss_id = :ss_id or ed.ed_ss_id is null)')
            ->addParams([':ec_id' => $this->ec_id,
                         ':ss_id' => $this->ss_id,])
            ->orderBy(['ecf.ecf_id' => SORT_ASC])
            ->all();   

        foreach ($exp_fields as $value) {
            ++ $this->outputs_csv_nparam;
                // Добавляем все поля в выходной массив 
            $this->outputs_csv[0][$this->outputs_csv_nparam-1] = trim($value['ecf_field']);
               // шаблон строки выходного массива с дефолтами                
            $this->outputs_csv_pattern[$this->outputs_csv_nparam-1] = (!is_null($value['ed_value'])?trim($value['ed_value']):'');
        };
    }

    /***************************************/
    // На вход id тега и значение - вносим значение в элемент массива
    /***************************************/

    // чтобы можно было один тег повторить в нескольких полях
    function put_element_to_output($tag_id, $val){
        foreach ($this->exp_fields as $exp_fields) {
          if ($exp_fields['eltf_dt_id'] == $tag_id) $this->put_element_to_output_sub($tag_id, $val, $exp_fields);
        }
    }

    /***************************************/
    function put_element_to_output_sub($tag_id, $val, $exp_fields){

                                        $this->add_trace('4.0 tag = '.$tag_id,'value', __FUNCTION__);
        $res = null;
        $val = trim($val);

        if (empty($exp_fields['ecf_field'])) return;

                                        $this->add_trace('4.1 tag = '.$tag_id,'value', __FUNCTION__);
                                        $this->add_trace('4.1.1  exp_fields[ecf_field] = '.$exp_fields['ecf_field'],'value', __FUNCTION__); 
        $res = array_search(trim($exp_fields['ecf_field']), $this->outputs_csv[0]);
                                        $this->add_trace('4.1.2  res - index of field = '.$res,'value', __FUNCTION__); 

        if (is_null($res)) return;

        // экранирование
        $val = str_replace('\n', '<br>',$val);
        $val = str_replace('"', '""',$val);

            // обрабатываем особые форматы
        if (trim($exp_fields['ecf_field']) == 'Feature(Name:Value:Position)') //PrestaShop
        {
                                        $this->add_trace('4.3 ','marker', __FUNCTION__);
            $this->outputs_csv[1][$res] .= $exp_fields['dt_name'].':'.$val.':1'.',';

        } elseif (trim($exp_fields['ecf_field']) == 'Categories (x,y,z...)') { //PrestaShop
            $this->outputs_csv[1][$res] .= $val.',';

        } elseif (trim($exp_fields['ecf_field']) == 'Image URLs (x,y,z...)') { //PrestaShop
            $this->outputs_csv[1][$res] .= $val.',';            

        } elseif (trim($exp_fields['ecf_field']) == 'description(ru-ru)') { //OpenCart
            $this->outputs_csv[1][$res] .= $val.'<br>';       

        } else { // общий случай. Просто вносим значение без предобработки

                                        $this->add_trace('4.4 ecf_datatype = '.$exp_fields['ecf_datatype'],'value', __FUNCTION__);
            if ($exp_fields['ecf_datatype'] == 'v') {  // значения
              $this->outputs_csv[1][$res] = $val;
            } elseif ($exp_fields['ecf_datatype'] == 'n') { // имена тегов
              $this->outputs_csv[1][$res] = $exp_fields['dt_name'];
            }
        };
    }

    /*******************************************/
    // добавляет строчку результирующего массива и увеличивает индекс
    function exp_append(){
        $this->exp_cycle();   
        $this->outputs_csv_index = 1;
        $this->outputs_csv[1] = $this->outputs_csv_pattern;
    }

    /*******************************************/  
    // выгрузка результирующего массива в текстовый файл
    function exp_cycle()
    {
        $res_str = '';

        for ($i = 0; $i < $this->outputs_csv_nparam; $i++) {
            $res_str .= '"'.$this->outputs_csv[$this->outputs_csv_index][$i].'"';
             if ($i< $this->outputs_csv_nparam-1) 
                $res_str .= ","; 
            else 
                $res_str .= "\n";
        };
        file_put_contents($this->outputs_csv_file, mb_convert_encoding($res_str,'UTF-8'), FILE_APPEND);
    }

    /********************************
    создает и возвращает ID записи
    Если не находит - создает. находит - выдает готовый.
    *********************************/
    function get_id($url = null, $seria_number = 0)
    {
        $new_id = null;
                                            $this->add_trace('1 ','marker', __FUNCTION__);
        $rows = (new \yii\db\Query())
            ->select(['ei_product_id'])
            ->from('export_id')
            ->where('ei_rd_parentchild_seria = :ei_rd_parentchild_seria and '.
                    ' ei_ec_id=:ei_ec_id and '.
                    ' ei_url = :ei_url')
            ->addParams([':ei_ec_id' =>  $this->export_ec_id, 
                        ':ei_rd_parentchild_seria' => $seria_number,
                        ':ei_url' => $url])
            ->one();
                                            $this->add_trace('2.5 Если значение есть ID = '.$rows['ei_product_id'],'marker', __FUNCTION__);             
        // номер уже создан - возвращаем и выходим
        If  (!empty($rows['ei_product_id'])) return $rows['ei_product_id'];

        // Нужно сгенерить!
                                            $this->add_trace('3 Значения небыло','marker', __FUNCTION__);        
        $rows = (new \yii\db\Query())
            ->select(['max(ei_product_id) as max_ei_product_id'])
            ->from('export_id')
            ->where('ei_ec_id=:ei_ec_id')
            ->addParams([':ei_ec_id' =>  $this->export_ec_id, ])
            ->one();

        // если есть предыдущие номера
        If  (!empty($rows['max_ei_product_id'])) {
                                            $this->add_trace('4 Все хорошо. Инкримент  max_ei_product_id ='.$rows['max_ei_product_id'],'marker', __FUNCTION__);                    
            $new_id = $rows['max_ei_product_id']+1;
        } else {  // предыдущих нет
            $new_id = $this->export_start_id;
        };

        // Втыкаем новый номер 
        Yii::$app->db->createCommand()
            ->insert('export_id', 
                        ["ei_ec_id" => $this->ec_id,
                         "ei_rd_parentchild_seria" =>  $seria_number,
                         "ei_url" =>  $url,
                         "ei_product_id" =>  $new_id,
                        ]) 
             ->execute();
        return $new_id;
    }

    /**************************************************/
    // Функции пред и пост обработки данных
    // функции декларируются в полях pars_rule.pr_pre_function и pars_rule.pr_post_function
    // Вызываются функции в теле класса
    /**************************************************/

    /*
        На странице Амазона выдирает ссылки на большие картинки и добавляет их ссылки вниз страницы
    */
    function pre_amazon_jp(){
        $res_arr = array();
        preg_match_all('/\"large\":\"(.*?)\"/', $this->current_page_body, $res_arr);
        
        //var_dump($res_arr);die;

        foreach ($res_arr[1] as $value) {

    
                            $this->add_trace('1  url img = '.$value,'pre_func', __FUNCTION__);
    
             //$parent = $this->current_page_DOM->getElementById('imgTagWrapperId');

            $img =  $this->current_page_DOM->createElement('img'); 
            $newnode = $this->current_page_DOM->appendChild($img);  
            $newnode->setAttribute("class","imglargelist");
            $newnode->setAttribute("src", $value);
        }

        //file_put_contents($this->ri_src_path.'Amazon_JP.html', $this->current_page_DOM->saveHTML(), FILE_APPEND);
        $this->current_page_xpath = new \DomXPath($this->current_page_DOM);
    }
}
