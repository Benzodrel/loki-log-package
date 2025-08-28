<?php


namespace boltSystem\yii2Logs\src\error\search;


use boltSystem\yii2Logs\src\error\drivers\ErrorLogDb;
use yii\data\ActiveDataProvider;

class ErrorLogDbSearch extends ErrorLogDb
{
    public $log_start;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['level', 'code', 'description', 'meta', 'date_create', 'url', 'log_start'], 'safe'],
        ];
    }

    public function behaviors()
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

    private $_dateFrom = "";
    private $_dateTo   = "";

    public function getDateFrom()
    {
        return $this->_dateFrom;
    }

    public function getDateTo()
    {
        return $this->_dateTo;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params): ActiveDataProvider
    {
        $query = ErrorLogDb::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['date_create' => SORT_DESC, 'id' => SORT_DESC]],
        ]);

        if (isset($params['ErrorLogSearch']) && isset($params['ErrorLogSearch']['from-date']) && $params['ErrorLogSearch']['from-date'] !== "") {
            $this->_dateFrom = trim($params['ErrorLogSearch']['from-date']);
        }

        if (isset($params['ErrorLogSearch']) && isset($params['ErrorLogSearch']['to-date']) && $params['ErrorLogSearch']['to-date'] !== "") {
            $this->_dateTo = trim($params['ErrorLogSearch']['to-date']);
        }

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->_dateFrom) {
            $date = \DateTime::createFromFormat('d.m.Y', $this->_dateFrom)->format('Y-m-d 00:00:01');
            $query->andWhere(['>=', 'date_create', $date]);
        }

        if ($this->_dateTo) {
            $date = \DateTime::createFromFormat('d.m.Y', $this->_dateTo)->format('Y-m-d 23:59:59');
            $query->andWhere(['<=', 'date_create', $date]);
        }

        if ($this->log_start) {
            $query->andWhere(['>=', 'date_create', $this->log_start]);
        }

        $query->andFilterWhere([
            'id'      => $this->id,
            'user_id' => $this->user_id,
            'level'   => $this->level,
        ]);

        $query->andFilterWhere(['like', 'code', $this->code])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'meta', $this->meta])
            ->andFilterWhere(['like', 'url', $this->url]);

        return $dataProvider;
    }
}