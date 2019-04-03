<?php

namespace vladayson\AccessRules\models;

use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use phpDocumentor\Reflection\Types\Object_;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class BaseModel.
 *
 * @package vladayson\AccessRules
 */
class BaseModel extends ActiveRecord
{
    public $tableName = false;

    const a = '';

    public function behaviors()
    {
        return [
            'saveRelations' => [
                'class'     => SaveRelationsBehavior::class,
                'relations' => [],
            ],
        ];
    }

    /**
     * @param array $attributesToCheck
     *
     * @return BaseModel
     */
    public function createOrReturn($attributesToCheck = ['name'])
    {
        $class = get_called_class();
        /** @var ActiveQuery $obj */
        $obj = (new $class())->find();
        foreach ($attributesToCheck as $attributeName) {
            $obj = $obj->andWhere([
                $attributeName => $this->{$attributeName}
            ]);
        }
        $obj = $obj->one();
        if ($obj) {
            $this->{$roles->tableSchema->primaryKey[0] ?? 'id'} = $obj->getPrimaryKey();
        } else {
            $obj = new $class($this->getAttributes());
        }

        $obj->save();

        return $obj;
    }

    /**
     * @param Roles | Permissions $child
     */
    public function addChild($child)
    {
        $child->parent_id = $this->id;
        $child->save();
    }

    /**
     * @param $name
     *
     * @return array | ActiveRecord | null
     */
    public static function findOneByName($name)
    {
        return self::find()->andWhere([Roles::tableName() . '.name' => $name])->one();
    }
}
