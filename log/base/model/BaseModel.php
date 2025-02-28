<?php


namespace BoltSystem\Yii2Logs\log\base\model;


class BaseModel extends \app\components\db\ActiveRecord
{
    public const ALLOW_STATUS = false;

    public const EVENT_AFTER_SAVE = 'EVENT_AFTER_SAVE';

    public const STATUS_ACTIVE   = 1;
    public const STATUS_DEACTIVE = 2;

    public static function mapStatus()
    {
        return [
            static::STATUS_ACTIVE   => 'Активный',
            static::STATUS_DEACTIVE => 'Не активный',
        ];
    }

    public const TYPE_RELATION_SINGLE   = 'single';
    public const TYPE_RELATION_MULTIPLE = 'multiple';

    protected $linkedFields = [];

    public function setLinkedField($name, $value)
    {
        $this->linkedFields[$name] = $value;
    }

    public function getLinkedField($name)
    {
        $linkedSetup = $this->hasMethod('linkedFieldsSetup') ? $this->linkedFieldsSetup() : [];

        if (array_key_exists($name, $this->linkedFields)) {
            return $this->linkedFields[$name] ?: [];
        }

        if (isset($linkedSetup[$name])) {
            list($linkClass, $linkField, $valueField) = $linkedSetup[$name];

            return $linkClass::getLink($this->id, $linkField, $valueField);
        }

        $link = DictionaryLinked::getLink(
            12,
            $this->id,
            static::getTypeForDictionaryLinked(),
            $name
        );

        return array_map('intval', $link);
    }

    public static function getClassByTypeModel($typeModel)
    {
        return match ($typeModel) {
            default                                         => false,
        };
    }

    public static function getAllModelTypes()
    {
        return [
        ];
    }

    public static function settingForIndex()
    {
        return [];
    }

    public function setAttributes($attrs, $safeOnly = true, $customFields = false)
    {
        parent::setAttributes($attrs, $safeOnly);

        if ($customFields) {
            $customFields = $this->customFields();

            if ($customFields && is_array($customFields) && count($customFields)) {
                foreach ($customFields as $fieldName) {
                    if (isset($attrs[$fieldName])) {
                        $this->{$fieldName} = $attrs[$fieldName];
                    }
                }
            }
        }
    }

    public static function translateType()
    {
        return false;
    }

    public static function translateFields()
    {
        return [];
    }

    public function metaFields()
    {
        return [];
    }

    public function getAction()
    {
    }

    public function prepareMetaForCustomFields()
    {
        $meta = $this->meta ?: [];

        if (is_string($this->meta)) {
            if (!trim($this->meta)) {
                $this->meta = '{}';
            }

            $meta = json_decode($this->meta, true) ?? [];
        }

        try {
            $meta = array_merge($this->metaFields(), $meta);
        } catch (Exception $e) {
            echo 'Meta incorrect:';
            echo '<pre>';

            var_dump($meta);

            die();
        }

        $this->meta = json_encode($meta);
    }

    public function getMeta_fields()
    {
        $this->prepareMetaForCustomFields();

        return json_decode($this->meta);
    }

    public function setMeta_fields($meta)
    {
        $this->prepareMetaForCustomFields();

        $currentMeta = json_decode($this->meta, true);

        foreach ($meta as $name => $value) {
            $currentMeta[$name] = $value;
        }

        $this->meta = json_encode($currentMeta);
    }

    public function getMetaFieldValue($name)
    {
        $fields = $this->meta_fields;

        if (in_array($name, array_keys((array) $fields))) {
            return $fields->{$name};
        }

        return null;
    }

    public function setMetaFieldValue($name, $value)
    {
        $this->meta_fields = [
            $name => $value,
        ];
    }

    public static function deleteByCustomAttr($filter, $substr = false)
    {
        $items = self::searchByCustomAttr($filter, $substr);

        foreach ($items as $_item) {
            $_item->delete();
        }

        return true;
    }

    public static function findByCustomAttr($filter, $substr = false)
    {
        return static::find()->where(['id' => static::searchIDByCustomAttr($filter, $substr)]);
    }

