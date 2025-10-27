<?php

namespace boltSystem\yii2Logs\src\event\drivers;

use Yii;
use yii\db\Exception;
use Ramsey\Uuid\Uuid;

class EventLogLoki extends EventLogDb
{
    public static function Add($type, $to_list, $data = [], $settings = [], $info = []): void
    {
        $newLog = [
            'id'          => Uuid::uuid4()->toString(),
            'type'        => $type,
            'to_list '    => $to_list,
            'data'        => $data,
            'settings'    => $settings,
            'info'        => $info,
            'date_create' => date('Y/m/d H:i:s', time()),
            'log_type'    => 'event',
        ];

        if (!count($to_list) && isset($data['parent_id'])) {
            $newLog = ['status_id' => EventLogLoki::STATUS_ERROR];
            Yii::info($newLog, 'Event');
            return;
        }

        if (isset($data['parent_id'])) {
            $newLog['parent_id'] = $data['parent_id'];
        } else {
            $newLog['parent_id'] = null;
        }

        foreach ($to_list as $_item) {
            if ($_item['value'] == 'Error') {
                $success = false;
            }
        }

        if ($success) {
            $newLog = ['status_id' => EventLogLoki::STATUS_SUCCESS];
        } else {
            $newLog = ['status_id' => EventLogLoki::STATUS_ERROR];
        }

        Yii::info($newLog, 'Event');
    }
}
