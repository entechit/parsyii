<?php
namespace app\models; // подключаем пространство имён

use yii\db\ActiveRecord; // подключаем класс ActiveRecord
 
class source_page extends ActiveRecord // расширяем класс ActiveRecord 
{
    public $ss_name;
    public $dc_name;
    public $pagesCount;
    public $ss_url;
    public $dp_name;

    public function getSource_site()
    {
        return $this->hasMany(source_site::className(), ['ss_id' => 'sp_ss_id']);
    }
    
    public function getPage_cms()
    {
        return $this->hasMany(page_cms::className(), ['dp_id' => 'sp_dp_id']);
    }    
  /*  
    public function getCustomer()
    {
        return $this->hasMany(Customer::className(), ['cust_id' => 'ss_cust_id']);
    }

    public function getCms()
    {
        return $this->hasMany(Cms::className(), ['dc_id' => 'ss_dc_id']);
    }
  */
    public static function FindFull($ss_id)
    {
        /*
       $l_where = '1=1';
       if (!empty($ss_id))
         $l_where = 'sp_ss_id = "'.$ss_id.'"';
       // 
       $res = source_page::find();
       //->where($l_where);    
       return $res;
       */
     /*   
        $res = source_page::findValid()
         ->select([
               '{{source_page}}.*', 
                  '{{source_site}}.ss_url'
                  ])
        ->joinWith('source_site');

        
        return $res;
        */
    }
  
}



?>