<?php

namespace vladayson\AccessRules;

use vladayson\AccessRules\models\Roles;
use yii\base\Action;
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
     * @var array
     */
    public $except = [];

    /**
     * @var bool
     */
    public $allow = true;

    /**
     * @param $action
     *
     * @return bool
     */
    public function checkAccess(Action $action)
    {
        $hasUser = false;
        $userId = !\Yii::$app->user->isGuest ? \Yii::$app->user->id : 0;

        if (in_array('*', $this->roles))
        {
            return $this->allow;
        }

        if (in_array($action->id, $this->except))
        {
            return $this->allow;
        }

        if (in_array('@', $this->roles) && $userId === 0)
        {
            return 'auth';
        }

        if (in_array('@', $this->roles) && $userId > 0)
        {
            return $this->allow;
        }

        foreach ($this->roles as $roleName) {
            $usersIds = Roles::getUsersByRole($roleName);
            if (in_array($userId, $usersIds)) {
                $hasUser = true;
                break;
            }
        }

        if (empty($this->actions) && $hasUser)
        {
            return $this->allow;
        }

        if (
            in_array($action->id, $this->actions) &&
            $hasUser
        ) {
            return $this->allow;
        }

        if (
            !in_array($action->id, $this->actions) &&
            $hasUser
        ) {
            return true;
        }

        return false;
    }
}
