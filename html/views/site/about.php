<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>

    <pre>
        Парсер сайтов для выделения контента в структуре страниц
1. Исходные данные 
  перечень сайтов, желательно с признаком структуры целевых страниц

2. Робот:
  По каждому из целевых сайтов выбирает полный перечень открытых для доступа без пароля страниц
  Строим дерево  - записываем в базу

3. Анализатор.
  Смотрим на какую структуру из уже известных больше всего похожа страница. Маркируем признаком драйвера.
  
4. Фильтруем страницы для анализа - т.е. если нам нужны страницы с описанием 1 товара, то отбираем в перечне только те, которые отвечают задаче.

5. По дереву выбранных адресов анализируем контент подобранным драйвером.
  Выбираем информацию, стягиваем картинку.

Архитектура:
  БД
  Исходные данные:
    source_site - сайты для граббинга указаннеы заказчиком
      - ss_id int()
      - ss_url varchar(250)
      - ss_dataadd - DataTimeStamp
      - ss_format - название CMS

    ss_url_tree - результат работы робота, найденные внутренние ссылки 
      - ut_id int()
      - ut_ss_id int()
      - ut_url varchar(2000)
      - ut_dn_page_type int()
      - ut_ready datatimestamp

      data_field
      - df_id int()
      - df_name varchar(250)
      - df_descript (250)
      - df_rd_fieldname ("rd_short_data" / "rd_long_data")

      driver_name
      - dn_id int()
      - dn_cms_name varchar(255)
      - dn_page_type varchar(255)

      result_data
      - rd_ut_id  int()
      - rd_df_id int()
      - rd_short_data varchar(1000)
      - rd_long_data text


Архитектура приложения
  route 
    model
      Base class 

    controller
    view
    </pre>

    <code><?= __FILE__ ?></code>
</div>
