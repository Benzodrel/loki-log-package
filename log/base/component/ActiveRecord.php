<?php

namespace BoltSystem\Yii2Logs\log\base\component;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQueryInterface;

class ActiveRecord extends \yii\db\ActiveRecord
{
    public const EVENT_BEFORE_DELETE = 'EVENT_BEFORE_DELETE';
    public const EVENT_AFTER_RECOVER = 'EVENT_AFTER_RECOVER';

    /**
     * {@inheritdoc}
     *
     * @return ActiveQuery
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::class, [get_called_class()]);
    }

    /**
     * Finds ActiveRecord instance(s) by the given condition.
     * This method is internally called by [[findOne()]] and [[findAll()]].
     * @param  mixed                  $condition please refer to [[findOne()]] for the explanation of this parameter
     * @return ActiveQueryInterface   the newly created [[ActiveQueryInterface|ActiveQuery]] instance.
     * @throws InvalidConfigException if there is no primary key defined
     * @internal
     */
    protected static function findByCondition($condition)
    {
        $query = parent::findByCondition($condition);

        // $query->undeleted();

        return $query;
    }

    /**
     * @inheritdoc
     */
    public static function findAll($condition)
    {
        if ($condition) {
            return parent::findAll($condition);
        }

        return static::find()->undeleted()->all();
    }

    public function handleSoftDelete()
    {
    }

    public function handleRecover()
    {
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (isset($changedAttributes['deleted']) && $this->deleted != $changedAttributes['deleted']) {
            if (intval($this->deleted) === 1) {
                $this->handleSoftDelete();

                $this->trigger(static::EVENT_BEFORE_DELETE);
            } else {
                $this->handleRecover();

                $this->trigger(static::EVENT_AFTER_RECOVER);
            }
        }
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $this->handleSoftDelete();

            $this->trigger(static::EVENT_BEFORE_DELETE);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return ActiveQuery
     */
    public function hasOne($class, $link)
    {
        return parent::hasOne($class, $link);
    }

    /**
     * {@inheritdoc}
     *
     * @return ActiveQuery
     */
    public function hasMany($class, $link)
    {
        return parent::hasMany($class, $link);
    }
}
