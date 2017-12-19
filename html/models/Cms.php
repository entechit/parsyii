<?php
namespace app\models; // подключаем пространство имён
use yii\db\ActiveRecord; // подключаем класс ActiveRecord
 
class Cms extends ActiveRecord // расширяем класс ActiveRecord 
{

    public $customersCount;
    public $sitesCount;
    
    public static function tableName()
    {
        return "dir_cms"; // тут меняем таблицу с которой будет работать модель
    }

    public function getCustomer()
    {
        return $this->hasMany(Customer::className(), ['cust_dc' => 'dc_id']);
    }
    
    public function getSource_site()
    {
        return $this->hasMany(source_site::className(), ['ss_dc_id' => 'dc_id']);
    }
    
    public static function FindFull()
    {
       $res = Cms::find()
         ->select([
               '{{dir_cms}}.*', // select all cms fields
                  'COUNT({{source_site}}.ss_id) AS sitesCount', // calculate sites count
                  'COUNT({{customer}}.cust_id) AS customersCount' // calculate customers count
                  ])
         ->joinWith('source_site') // ensure table junction
         ->joinWith('customer') // ensure table junction
         ->groupBy('{{dir_cms}}.dc_id'); // group the result to ensure aggregation function works
     return $res;
    }
}

?>