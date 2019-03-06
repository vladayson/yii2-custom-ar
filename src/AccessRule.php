<?php

namespace vladayson\AccessRules;

use vladayson\AccessRules\models\Roles;
use yii\base\Component;

/**
 * Class AccessRule.
 *
 * @package vladayson\AccessRules
 */
class AccessRule extends Component
{
    /**
     * @var array
     */
    public $roles = [];

    /**
     * @var array
     */
    public $actions = [];

    /**
     * @var bool
     */
    public $allow = true;

    /**
     * @param $action
     *
     * @return bool
     */
    public function checkAccess($action)
    {
        $hasUser = false;
        $userId = @\Yii::$app->user->id ?? 0;

        if (
            empty($this->actions) ||
            (
                in_array('@', $this->roles) &&
                $userId > 0
            )
        ) {
            return $this->allow;
        }
        foreach ($this->roles as $roleName) {
            $usersIds = Roles::getUsersByRole($roleName);
            if (in_array($userId, $usersIds)) {
                $hasUser = true;
                break;
            }
        }
        if (!$hasUser) {
            return $this->allow;
        }
        if (
            in_array($action, $this->actions) &&
            $hasUser
        ) {
            return $this->allow;
        }
        if (
            !in_array($action, $this->actions) &&
            in_array('*', $this->actions) &&
            $hasUser
        ) {
            return $this->allow;
        }

        return false;
    }
}