    public static function searchByCustomAttr($filter, $substr = false)
    {
        return static::findByCustomAttr($filter, $substr)->all();
    }

    public static function searchIDByCustomAttr($filter, $substr = false)
    {
        $all = static::find()->all();

        $itemsID = [];

        foreach ($all as $_item) {
            $verif = true;

            foreach ($filter as $_name => $_value) {
                $itemValue = $_item->{$_name};

                if (!is_array($itemValue)) {
                    if (!$substr) {
                        if (!is_array($_value)) {
                            if ($itemValue != $_value) {
                                $verif = false;
                            }
                        } else {
                            if (!in_array($itemValue, $_value)) {
                                $verif = false;
                            }
                        }
                    } else {
                        if (!is_array($_value)) {
                            if (strpos($itemValue, $_value) === false) {
                                $verif = false;
                            }
                        } else {
                            foreach ($_value as $_val) {
                                $inArray = false;

                                if (strpos($itemValue, $_val) !== false) {
                                    $inArray = true;
                                }

                                $verif = $inArray;
                            }
                        }
                    }
                } else {
                    $inArrayVerif = false;

                    foreach ($itemValue as $_nameItem => $_valueItem) {
                        if (!$substr) {
                            if (!is_array($_value)) {
                                if ($_valueItem == $_value) {
                                    $inArrayVerif = true;
                                }
                            } else {
                                if (in_array($_valueItem, $_value)) {
                                    $inArrayVerif = true;
                                }
                            }
                        } else {
                            if (!is_array($_value)) {
                                if (strpos($_valueItem, $_value) !== false) {
                                    $inArrayVerif = true;
                                }
                            } else {
                                foreach ($_value as $_val) {
                                    $inArray = false;

                                    if (strpos($_valueItem, $_val) !== false) {
                                        $inArray = true;
                                    }

                                    $inArrayVerif = $inArray;
                                }
                            }
                        }

                        if ($inArrayVerif) {
                            break;
                        }
                    }

                    $verif = $inArrayVerif;
                }
            }

            if ($verif) {
                $itemsID[] = $_item->id;
            }
        }

        return $itemsID;
    }

    public function asArray()
    {
        $data = $this->attributes;

        $customFields = $this->customFields();

        if ($customFields) {
            foreach ($customFields as $_field) {
                $data[$_field] = $this->{$_field};
            }
        }

        return $data;
    }

    public function getOwnRelation($item, $execute = true)
    {
        list($modelClassName, $tech_name, $type_id) = $item;

        $name = $item[3] ?? null;

        $query = null;

        if ($type_id == BaseModel::TYPE_RELATION_SINGLE) {
            if ($name) {
                $methodName = 'get' . Inflector::camelize($name);

                if (\method_exists($this, $methodName)) {
                    $query = $this->$methodName();
                } else {
                    $query = $modelClassName::find()
                        ->where([$tech_name => $this->id])
                        ->apiUndeleted();
                }
            } else {
                $query = $modelClassName::find()
                    ->where([$tech_name => $this->id])
                    ->apiUndeleted();
            }
        } elseif ($type_id == BaseModel::TYPE_RELATION_MULTIPLE) {
            $query = $modelClassName::find()
                ->where([
                    'id' => DictionaryLinked::find()->where([
                        'type_id' => $modelClassName::getTypeForDictionaryLinked(),
                        'title'   => $tech_name,
                        'value'   => strval($this->id),
                    ])->select('entity_id'),
                ])
                ->apiUndeleted();
        } else {
            throw new ErrorException('Unknown own relation type');
        }

        if ($execute) {
            return $query->all();
        }

        return $query;
    }

