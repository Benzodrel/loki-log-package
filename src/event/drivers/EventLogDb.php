<?php


namespace boltSystem\yii2Logs\src\event\drivers;


use yii\base\BaseObject;

class EventLogDb extends \boltSystem\yii2Logs\src\base\model\BaseModel
{
    public const STATUS_ERROR   = 0;
    public const STATUS_SUCCESS = 1;
    public const STATUS_BOTH    = 2;

    public function __get($name)
    {
        $metaFields = $this->metaFields();

        if ($metaFields && is_array($metaFields)) {
            if (isset($metaFields[$name])) {
                return $this->getMetaFieldValue($name);
            }
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        $metaFields = $this->metaFields();

        if ($metaFields && is_array($metaFields)) {
            if (isset($metaFields[$name])) {
                $this->setMetaFieldValue($name, $value);
            }
        }

        return parent::__set($name, $value);
    }

    public static function getMapStatuses()
    {
        return [
            self::STATUS_ERROR   => 'Ошибка',
            self::STATUS_SUCCESS => 'Успешно',
            self::STATUS_BOTH    => 'С ошибками',
        ];
    }

    public function getTitle()
    {
        return \app\models\system\Events::getMapEvents()[$this->type];
    }

    public static function Add($type, $to_list, $data = [], $settings = [], $info = [])
    {
        $newLog = new EventLogDb();

        $newLog->type     = $type;
        $newLog->to_list  = $to_list;
        $newLog->data     = $data;
        $newLog->settings = $settings;
        $newLog->info     = $info;

        $newLog->date_create = date('Y/m/d H:i:s', time());

        $allSuccess = true;
        $oneSuccess = false;

        foreach ($to_list as $_item) {
            if (!$_item['status']) {
                $allSuccess = false;
            } else {
                $oneSuccess = true;
            }
        }

        if (!$allSuccess && !$oneSuccess) $newLog->status_id = EventLogDb::STATUS_ERROR;
        if (!$allSuccess && $oneSuccess)  $newLog->status_id = EventLogDb::STATUS_BOTH;
        if ($allSuccess && $oneSuccess)   $newLog->status_id = EventLogDb::STATUS_SUCCESS;
        if (!count($to_list))           $newLog->status_id = EventLogDb::STATUS_ERROR;

        $newLog->save();
    }

    public static function settingForIndex()
    {
        return [
            'export' => false,
            'create' => false,
            'title'  => 'Лог событий',
            'url'    => '/backend/event-log',
            'titles' => [
                'btn-create' => 'Создать'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logs_event}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status_id'], 'integer'],
            [['date_create'], 'safe'],
            [['type', 'meta'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'type'        => 'Тип',
            'meta'        => 'META',
            'status_id'   => 'Статус',
            'mail_count'  => 'Писем отправлено',
            'push_count'  => 'Пуш ув. отправлено',
            'mail_list'   => 'Список e-mail',
            'date_create' => 'Дата создания',
        ];
    }

    public function metaFields()
    {
        return [
            'to_list'  => [],
            'data'     => [],
            'settings' => [],
            'info'     => [],
            'errors'   => []
        ];
    }

    public function customFields()
    {
        return [
            'to_list',
            'errors',
            'mail_count',
            'mail_list',
            'push_count',
        ];
    }

    public function getMail_count()
    {
        $value = 0;

        foreach ($this->to_list as $_item) {
            if ($_item->type == 'mail') {
                $value++;
            }
        }

        return $value;
    }

    public function getMail_list()
    {
        $value = [];

        foreach ($this->to_list as $_item) {
            if ($_item->type == 'mail') {
                $value[] = $_item->to;
            }
        }

        return count($value) ? implode(', ', $value) : 'Нет';
    }

    public function getPush_count()
    {
        $value = 0;

        foreach ($this->to_list as $_item) {
            if ($_item->type == 'push') {
                $value++;
            }
        }

        return $value;
    }
}