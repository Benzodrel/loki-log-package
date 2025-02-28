<?php

namespace BoltSystem\Yii2Logs\log\action\search;

use BoltSystem\Yii2Logs\log\action\drivers\LogDb;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * LogSearch represents the model behind the search form about `app\models\Log`.
 */
class LogDbSearch extends LogDb
{
    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id', 'type_id', 'action_id', 'entity_id'], 'integer'],
            [['meta'], 'safe'],
        ];
    }

    public function behaviors(): array
    {
        return [];
    }
    

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return parent::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params) :ActiveDataProvider
    {
        $query = LogDb::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => [ 'id' => SORT_DESC ]]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            $query->undeleted();
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'        => $this->id,
            'user_id'   => $this->user_id,
            'type_id'   => $this->type_id,
            'action_id' => $this->action_id,
            'entity_id' => $this->entity_id,
        ]);

        // $query->andFilterWhere(['like', 'meta', $this->meta]);

        $query->undeleted();
        return $dataProvider;
    }
}
