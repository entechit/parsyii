<?php
namespace app\models; // подключаем пространство имён

use yii\db\ActiveRecord; // подключаем класс ActiveRecord
 
class source_site extends ActiveRecord // расширяем класс ActiveRecord 
{
    public $cust_name;
    public $dc_name;    
    
    public function getCustomer()
    {
        return $this->hasMany(Customer::className(), ['cust_id' => 'ss_cust_id']);
    }

    public function getCms()
    {
        return $this->hasMany(Cms::className(), ['dc_id' => 'ss_dc_id']);
    }

    public static function FindFull($cust_id)
    {
       $l_where = '1=1';
       if (!empty($cust_id))
         $l_where = 'ss_cust_id = "'.$cust_id.'"';
       // 
       $res = source_site::find()
         ->select([
               '{{source_site}}.*', 
                  '{{customer}}.cust_name',
                  '{{dir_cms}}.dc_name'
                  ])
        ->where($l_where)
        ->joinWith('customer')
        ->joinWith('cms');
       return $res;
    }  
}



?>