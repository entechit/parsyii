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


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['ss_url', 'ss_format'], 'required'],
            // email has to be a valid email address
            ['ss_url', 'url']
        ];
    }
}
