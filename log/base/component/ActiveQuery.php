<?php

namespace BoltSystem\Yii2Logs\log\base\component;

use app\models\User;
use Yii;
use yii\db\ActiveQuery as BaseQuery;

class ActiveQuery extends BaseQuery
{
    /**
     * Получение alias-a для таблицы
     * @return null|string
     */
    public function getAlias()
    {
        [, $alias] = $this->getTableNameAndAlias();

        return $alias;
    }

    public function hideDeleted($joinWith = false)
    {
        $alias = $this->alias;

        $modelClass = $this->modelClass;
        $model      = $modelClass::instance();

        if ($model->hasAttribute('deleted')) {
            if ($joinWith) {
                $this->andOnCondition(["{$alias}.deleted" => 0]);
            } else {
                $this->andWhere(["{$alias}.deleted" => 0]);
            }
        }

        return $this;
    }

    public function hideDeactivated($joinWith = false)
    {
        $alias = $this->alias;

        /**
         * @var string                     $modelClass
         * @var \app\models\base\BaseModel $model
         */
        $modelClass = $this->modelClass;
        $model      = $modelClass::instance();

        if (defined("$modelClass::STATUS_DEACTIVE")) {
            if ($model->hasAttribute('activity')) {
                if ($joinWith) {
                    $this->andOnCondition(['!=', "{$alias}.activity", $modelClass::STATUS_DEACTIVE]);
                } else {
                    $this->andWhere(['!=', "{$alias}.activity", $modelClass::STATUS_DEACTIVE]);
                }
            } elseif ($model->hasAttribute('status_id')) {
                if ($joinWith) {
                    $this->andOnCondition(['!=', "{$alias}.status_id", $modelClass::STATUS_DEACTIVE]);
                } else {
                    $this->andWhere(['!=', "{$alias}.status_id", $modelClass::STATUS_DEACTIVE]);
                }
            }
        }

        return $this;
    }

    public function apiUndeleted($join = false)
    {
        $this->hideDeleted($join);
        $this->hideDeactivated($join);

        return $this;
    }

    public function undeleted($showDeactivated = true)
    {
        $currentUser = $this->getUser();

        if (!$currentUser || $currentUser->role != User::ROLE_ADMIN) {
            $this->hideDeleted();
        } else {
            if (!$showDeactivated) {
                $this->hideDeactivated();
            }
        }

        return $this;
    }

    public function filterUndeleted($showDeactivated = true)
    {
        return $this->undeleted($showDeactivated);
    }

    public function undeletedJoin($showDeactivated = true)
    {
        $currentUser = $this->getUser();

        if (!$currentUser || $currentUser->role != User::ROLE_ADMIN) {
            $this->hideDeleted(true);
            $this->hideDeactivated(true);
        } else {
            if (!$showDeactivated) {
                $this->hideDeactivated(true);
            }
        }

        return $this;
    }

    private function getUser()
    {
        $currentUser = false;

        if (Yii::$app->request->isConsoleRequest) {
            return false;
        }

        if (Yii::$app->request->getIsAjax() || strpos(Yii::$app->request->url, '/api/') === 0) {
            return false;
        }

        if (Yii::$app->hasProperty('user')) {
            $currentUser = User::getCurrent();
        }

        return $currentUser;
    }

    /**
     * @return ActiveRecord|null
     */
    public function one($db = null): ?ActiveRecord
    {
        // Conditionally add limit 1 clause
        if (!empty($this->where)) {
            $class = $this->modelClass;
            $pks   = $class::primaryKey();
            // Check if all pk are used in where. If not, use LIMIT 1. (Maybe we can test for unique indexes too)
            if (!empty(array_diff(array_values($pks), array_keys($this->where)))) {
                $this->limit(1);
            }
        } else {
            $this->limit(1);
        }

        return parent::one($db);
    }
}
