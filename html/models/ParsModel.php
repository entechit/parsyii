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
    public $sp_id;        // указатель на текущую анализируемую страницу в source_page. Если она = 0 разбор закончен
    public $sp_url;       // адрес текущей страницы
    public $sp_dp_id;     // тип текущей страницы
    PUblic $ri_img_path;  // путь по которому сохранены картинки
    public $ri_src_path;
    public $dc_id;        // код CMS 
    public $HTTP_status;  // статус ответа загружаемой страницы

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

    // переменные работы с прайсом
    public $pager_page_n; // счетчик страниц в результате поиска
    public $searchmask; // маска поисковой строки
    public $price_id;  // текущее значение строки прайса

    public $counter_made_price_row; // обработано строк прайса 
    public $counter_add_price_pages; // найдено ссылок для строк прайса 
    public $url_per_price; // Количество вариантов сохраняемых карточек, если найдено несколько ссылок
    public $url_per_price_counter; // счетчик ссылок на одну и туже позицию прайса

//require_once Yii::app()->basePath . '/models/PriceSearchModel.php';

    // Формируем переменную коннекта к базе данных
    function __construct(){
        $this->sp_id = -1;
        $this->parslog = '';
        $this->ri_img_path = '../parsdata/';
        $this->ri_src_path = '../source_page/';
        $this->is_proxy = true;
        $this->is_trace = false;

        $this->counter_dl_img = 0;      // количество скачаных картинок
        $this->counter_dl_pages = 0;    // количество скачаных страниц
        $this->counter_add_pages = 0;   // количество добавленных в набор страниц
        $this->counter_type_pages = 0;  // количество типизированных страниц
        $this->counter_steps = 0;       // сделано шагов по справочнику страниц

        $this->mode_get_node = '';

        $this->pr_parentchild = '';
        $this->parentchild_series = 0;


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
    
        /* 
          если загружаем по прайсу, то изначально формируем ссылки на страницы с условием поиска, 
          а только потом из них выгребаем ссылки на карточки, которые нужно будет слинковать с пунктами прайса
        */
        if (($this->cb_find_internal_url == 1) and ($this->rb_url_source == 'rb_seek_url_price')){
            // делаем в другом модуле чтобы не перегружать
$this->add_trace('PRICE !!!!!');
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
                
$this->add_trace('3. main_pars_f ID : '.$this->sp_id.'   URL : '.$this->sp_url);
                
                if ($this->cb_type_source_page == '1' and empty($this->sp_dp_id)) 
                { 
                 //$this->addlog(" choose_pattern() ID : ".$this->sp_id);
                    $this->choose_pattern(); // типизирует
                }
 
                if (($this->cb_find_internal_url == 1) and ($this->rb_url_source == 'rb_seek_url_onsite'))
                {   
                    $this->add_trace('4. main_pars_f.Seek_url  ID : '.$this->sp_id.'   URL : '.$this->sp_url);
                    $this->seek_urls();  // гребет ссылки на текущей странице
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


        if ($this->cb_export_data == '1') { // выгрузка данных
            // export_data();
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
            ->select(['sp_id', 'sp_url', 'sp_dp_id'])
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

            $this->add_trace('1. FETCH ID : '.$this->sp_id.'   URL : '.$this->sp_url);

            ++ $this->counter_steps;

        } else {  // разбор окончен, больше страниц нет
            $this->sp_id = 0;
        };
    }


 // ************************************************
    // № 1. загружает по ссылке страницу в переменную $current_page_body и созданный DOM объект в current_page_DOM
    function get_page()
    {
      $res = '';
      try {
        $this->current_page_body = $this->file_get_contents_proxy($this->sp_url); 
        $current_page_DOM = new \DOMDocument();
        $current_page_DOM->preserveWhiteSpace = false;
        @$current_page_DOM->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $this->current_page_body); 
        $this->current_page_xpath = new \DomXPath($current_page_DOM);

//$this->add_trace('2. main_pars_f ID : '.$this->sp_id.' HTTP-Status : '. $this->HTTP_status .'   URL : '.$this->sp_url );
        // если ответ в заголовке не 200 ОК значит страница с ошибкой  
       
        If (strpos($this->HTTP_status, '200') === false){
          $this->mark_error_sp($this->HTTP_status);
          $res = 'continue';

$this->add_trace('2.1 main_pars_f ID : '.$this->sp_id.' HTTP-Status : '. $this->HTTP_status .'   URL : '.$this->sp_url );


        } else {
          ++ $this->counter_dl_pages;  

//$this->add_trace('2.2 main_pars_f ID : '.$this->sp_id.' HTTP-Status : '. $this->HTTP_status .'   URL : '.$this->sp_url );
        };

      } catch (yii\base\ErrorException $e) {
          $this->mark_error_sp($e);
          $res = 'continue';
//$this->add_trace('2.3 main_pars_f ID : '.$this->sp_id.' HTTP-Status : '. $this->HTTP_status .'   URL : '.$this->sp_url );

      };
     
      return $res;
    }

    //*************************************************************************************
    // в текущей странице находит уникальные ссылки и запихивает в таблицу найденных ссылок
    //*************************************************************************************
    function seek_urls()
    {
        $this->add_trace('seek_urls() SP_ID : '.$this->sp_id);

        $nodes = $this->current_page_xpath->query('//a/@href');


        
        foreach ($nodes as $node) 
        {
        
//$this->add_trace('seek_urls 1 ID : '.$this->sp_id.' NODE : '.$node->nodeValue );
            

            $res_url = trim($node->nodeValue);
            $res_arr = $this->adjust_URL($res_url); // дописываем домен
            $res_url = $res_arr[0];
//$this->add_trace('seek_urls 2 ID : '.$this->sp_id.' res_url : '.$res_url);          
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
    ***************************************************************/
    function choose_pattern()
    {
      //$this->addlog(" 0 EXECUTE choose_pattern()");
      $prev_dp_id = -1;
      $counter_cond = 0;
      $counter_vin = 0;

      if (!empty($this->sp_dp_id)) return; // если уже есть определение страницы - выходим

      //$this->addlog(" 1 EXECUTE choose_pattern()");

      // Цикл по типизаторам текущего CMS
      $row = (new \yii\db\Query())
            ->select(['pars_rule.pr_selector', 'dir_page_cms.dp_id'])
            ->from('pars_rule')
            ->join('LEFT JOIN', 'dir_page_cms', 'pars_rule.pr_dp_id = dir_page_cms.dp_id')
            ->where('dir_page_cms.dp_dc_id = :dp_dc_id and pars_rule.pr_dt_id = 1') // 1 - это id тега   поля-типизатора
            ->orderBy(['dir_page_cms.dp_id' => SORT_ASC])
            ->addParams([':dp_dc_id' => $this->dc_id, ])
            ->all();


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

              
        $expression = sprintf('count(%s) > 0', $pars_cond['pr_selector']);
        if ($this->current_page_xpath->evaluate($expression)) // если есть хоть одно совпадение
        { 
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
            //var_dump($this->sp_url);
        //var_dump($prev_dp_id);die;
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
            ->where('pars_rule.pr_dp_id = :pr_dp_id and (pars_rule.pr_id_parent is null or pars_rule.pr_id_parent="")')
            ->addParams([':pr_dp_id'=>$this->sp_dp_id]);

      foreach ($rules_rows_parent->each() as $rules_row_parent) 
      {    

$this->add_trace('PRICE 5.1 Get_content() this->sp_url = '.$this->sp_url);                    
        $this->pr_parentchild = $rules_row_parent['pr_parentchild'];

        if ($rules_row_parent['pr_nodetype']=='q') // набор элементов
        {
$this->add_trace('PRICE 5.2 Get_content() - Query ');                        
          $this->get_query($this->current_page_xpath, $rules_row_parent);
        } 
        elseif($rules_row_parent['pr_nodetype']=='n') // одиночный элемент
        { 
$this->add_trace('PRICE 5.2 Get_content() - Node ');                        
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
        
        if ($context !== NULL) {
          if ($this->pr_parentchild == 'c') ++ $this->parentchild_series;
          $res_nodes = $node->query($selector['pr_selector'], $context);  
        }else {
          $this->parentchild_series = 0;
          $res_nodes = $node->query($selector['pr_selector']);
        };


        If ($res_nodes === false) { 
            $this->add_trace("0 get_query Xpath Ошибка построения query");      
            return; 
        }

 //$this->addlog(" 1 get_query Xpath pr_id =".$selector['pr_id']. '' . $selector['pr_selector']);

        foreach ($res_nodes as $res_node) // идем внутри полученного набора элементов
        {
            $rules_rows_sub = (new \yii\db\Query())
                  ->select(['pars_rule.*', 'dir_tags.dt_rd_field', 'dir_tags.dt_is_img',])
                  ->from('pars_rule')
                  ->join('LEFT JOIN', 'dir_tags', 'pars_rule.pr_dt_id = dir_tags.dt_id')
                  ->where('pars_rule.pr_dp_id = :pr_dp_id and pars_rule.pr_id_parent = :pr_id')
                  ->addParams([':pr_dp_id' => $this->sp_dp_id,
                               ':pr_id'    => $selector['pr_id'],]);

          //  $this->addlog(" 2 get_query Xpath Мы вошли в набор!!! ");

$this->add_trace('PRICE 5.2.1 Get_query() - Query selector[pr_selector]'.$selector['pr_selector']);

            if (($this->pr_parentchild == 'c') and ($context == NULL)) {
              ++ $this->parentchild_series;  
            }  

            foreach ($rules_rows_sub->each() as $rules_row_sub) 
            {    
                 
                if ($rules_row_sub['pr_nodetype']=='q')
                {

               //   $this->addlog(" 3 get_query Q Xpath =".$rules_row_sub['pr_selector']);
                  $this->get_query($this->current_page_xpath, $rules_row_sub,  $res_node );    
               
                } elseif ($rules_row_sub['pr_nodetype']=='n') 
                {
$this->add_trace(" 4 get_query N Xpath =".$rules_row_sub['pr_selector']."PR_ID = ".$rules_row_sub['pr_id']);
                    $this->get_node($this->current_page_xpath, $rules_row_sub, $res_node );
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
$this->add_trace(" get_node 1 N Xpath =".$selector['pr_selector']);        
        if ($context !== NULL) {
          $res_nodes = $node->query($selector['pr_selector'], $context);   
        }else {
          $res_nodes = $node->query($selector['pr_selector']); 
        };

        if ( $res_nodes->length == 0) return;
        
        //$val = '1';
        foreach ($res_nodes as $res_node) {

$this->add_trace(" get_node 2 N Xpath =".$selector['pr_selector']);        

            if ($selector['dt_is_img']==1){
                $res_srcS = $node->query('./@src', $res_node);  
                $res_altS = $node->query('./@alt', $res_node);  
                $res_titleS = $node->query('./@title', $res_node);  
                $res_hrefS = $node->query('./@href', $res_node);  

                foreach ($res_srcS as $res_src) {
                    $val = $res_src->nodeValue;
                };
                
                foreach ($res_hrefS as $res_href) {
                    $val = $res_href->nodeValue;
                };

                foreach ($res_altS as $res_alt) {
                    $alt = $res_alt->nodeValue;
                };

                foreach ($res_titleS as $res_title) {
                    $title = $res_title->nodeValue;
                };
          
//$this->add_trace('Get Alt and Title: this->sp_id: '.$this->sp_id.' content : '.$full);   
            } else {
                $node = $res_node;
                $val = trim($res_node->nodeValue);

$this->add_trace(" get_node 3 val =".$val  . 'MODE_GET_NODE = '.$this->mode_get_node);        
            };
        };

        // если это картинка, то вынимаем параметры alt и title

        if ($selector['dt_is_img']){
          $res_arr = $this->adjust_URL($val); // дописываем домен
          $val = $res_arr[0];  
        };
        

        if (!empty($val)){
$this->add_trace(" get_node 4 val =".$val );
          if ($this->mode_get_node == 'result'){
$this->add_trace(" get_node 4.1 val =".$val  . 'MODE_GET_NODE = '.$this->mode_get_node);
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

          } elseif ($this->mode_get_node == 'urls') {  // записываем ссылку к прайсу
$this->add_trace(" get_node 4.2 val =".$val  . 'MODE_GET_NODE = '.$this->mode_get_node);
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

//$this->add_trace('adjust_URL 1 ID : '.$this->sp_id.' res_url : '.$res_url);      

      // 1. Определяем тип страницы 
      $exts = array('.jpg','.png', '.gif', 'webp', '.svg'); 
      if (in_array(strtolower(substr($res_url,-4,4)), $exts))
      {
        $page_type = 'img';
//$this->add_trace('adjust_URL 2.1 ID : '.$this->sp_id.' res_url : '.$res_url);           
      } 
      elseif (( substr($res_url,0,1) == '#') or (empty($res_url)))
      {
//$this->add_trace('adjust_URL 2.2 ID : '.$this->sp_id.' res_url : '.$res_url);           
        $res_url = ''; 
        $page_type = 'other';
      };


      // 2. Нужно ли дописывать домен
      if (substr($res_url,0,2) == '//')
      {
//$this->add_trace('adjust_URL 3.1 ID : '.$this->sp_id.' res_url : '.$res_url);           
        // $res_url = $source_url;

      } elseif (substr($res_url,0,1) == '/') // дописываем домен
      {
        $res_url = $this->ss_url.$res_url;

//$this->add_trace('adjust_URL 3.2 ID : '.$this->sp_id.' res_url : '.$res_url);                   

      } elseif (substr($res_url,0,4) != 'http') // начинается с непонятно чего даже без слеша
      {
        $res_url = $this->ss_url.'/'.$res_url;
//$this->add_trace('adjust_URL 3.2 ID : '.$this->sp_id.' res_url : '.$res_url);                   
      }
      elseif (substr($res_url,0,strlen($this->ss_url)) != $this->ss_url) // если внешняя ссылка
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
    function add_trace($trace_text){

        If (!$this->is_trace) return;
        
        Yii::$app->db->createCommand()
            ->insert('t_trace', 
                     ["trace_comment" => addslashes(Substr($trace_text,0,400)),]) 
            ->execute();
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
        2. Должна быть заведена страница поиска в dir_psge_cms
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


$this->add_trace('PRICE 1 Cust_id : '.$this->cust_id.'   Наименование : '.$price_row['price_modelname']);
            $this->price_id = $price_row['price_id'];

           // формируем ссылку поиска
            if (empty($price_row['price_modelcode'])){
                $search_string = $price_row['price_modelname'];
            } else {
                $search_string = $price_row['price_modelcode'];
            };

$this->add_trace('PRICE 2 search_string = '.$search_string);
            // формируем строку поиска по маске конкретного каталога 
            $search_url=sprintf($this->searchmask, $this->space2plus($search_string), $this->pager_page_n);
$this->add_trace('PRICE 3 search_url = '.$search_url);
            // инициализируем переменную ссылкой
            $this->sp_url = $search_url;
$this->add_trace('PRICE 4 insert_price_ulr_list this->sp_url = '.$this->sp_url);
            // выгребаем страницу
            $this->get_page();
$this->add_trace('PRICE 5 insert_price_ulr_list this->sp_url = '.$this->sp_url);            
            // выгрбаем через селекторы все ссылки 
            $this->get_content();
$this->add_trace('PRICE 7 insert_price_ulr_list this->sp_url = '.$this->sp_url);
            ++ $this->counter_made_price_row;
        };
    }


    //  втыкаем ссылки в линковочную таблицу и таблицу ссылок
    function insert_price_ulr_list($val){

        ++ $this->url_per_price_counter;

$this->add_trace('PRICE 6 insert_price_ulr_list VAL = '.$val);
        $res_url = $this->adjust_URL($val);
$this->add_trace('PRICE 6.1 insert_price_ulr_list res_url = '.$res_url[0]);
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
}