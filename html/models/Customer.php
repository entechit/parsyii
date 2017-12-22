<?php
namespace app\models; // ���������� ������������ ���
use yii\db\ActiveRecord; // ���������� ����� ActiveRecord
 
class Customer extends ActiveRecord // ��������� ����� ActiveRecord 
{
    public $sitesCount;

    public function getSource_site()
    {
        return $this->hasMany(source_site::className(), ['ss_cust_id' => 'cust_id']);
    }

    public static function FindFull()
    {
       $res = Customer::find()
         ->select([
               '{{customer}}.*', // select all cms fields
                  'COUNT({{source_site}}.ss_id) AS sitesCount', // calculate sites count
                  ])
         ->joinWith('source_site') // ensure table junction
         ->groupBy('{{customer}}.cust_id'); // group the result to ensure aggregation function works
     return $res;
    }    
}    
?>