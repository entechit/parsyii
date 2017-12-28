<?php
namespace app\models; // подключаем пространство имён

use yii\db\ActiveRecord; // подключаем класс ActiveRecord
 
class source_site extends ActiveRecord // расширяем класс ActiveRecord 
{
    public $cust_name;
    public $dc_name;
    public $pagesCount;

    public function getSource_page()
    {
        return $this->hasMany(source_page::className(), ['sp_ss_id' => 'ss_id']);
    }
    
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
                  '{{dir_cms}}.dc_name',
                  'COUNT({{source_page}}.sp_id) AS pagesCount',
                  ])
        ->joinWith('customer')
        ->joinWith('cms')
        ->joinWith('source_page') // ensure table junction
        ->where($l_where)        
        ->groupBy('{{source_site}}.ss_id'); // group the result to ensure aggregation function works
       return $res;
    }  
}



?>