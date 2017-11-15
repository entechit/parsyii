<?php
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
*/

    public pager_page_n = 0; // счетчик страниц в результате поиска

    // основная
    function price_main_f(){
        // выбираем прайс заказчика, позиции у которых нет привязанных ссылок в линковочной таблице
        // Mode - пусто - имя + артикул
        // articul - только по артикулу
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

           // формируем ссылку поиска
            if (empty($price_row['sp.price_modelcode']){
                $search_string = $price_row['sp.price_modelcode'];
            } else {
                $search_string = $price_row['sp.price_modelname'];
            };
            $search_url=step_search($catalog, $search_string, $pager_page_n);
            
            // инициализируем переменную ссылкой
            $this->sp_url = $search_url;
            // выгребаем страницу
            $this->get_page();
        }

    }

    // По очереди вызывается для формирования поисковой ссылки поочередно каждой страницы
    function step_search($catalog, $search_string, $page_n){
        
        if ($catalog == 'http://hotline.ua'){
            $mask = "http://hotline.ua/sr/?q=".$search_string."Xiaomi+redmi+note&p=".$page_n;
        }


    }

    // по маске получаем список ссылок в каталоге и втыкаем их в линковочную таблицу и таблицу ссылок
    function get_price_ulr_list(){

    }


    // формирует условие поиска, заменяя пробелы плюсами 
    function space2plus($incstr){
        $res = str_replace(' ', '  ', $incstr);
        $res = str_replace(' ', '+', $res);
        return $res;
    }





}