<?php
   // namespace app\models;
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

        $res = false;


        if ($this->sp_dc == 71) //'http://hotline.ua'
        {
            $this->searchmask = "http://hotline.ua/sr/?q=%s&p=%s";
            $this->sp_dp_id = 16; // страница с результатами поиска на Hotline
            $this->pager_page_n = 0;
            $res = true;
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
            ->select([`sp.price_cust_id`, `sp.price_maufacturer`, `sp.price_cat`, `sp.price_subcat`, `sp.price_modelname`, `sp.price_modelcode`])
            ->from('source_price sp')
            ->join('LEFT JOIN', 'link_price_source_page lpsp', 'lpsp.lpsp_price_id = sp.price_id')
            ->where('sp.price_cust_id = :price_cust_id and lpsp.lpsp_id is null')
            ->addParams([':price_cust_id' => $this->cust_id,])
            ->orderBy(['price_id' => SORT_ASC])
            ->all();


        // цикл по прайсу
        foreach ($price_rows as $price_row) {


$this->add_trace('PRICE Cust_id : '.$this->cust_id.'   Наименование : '.$price_row['price_modelname']);
            $this->price_id = $price_row['price_id'];

           // формируем ссылку поиска
            if (empty($price_row['price_modelcode']){
                $search_string = $price_row['price_modelcode'];
            } else {
                $search_string = $price_row['price_modelname'];
            };
            // формируем строку поиска по маске конкретного каталога 
            $search_url=sprintf($this->searchmask, $this->space2plus($search_string), $this->pager_page_n);
            
            // инициализируем переменную ссылкой
            $this->sp_url = $search_url;
            // выгребаем страницу
            $this->get_page();
            // выгрбаем через селекторы все ссылки 
            $this->get_content();

            ++ $this->counter_made_price_row;
        };
    }


    //  втыкаем ссылки в линковочную таблицу и таблицу ссылок
    function insert_price_ulr_list($val){
        
        $res_url = $this->adjust_URL($val);

        if (!empty($res_url))
        {
            try {
                Yii::$app->db->createCommand()
                         ->insert('source_page', 
                                 ["sp_ss_id" => $this->ss_id,
                                "sp_url" => $res_url,]) 
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
        $res = str_replace(' ', '  ', $incstr);
        $res = str_replace(' ', '+', $res);
        return $res;
    }
?>