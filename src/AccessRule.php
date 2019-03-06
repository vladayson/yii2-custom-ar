<?php

namespace vladayson\AccessRules;

use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class AccessRules.
 *
 * @package vladayson\AccessRules
 */
//class AccessRule extends Component
class AccessRule
{
    public $db = 'db';

    /**
     * @param        $name
     * @param string $description
     *
     * @return Roles
     */
    public function addRole($name, $description = '')
    {
        return (new Roles([
            'name' => $name,
            'description' => $description
        ]))->createOrReturn();
    }

    /**
     * @param Roles $role
     * @param Roles $childRole
     *
     * @return bool
     */
    public function addChild(Roles $role, Roles $childRole)
    {
        $role->roles = ArrayHelper::merge($role->roles, [$childRole]);
        return $role->save();
    }
}
