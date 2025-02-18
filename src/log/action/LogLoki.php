<?php

namespace app\components\log\action;

use Yii;

class LogLoki extends \app\models\Log
{
    public static function getMapActionIdToActionName()
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

    public static function getActionIdTitle($actionId)
    {
        return static::getMapActionIdToActionName()[$actionId] ?? 'Unknown';
    }

    protected static function generateId():string {

        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        $id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        return $id;
    }

    public static function create($type_id, $user_id, $action_id, $entity_id, $field) :void
    {
        $type_name   = self::getTypeNameByTypeId($type_id);
        $action_name = self::getActionIdTitle($action_id);
        $newLog      = [
            'id'          => self::generateId(),
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

    public static function createByClassName($className, $user_id, $action_id, $entity_id, $field)
    {
        $type_id = self::getTypeIdByClassName($className);

        self::create($type_id, $user_id, $action_id, $entity_id, $field);
    }
}
