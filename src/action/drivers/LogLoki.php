<?php

namespace boltSystem\yii2Logs\src\action\drivers;

use Yii;
use Ramsey\Uuid\Uuid;

class LogLoki extends LogDb
{
    public static function getMapActionIdToActionName(): array
    {
        return [
            self::ACTION_UNKNOW      => 'Unknown',
            self::ACTION_CREATE      => 'Create',
            self::ACTION_UPDATE      => 'Update',
            self::ACTION_DELETE      => 'Delete',
            self::ACTION_SOFT_DELETE => 'Soft delete',
            self::ACTION_RECOVER     => 'Recover',
        ];
    }

    public static function getActionIdTitle($actionId): string
    {
        return static::getMapActionIdToActionName()[$actionId] ?? 'Unknown';
    }

    public static function create($type_id, $user_id, $action_id, $entity_id, $field) :void
    {
        $type_name   = self::getTypeNameByTypeId($type_id);
        $action_name = self::getActionIdTitle($action_id);
        $newLog      = [
            'id'          => Uuid::uuid4()->toString(),
            'level'       => 'info',
            'code'        => 200,
            'type_id'     => $type_id,
            'type_name'   => $type_name,
            'user_id'     => $user_id,
            'action_id'   => $action_id,
            'action_name' => $action_name,
            'entity_id'   => $entity_id,
            'fields'      => $field,
            'log_type'    => 'action',
        ];

        Yii::info($newLog, $action_name);
    }

    public static function createByClassName($className, $user_id, $action_id, $entity_id, $field): void
    {
        $type_id = self::getTypeIdByClassName($className);

        self::create($type_id, $user_id, $action_id, $entity_id, $field);
    }
}
