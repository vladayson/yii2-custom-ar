<?php

namespace vladayson\AccessRules;

use vladayson\AccessRules\models\Roles;
use Yii;
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
    public function checkAccess(Action $action, array $roles)
    {
        $role = key($roles);
        $actions = $roles[$role];

        $user = \Yii::$app->user;

        if ($role === '*')
        {
            return true;
        }

        if ($role === '@' && $user->isGuest)
        {
            return Yii::$app->getResponse()->redirect(\Yii::$app->user->loginUrl)->send();
        }

        if ($role === '@' && !$user->isGuest)
        {
            return true;
        }

        $roleUser = Roles::getUserRole($user->id);

        if ($role !== $roleUser)
        {
            return false;
        }

        if (in_array('*', $actions))
        {
            return true;
        }

        if (!in_array($action->id, $actions))
        {
            return false;
        }

        return true;
    }
}
