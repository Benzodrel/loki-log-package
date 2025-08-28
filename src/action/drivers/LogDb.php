<?php


namespace BoltSystem\Yii2Logs\log\action\drivers;

use app\models\helpers\BackendView;
use Yii;

class LogDb extends \boltSystem\yii2Logs\src\base\model\BaseModel
{
    public static function settingForIndex()
    {
        return [
            'export' => false,
            'create' => false,
            'title'  => 'Системное - Логи',
            'url'    => '/backend/log',
            'titles' => [
                'btn-create' => 'Добавить лог',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%logs_action}}';
    }

    public const ACTION_UNKNOW      = 0;
    public const ACTION_CREATE      = 1;
    public const ACTION_UPDATE      = 2;
    public const ACTION_DELETE      = 3;
    public const ACTION_RECOVER     = 4;
    public const ACTION_SOFT_DELETE = 5;

    public static function getMapActionIdToActionName(): array
    {
        return [
            self::ACTION_UNKNOW      => 'Неизвестно',
            self::ACTION_CREATE      => 'Создан',
            self::ACTION_UPDATE      => 'Изменен',
            self::ACTION_DELETE      => 'Удален',
            self::ACTION_SOFT_DELETE => 'Soft delete',
            self::ACTION_RECOVER     => 'Восстановлен',
        ];
    }

    public static function getMapClassNameToTypeId(): array
    {
        return Yii::$app->params['actionLogParams']['getMapClassNameToTypeId'];
    }

    public static function getMapTypeIdToTypename(): array
    {
        return Yii::$app->params['actionLogParams']['getMapTypeIdToTypename'];
    }

    public static function getMapTypeIdToLinkEdit(): array
    {
        return Yii::$app->params['actionLogParams']['getMapTypeIdToLinkEdit'];
    }

    public static function getTypeIdByClassName($className)
    {
        $listMap = self::getMapClassNameToTypeId();

        if (!isset($listMap[$className])) {
            return self::ACTION_UNKNOW;
        }

        return $listMap[$className];
    }

    public static function getTypeNameByTypeId($type_id)
    {
        $listMap = self::getMapTypeIdToTypename();

        if (!isset($listMap[$type_id])) {
            $type_id = self::ACTION_UNKNOW;
        }

        return $listMap[$type_id];
    }

    public static function getTypeNameByClassName($className)
    {
        $type_id = self::getTypeIdByClassName($className);
        return self::getTypeNameByTypeId($type_id);
    }

    public static function getClassNameByTypeId($type_id)
    {
        foreach (self::getMapClassNameToTypeId() as $className => $value) {
            if ($type_id === $value) {
                return $className;
            }
        }

        return false;
    }

    public static function create($type_id, $user_id, $action_id, $entity_id, $field)
    {
        $newLog = new LogDb();

        $newLog->type_id   = $type_id;
        $newLog->user_id   = $user_id;
        $newLog->action_id = $action_id;
        $newLog->entity_id = $entity_id;

        $newLog->meta = json_encode([
            'field' => $field,
        ]);

        $newLog->save();

        return $newLog;
    }

    public static function createByClassName($className, $user_id, $action_id, $entity_id, $field)
    {
        $type_id = self::getTypeIdByClassName($className);

        return self::create($type_id, $user_id, $action_id, $entity_id, $field);
    }

    public function getClassName(): string
    {
        return LogDb::getClassNameByTypeId($this->type_id);
    }

    public function getFields(): array
    {
        $fields = [];
        $meta   = json_decode($this->meta, true);

        if (isset($meta['field'])) {
            $fields = $meta['field'];
        }

        return $fields;
    }

    public function getFieldsCount(): int
    {
        $fields = $this->getFields();
        $count  = 0;

        foreach ($fields as $key => $value) {
            $count++;
        }

        return $count;
    }

    public function getLinkEdit(): string
    {
        return LogDb::getMapTypeIdToLinkEdit()[$this->type_id] . '?id=' . $this->entity_id;
    }

    public function getTypeName(): string
    {
        return LogDb::getTypeNameByTypeId($this->type_id);
    }

    public function serializeLogData()
    {
        $class  = $this->getClassName();
        $fields = $this->getFields();

        $html = [];

        foreach ($fields as $name => $value) {
            if ($name == 'status_id' || $name == 'activity') {
                $html[$name] = BackendView::getDropdown_status()[$value];
            } elseif ($name == 'user_id' || $name == 'user_create_id' || $name == 'creator_id') {
                $list        = BackendView::getDropdown_user();
                $html[$name] = (isset($list[$value]) ? $list[$value] : '(Неизвестно)');
            } else {
                $html[$name] = $value;
            }
        }

        return $html;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['user_id', 'type_id', 'action_id', 'entity_id', 'meta'], 'required'],
            [['user_id', 'type_id', 'action_id', 'entity_id'], 'integer'],
            [['meta'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(): array
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'user_id'     => Yii::t('app', 'Пользователь'),
            'type_id'     => Yii::t('app', 'Сущность'),
            'action_id'   => Yii::t('app', 'Действие'),
            'entity_id'   => Yii::t('app', 'Элемент'),
            'meta'        => Yii::t('app', 'Описание'),
            'date_create' => Yii::t('app', 'Date Create'),
        ];
    }
}