    public function toPlainData($fields = [])
    {
        if (!is_array($fields)) {
            throw new ErrorException('toPlainData received non-array arguments', 1);
        }

        $data = $this->attributes;

        $customFields = $this->customFields();

        if ($customFields) {
            foreach ($customFields as $_field) {
                $data[$_field] = $this->{$_field};
            }
        }

        $allFlag          = in_array('[[*]]', $fields);
        $allFieldsFlag    = in_array('[[fields]]', $fields);
        $allCompositeFlag = in_array('[[composite]]', $fields);
        $allRelationsFlag = in_array('[[relations]]', $fields);
        $allFilesFlag     = in_array('[[files]]', $fields);

        $filteredData = [];

        if (
            empty($fields) ||
            $allFieldsFlag ||
            $allFlag
        ) {
            $filteredData = $data;
        } else {
            foreach ($fields as $key => $value) {
                if (!is_array($value)) {
                    if (array_key_exists($value, $data)) {
                        $filteredData[$value] = $data[$value];
                    }
                }
            }
        }

        foreach ($this->compositeFields() as $value) {
            if (
                empty($fields) ||
                $allCompositeFlag ||
                $allFlag ||
                in_array($value, $fields)
            ) {
                $filteredData[$value] = $this->{$value};
            }
        }

        foreach (static::getMapRelationList() as $value) {
            if (
                $allRelationsFlag ||
                $allFlag ||
                isset($fields[$value]) ||
                in_array($value, $fields)
            ) {
                $fieldsValue = ArrayHelper::getValue($fields, $value, false);

                $relationValue = $this->{$value};

                if (isset($relationValue)) {
                    if (is_array($relationValue)) {
                        $filteredData[$value] = [];

                        foreach ($relationValue as $rI => $rV) {
                            if (is_object($rV)) {
                                if (!$rV->isEnabled) {
                                    continue;
                                }

                                if ($rV->hasMethod('translateByLang')) {
                                    $rV->translateByLang($this->currentLang);
                                }

                                if (is_array($fieldsValue)) {
                                    $filteredData[$value][] = $rV->toPlainData($fieldsValue);
                                } else {
                                    $filteredData[$value][] = $rV->toPlainData();
                                }
                            } else {
                                if (is_array($fieldsValue)) {
                                    Yii::$app->errorLog::RegisterErrorByErrorException(
                                        new ErrorException("Can't resolve relation " . static::class . '::' . $value . '[' . $rI . '] => ' . json_encode($fieldsValue))
                                    );
                                } else {
                                    Yii::$app->errorLog::RegisterErrorByErrorException(
                                        new ErrorException("Can't resolve relation " . static::class . '::' . $value . '[' . $rI . ']')
                                    );
                                }
                            }
                        }
                    } else {
                        if (is_object($relationValue)) {
                            try {
                                if (!$relationValue->isEnabled) {
                                    $filteredData[$value] = null;

                                    continue;
                                }
                            } catch (\Throwable $e) {
                                throw new Exception('[' . get_class($relationValue) . "][$value] " . $e->getMessage(), $e->getCode());
                            }

                            if ($relationValue->hasMethod('translateByLang')) {
                                $relationValue->translateByLang($this->currentLang);
                            }

                            if (is_array($fieldsValue)) {
                                $filteredData[$value] = $relationValue->toPlainData($fieldsValue);
                            } else {
                                $filteredData[$value] = $relationValue->toPlainData();
                            }
                        } else {
                            if (is_array($fieldsValue)) {
                                Yii::$app->errorLog::RegisterErrorByErrorException(
                                    new ErrorException("Can't resolve relation " . static::class . '::' . "$value => " . json_encode($fieldsValue))
                                );
                            } else {
                                Yii::$app->errorLog::RegisterErrorByErrorException(
                                    new ErrorException("Can't resolve relation " . static::class . '::' . "$value")
                                );
                            }
                        }
                    }
                } else {
                    $filteredData[$value] = null;
                }
            }
        }

        foreach ($this->getOwnRelations() as $item) {
            if (count($item) >= 4) {
                list($modelClassName, $tech_name, $type_id, $name) = $item;

                if (trim($name) && isset($fields[$name])) {
                    $fieldsValue = $fields[$name];

                    if (is_array($fieldsValue) && !empty($fieldsValue)) {
                        $relationQuery = $this->getOwnRelation($item, false);

                        $limit = ArrayHelper::remove($fieldsValue, '{{limit}}', 0);

                        if ($limit > 0) {
                            $relationQuery->limit($limit);
                        }

                        $query_count = clone $relationQuery;

                        $sql = $query_count->createCommand()->rawSql;

                        $filteredData[$name . '_sql'] = $sql;

                        $relationValue = $relationQuery->all();

                        if (isset($relationValue) && is_array($relationValue)) {
                            $filteredData[$name] = [];

                            foreach ($relationValue as $rI => $rV) {
                                if (is_object($rV)) {
                                    if ($rV->hasMethod('translateByLang')) {
                                        $rV->translateByLang($this->currentLang);
                                    }

                                    $filteredData[$name][] = $rV->toPlainData($fieldsValue);
                                } else {
                                    Yii::$app->errorLog::RegisterErrorByErrorException(
                                        new ErrorException("Can't resolve own relation " . static::class . '::' . $name . '[' . $rI . ']')
                                    );
                                }
                            }
                        } else {
                            Yii::$app->errorLog::RegisterErrorByErrorException(
                                new ErrorException("Can't resolve own relation " . static::class . '::' . $name)
                            );
                        }
                    } else {
                        Yii::$app->errorLog::RegisterErrorByErrorException(
                            new ErrorException("Can't resolve own relation " . static::class . '::' . $name . ' - You must set value like: key => [...]')
                        );
                    }
                }
            }
        }

        return $filteredData;
    }

