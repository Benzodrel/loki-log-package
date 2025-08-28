<?php


namespace boltSystem\yii2Logs\src\event\search;


use app\models\Event;
use boltSystem\yii2Logs\src\event\drivers\EventLogDb;
use yii\base\BaseObject;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class EventLogDbSearch extends EventLogDb
{
    public $q;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [["id", "sorting", "activity", 'user_type', 'event_type', 'timezone', 'category', 'city_id'], "integer"],
            [["deleted", "is_archive", 'online', "organizer_site_show"], "boolean"],
            [["title", "dates", "description", "video_stream_link", "date_create", "date_update", "q", 'date_from', 'date_to', 'video_stream', 'time_start', 'organizer', "organizer_site_link"], "safe"],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = EventLogDb::find()
            ->alias('model');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->query($query, $params);

        return $dataProvider;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function querySearch($params)
    {
        $query = EventLogDb::find()
            ->alias('model');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $formData = [
            $this->formName() => $params['filter'] ?? [],
        ];

        $this->query($query, $formData);

        return $dataProvider;
    }

    protected function query($query, $formData)
    {

        if (!($this->load($formData) && $this->validate())) {
            return false;
        }

        $query->andFilterWhere([
            'model.id'         => $this->id,
            'model.sorting'    => $this->sorting,
            'model.activity'   => $this->activity,
            'model.deleted'    => $this->deleted,
            'model.event_type' => $this->event_type,
            'model.category'   => $this->category,
            'model.city_id'    => $this->city_id,
            'model.user_type'  => $this->user_type,
            'model.timezone'   => $this->timezone,
        ]);

        $query
            ->andFilterWhere(['like', 'model.title', $this->title])
            ->andFilterWhere(['like', 'model.dates', $this->dates])
            ->andFilterWhere(['like', 'model.description', $this->description])
            ->andFilterWhere(['like', 'model.video_stream_link', $this->video_stream_link])
            ->andFilterWhere(['like', 'model.video_stream', $this->video_stream])

            ->andFilterWhere(['like', 'model.date_create', $this->date_create])
            ->andFilterWhere(['like', 'model.date_update', $this->date_update])
            ->andFilterWhere(['like', 'model.date_from', $this->date_from])
            ->andFilterWhere(['like', 'model.date_to', $this->date_to])
            ->andFilterWhere(['like', 'model.time_start', $this->time_start])
            ->andFilterWhere(['like', 'model.online', $this->online])
            ->andFilterWhere(['like', 'model.organizer', $this->organizer])
            ->andFilterWhere(['like', 'model.organizer_site_link', $this->organizer])
            ->andFilterWhere(['like', 'model.organizer_site_show', $this->organizer])
        ;

        $query->andFilterWhere([
            'or',
            ['model.id' => $this->q],
            ['like', 'model.title', $this->q],
            ['like', 'model.description', $this->q],
            ['like', 'model.video_stream_link', $this->q],
            ['like', 'model.video_stream', $this->q],
            ['like', 'model.organizer', $this->q],
            ['like', 'model.organizer_site_link', $this->q],
        ]);

        return false;
    }
}