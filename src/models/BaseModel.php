<?php

namespace vladayson\AccessRules;

use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Class BaseModel.
 *
 * @package vladayson\AccessRules
 */
class BaseModel extends ActiveRecord
{
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
     * @return Roles
     */
    public function createOrReturn($attributesToCheck = ['name'])
    {
        /** @var ActiveQuery $obj */
        $obj = (new self())->find();
        foreach ($attributesToCheck as $attributeName) {
            $obj = $obj->andWhere([
                $attributeName => $this->{$attributeName}
            ]);
        }
        $obj = $obj->one();
        if ($obj) {
            $this->{$this->getPrimaryKey()} = $obj->{$obj->getPrimaryKey()};
        }

        $obj->save();

        return $obj;
    }
}