    public function settingsSearchField($name)
    {
        return [];
    }

    public function getWidgets()
    {
        $widgets = [];

        return $widgets;
    }

    public function beforeSaveFields()
    {
        return [];
    }

    public function forceUpdateAttributes($attributes)
    {
        static::updateAll($attributes, ['id' => $this->id]);
    }


    public static function getTypeForImage()
    {
        return false;
    }

    public static function getAllFileList($entity_id, $typesImage, $_langs = [])
    {
        if (!is_array($typesImage)) {
            $typesImage = [$typesImage];
        }

        $list = [];

        foreach ($typesImage as $typeImage) {
            if ($typeImage !== false) {
                $list = array_merge($list, File::findFiles($entity_id, $typeImage));

                $langList = Language::getList();

                $langs = [];

                foreach ($langList as $_id => $_name) {
                    $langs[] = $_name;
                }

                foreach ($langs as $_lang) {
                    if ($_lang != 'ru') {
                        /**
                         * @var \app\models\Category $category
                         */
                        $category = \app\models\Category::byAlias('image_lang_' . $_lang);

                        if ($category) {
                            $category_id = $category->id;

                            $langImages = File::findFiles($entity_id, $typeImage, $category_id);

                            if ($langImages && is_array($langImages) && count($langImages) > 0) {
                                $list = array_merge($list, $langImages);
                            }
                        }
                    }
                }
            }
        }

        return $list;
    }

    public function getFileList()
    {
        $typeImage = array_values(static::getFileTypesList()) ?: static::getTypeForImage();

        if ($typeImage !== false) {
            $langs = [];

            if (static::isTranslateAllowed()) {
                $langs = $this->getTranslatedLang();
            }

            return self::getAllFileList($this->id, $typeImage, $langs);
        }

        return [];
    }

    public static function getFileTypesList()
    {
        return [];
    }

    public function getRelationsValue()
    {
        $relations = static::getRelationList();

        $relationsValue = [];

        foreach ($relations as $_name => $_value) {
            $relationsValue[$_name] = $this->{$_name};
        }

        return $relationsValue;
    }

