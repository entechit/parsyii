<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Source_page;

class Source_pageSearch extends source_page
{
    public static function tableName()
    {
        return "source_page"; // тут меняем таблицу с которой будет работать модель
    }
   
    public function rules()
    {
        // только поля определенные в rules() будут доступны для поиска
        return [
            [['sp_id', 'sp_ss_id', 'sp_dp_id', 'sp_parsed'], 'integer'],
            [['sp_url', 'sp_datetimeadd','sp_errors'], 'safe'],
        ];
        
    }
    
    

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        if (!empty($params['sp_ss_id']))
        $params['Source_pageSearch']['sp_ss_id'] = $params['sp_ss_id'];
     //   $query = source_page::findAll('21');
        $query = source_page::find()
          ->select([
               '{{source_page}}.*', 
                  '{{source_site}}.ss_url',
                  '{{dir_page_cms}}.dp_name'
                  ])
          ->joinWith('source_site')
          ->joinWith('page_cms');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        // загружаем данные формы поиска и производим валидацию
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        // изменяем запрос добавляя в его фильтрацию
        //$query->andFilterWhere(['sp_ss_id' => $this->ss]);
        $query->andFilterWhere(['sp_id' => $this->sp_id])
              ->andFilterWhere(['sp_ss_id' => $this->sp_ss_id]);
        $query->andFilterWhere(['like', 'sp_url', $this->sp_url])
              ->andFilterWhere(['like', 'sp_datetimeadd', $this->sp_datetimeadd]);

        return $dataProvider;
    }
}

?>