<?php
namespace app\models; // ���������� ������������ ���
use yii\db\ActiveRecord; // ���������� ����� ActiveRecord
 
class Page_cms extends ActiveRecord // ��������� ����� ActiveRecord 
{
   
    public static function tableName()
    {
        return "dir_page_cms"; // ��� ������ ������� � ������� ����� �������� ������
    }
}

?>