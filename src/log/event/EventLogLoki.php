<?php

namespace BoltSystem\Yii2LokiLog\log\event;

use Yii;
use yii\db\Exception;

class EventLogLoki extends \app\models\EventLog
{
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

    public static function Add($type, $to_list, $data = [], $settings = [], $info = []): void
    {
        $newLog = [
            'id'          => self::generateId(),
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
