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

        $allSuccess = true;
        $oneSuccess = false;

        foreach ($to_list as $_item) {
            if (!$_item['status']) {
                $allSuccess = false;
            } else {
                $oneSuccess = true;
            }
        }

        if (!$allSuccess && !$oneSuccess) {
            $newLog = ['status_id' => EventLogLoki::STATUS_ERROR];
        }
        if (!$allSuccess && $oneSuccess) {
            $newLog = ['status_id' => EventLogLoki::STATUS_BOTH];
        }
        if ($allSuccess && $oneSuccess) {
            $newLog = ['status_id' => EventLogLoki::STATUS_SUCCESS];
        }
        if (!count($to_list)) {
            $newLog = ['status_id' => EventLogLoki::STATUS_ERROR];
        }

        Yii::info($newLog, 'Event');
    }
}