    public static function importModel($exportData)
    {
        if (!isset($exportData['attributes']['date_create'])) {
            $exportData['attributes']['date_create'] = date('Y-m-d h:i:s', time());
        }

        if (!isset($exportData['attributes']['date_update'])) {
            $exportData['attributes']['date_update'] = date('Y-m-d h:i:s', time());
        }
        $entityID = intval(ArrayHelper::remove($exportData['attributes'], 'id'));
        if ($entityID && static::find()->where(['id' => $entityID])->exists()) {
            if (static::updateAll($exportData['attributes'], ['id' => $entityID])) {
                /**
                 * @var static $model
                 */
                $model = static::find()->where(['id' => $entityID])->one();

                if ($model) {
                    if (static::isTranslateAllowed()) {
                        $model->importTranslatedData($exportData['translate']);
                    }

                    $model->refresh();

                    return $model;
                }
            }
        } else {
            if ($primaryKeys = static::getDb()->schema->insert(static::tableName(), $exportData['attributes'])) {
                /**
                 * @var static $model
                 */
                $model = static::find()->where($primaryKeys)->one();

                if ($model) {
                    if (static::isTranslateAllowed()) {
                        $model->importTranslatedData($exportData['translate']);
                    }

                    $model->refresh();

                    return $model;
                }
            }
        }

        return false;
    }

    public static function updateModel($id, $data)
    {
        unset($data['id']);

        $data['date_update'] = date('Y/m/d h:i:s', time());

        static::updateAll($data, ['id' => $id]);
    }

    public static function GET_DATE_UPDATE()
    {
        return date('Y/m/d H:i:s', time());
    }

    public function DATE_UPDATE()
    {
        $this->date_update = BaseModel::GET_DATE_UPDATE();
    }

    public static function getListForSettings($mapField = 'title')
    {
        $ret = [];

        foreach (static::find()->apiUndeleted()->each() as $elem) {
            $fields   = array_keys($elem->attributeLabels());
            $mapValue = $elem->id;
            if (in_array($mapField, $fields)) {
                $mapValue = $elem->{$mapField};
            }

            $ret[] = [
                'label' => $mapValue,
                'value' => $elem->id,
            ];
        }

        return $ret;
    }

    public static function getListForFrontendSettings($mapField = 'title')
    {
        $ret = [];

        foreach (static::find()->apiUndeleted()->each() as $elem) {
            $fields   = array_keys($elem->attributeLabels());
            $mapValue = $elem->id;
            if (in_array($mapField, $fields)) {
                $mapValue = $elem->{$mapField};
            }

            $ret[] = [
                'label' => $mapValue,
                'id'    => $elem->id,
            ];
        }

        return $ret;
    }

    public static function getAllAsArray()
    {
        $ret = [];

        foreach (static::find()->apiUndeleted()->each() as $elem) {
            $ret[] = $elem->asArray();
        }

        return $ret;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->hasAttribute('date_create') && $this->hasAttribute('date_update')) {
                if ($insert) {
                    $this->date_create = date('Y-m-d H:i:s');
                }

                $this->date_update = date('Y-m-d H:i:s');
            }

