<?php
namespace app\models; // подключаем пространство имён
use yii\db\ActiveRecord; // подключаем класс ActiveRecord
 
class Page_cms extends ActiveRecord // расширяем класс ActiveRecord 
{
   
    public static function tableName()
    {
        return "dir_page_cms"; // тут меняем таблицу с которой будет работать модель
    }
}

?>