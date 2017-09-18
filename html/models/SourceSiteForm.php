<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class SourceSiteForm extends Model
{
    public $ss_url;
    public $ss_format;
    public $ss_descript;
    public $db;

    // Формируем переменную коннекта к базе данных
    function __construct(){
        $db = Yii::$app->db;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['ss_url', 'ss_format'], 'required', 'message'=>'Поле URL не может быть пустым.'],
            // email has to be a valid email address
            ['ss_url', 'url'],

        ];
    }

    // выгребаем пока что все из таблицы источников сайта
    public function getdata(){
        $posts = Yii::$app->db->createCommand('SELECT * FROM source_site')->queryAll();
        return $posts;
    }
}