            return true;
        }

        return false;
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            try {
                $ownRelations = $this->getOwnRelations();

                foreach ($ownRelations as $item) {
                    list($modelClassName, $tech_name, $type_id) = $item;

                    if ($type_id == BaseModel::TYPE_RELATION_SINGLE) {
                        foreach ($modelClassName::find()->where([$tech_name => $this->id])->each() as $ownItem) {
                            $ownItem->{$tech_name} = 0;
                            $ownItem->save();
                        }
                    } elseif ($type_id == BaseModel::TYPE_RELATION_MULTIPLE) {
                        $linkList = DictionaryLinked::find()->where([
                            'type_id' => $modelClassName::getTypeForDictionaryLinked(),
                            'title'   => $tech_name,
                            'value'   => $this->id . '',
                        ])->all();

                        foreach ($linkList as $link) {
                            $link->delete();
                        }
                    }
                }
            } catch (Exception $ex) {
                Yii::$app->errorLog::RegisterErrorByErrorException($ex);

                return false;
            } finally {
                return true;
            }
        }

        return false;
    }

    public function canDelete()
    {
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();

        if ($this->hasMethod('linkedFieldsSetup')) {
            $setup = $this->linkedFieldsSetup();

            foreach ($setup as $key => $val) {
                $this->linkedFields[$key] = $this->{$key};
            }
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->trigger(static::EVENT_AFTER_SAVE);

        parent::afterSave($insert, $changedAttributes);

        $linkedSetup = $this->hasMethod('linkedFieldsSetup') ? $this->linkedFieldsSetup() : [];

        foreach ($this->linkedFields as $name => $value) {
            if (isset($linkedSetup[$name])) {
                list($linkClass, $linkField, $valueField) = $linkedSetup[$name];

                $linkClass::setLink($this->id, $linkField, $valueField, $value);
            } else {
                DictionaryLinked::setLink(
                    12,
                    $this->id,
                    static::getTypeForDictionaryLinked(),
                    $name,
                    $value
                );
            }
        }

        if (!empty($this->slugFields()) && $this->hasAttribute('seo_alias')) {
            $needSlug = false;
            if ($insert) {
                $needSlug = static::$slugAfterInsert;
            } else {
                if (static::$slugAfterUpdate) {
                    foreach ($this->slugFields() as $field) {
                        if (isset($changedAttributes[$field]) && $changedAttributes[$field] != $this->{$field}) {
                            $needSlug = true;

                            break;
                        }
                    }
                }
            }

            if ($needSlug) {
                static::updateAll(['seo_alias' => $this->slug], ['id' => $this->id]);
            }
        }

        $this->refresh();
    }

    public function getFilesFields()
    {
        $data = [];

        foreach (static::getFileTypesList() as $fileField => $fileType) {
            $data[$fileField] = ArrayHelper::toArray(
                File::findFiles($this->id, $fileType),
                [File::class => [
                    'id',
                    'url',
                    'base_name',
                ]]
            );
        }

        return $data;
    }

    public static $slugAfterInsert = true;
    public static $slugAfterUpdate = true;

    public function slugFields(): array
    {
        return [];
    }

    public function getSlug(): string
    {
        return $this->generateSlug();
    }

    public function generateSlug($customValue = null): string
    {
        $baseList = [];

        if (!empty($customValue)) {
            if (is_string($customValue)) {
                $baseList = [
                    trim($customValue),
                ];
            } elseif (is_array($customValue)) {
                foreach ($customValue as $value) {
                    $baseList[] = trim($value);
                }
            }
        } else {
            foreach ($this->slugFields() as $field) {
                $baseList[] = trim($this->{$field});
            }
        }

        $base = trim(implode(' ', $baseList));

        // $seoAlias = Inflector::transliterate($base, "Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();");
        $seoAlias = Inflector::transliterate($base, 'Russian-Latin/BGN');
        $seoAlias = Inflector::slug($seoAlias);

        return trim(preg_replace('/-+/', '-', $seoAlias), '-');
    }

    public function getIsInactive(): bool
    {
        if ($this->hasAttribute('status_id') && $this->status_id == static::STATUS_DEACTIVE) {
            return true;
        }

        if ($this->hasAttribute('activity') && $this->activity == static::STATUS_DEACTIVE) {
            return true;
        }

        return false;
    }

    public function getIsDeleted(): bool
    {
        if ($this->hasAttribute('deleted') && $this->deleted) {
            return true;
        }

        return false;
    }

    public function getIsEnabled(): bool
    {
        return !$this->isDeleted && !$this->isInActive;
    }

    /**
     * @var Field[]|null
     */
    protected $_configuredFields = null;

    public function init()
    {
        $this->_configuredFields = static::configureFields();

        parent::init();
    }

    /**
     * @return Field[]|null
     */
    public static function configureFields()
    {
        return null;
    }

    protected function resolveFieldRelation($name, $value)
    {
        if ($value instanceof ActiveQueryInterface) {
            $this->setRelationDependencies($name, $value);
            return $this->_related[$name] = $value->findFor($name, $this);
        }

        return $value;
    }

    public function __get($name)
    {
        $_configuredFields = static::configureFields();

        if ($_configuredFields) {
            foreach ($_configuredFields as $fieldName => $fieldConfig) {
                if ($name === $fieldName) {
                    if ($fieldConfig->link) {
                        return $this->getLinkedField($name);
                    }

                    break;
                }
            }
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        $_configuredFields = static::configureFields();

        if ($_configuredFields) {
            foreach ($_configuredFields as $fieldName => $fieldConfig) {
                if ($name === $fieldName) {
                    if ($fieldConfig->link) {
                        $this->setLinkedField($name, $value);

                        return;
                    }

                    break;
                }
            }
        }

        parent::__set($name, $value);
    }

    public static function getMapRelationList()
    {
        $_configuredFields = static::configureFields();

        if (!$_configuredFields) {
            return [];
        }

        $relations = [];

        foreach ($_configuredFields as $fieldName => $fieldConfig) {
            if ($fieldConfig->relation) {
                $relations[$fieldName] = $fieldConfig->relation;
            } elseif ($fieldConfig->ownRelation) {
                $relations[$fieldName] = $fieldConfig->ownRelation;
            }
        }

        return $relations;
    }

    public static function getRelationList()
    {
        $_configuredFields = static::configureFields();

        if (!$_configuredFields) {
            return [];
        }

        $relations = [];

        foreach ($_configuredFields as $fieldName => $fieldConfig) {
            if ($fieldConfig->relationModelType) {
                $relations[$fieldName] = $fieldConfig->relationModelType;
            }
        }

        return $relations;
    }

    public function getOwnRelations()
    {
        $_configuredFields = static::configureFields();

        if (!$_configuredFields) {
            return [];
        }

        $ownRelations = [];

        foreach ($_configuredFields as $fieldName => $fieldConfig) {
            if ($fieldConfig->ownLink) {
                $ownRelations[] = $fieldConfig->ownLink;
            }
        }

        return $ownRelations;
    }

    public function behaviors()
    {
        if (!$this->_configuredFields) {
            return [];
        }

        $typeCasting = [];

        foreach ($this->_configuredFields as $fieldName => $fieldConfig) {
            if ($fieldConfig->typeCast) {
                $typeCasting[$fieldName] = $fieldConfig->typeCast;
            }
        }

        if (empty($typeCasting)) {
            return [];
        }

        return [
            'typecast' => [
                'class'                 => AttributeTypecastBehavior::class,
                'attributeTypes'        => $typeCasting,
                'typecastAfterValidate' => true,
                'typecastBeforeSave'    => true,
                'typecastAfterFind'     => true,
            ],
        ];
    }

    public function linkedFieldsSetup(): array
    {
        if (!$this->_configuredFields) {
            return [];
        }

        $links = [];

        foreach ($this->_configuredFields as $fieldName => $fieldConfig) {
            if ($fieldConfig->link) {
                $links[$fieldName] = $fieldConfig->link;
            }
        }

        return $links;
    }

    public function customFields()
    {
        if (!$this->_configuredFields) {
            return [];
        }

        $customFields = [];

        foreach ($this->_configuredFields as $fieldName => $fieldConfig) {
            if ($fieldConfig->isCustom) {
                $customFields[] = $fieldName;
            }
        }

        return $customFields;
    }

    public function compositeFields()
    {
        if (!$this->_configuredFields) {
            return [];
        }

        $compositeFields = [];

        foreach ($this->_configuredFields as $fieldName => $fieldConfig) {
            if ($fieldConfig->isComposite) {
                $compositeFields[] = $fieldName;
            }
        }

        return $compositeFields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        if (!$this->_configuredFields) {
            return [];
        }

        $attributeLabels = [];

        foreach ($this->_configuredFields as $fieldName => $fieldConfig) {
            $attributeLabels[$fieldName] = $fieldConfig->title;
        }

        return $attributeLabels;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        if (!$this->_configuredFields) {
            return [];
        }

        $rules = [];

        foreach ($this->_configuredFields as $fieldName => $fieldConfig) {
            foreach ($fieldConfig->rules as $rule) {
                if (is_array($rule)) {
                    $rules[] = array_merge([
                        [$fieldName],
                    ], $rule);
                } else {
                    $rules[] = [
                        [$fieldName], $rule,
                    ];
                }
            }
        }

        return $rules;
    }

    public function getUpdatedAt(): DateTime
    {
        if (!$this->hasAttribute('date_update')) {
            return new DateTime('now');
        }

        return DateTime::createFromFormat('Y-m-d H:i:s', $this->date_update) ?: new DateTime('now');
    }
}