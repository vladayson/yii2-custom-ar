<?php

namespace vladayson\AccessRules;

use app\models\Users;
use vladayson\AccessRules\models\Permissions;
use yii\base\Behavior;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use vladayson\AccessRules\models\Roles;
use yii\base\Controller;
use yii\base\Module;
use yii\helpers\StringHelper;

/**
 * Class AccessRules.
 *
 * @package vladayson\AccessRules
 */
class AccessControl extends Behavior
{
    /**
     * @var array
     */
    public $rules = [];

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
    public function addChildRole(Roles $role, Roles $childRole)
    {
        $role->roles = ArrayHelper::merge($role->roles, [$childRole]);
        return $role->save();
    }

    /**
     * @param        $name
     * @param string $description
     *
     * @return Permissions
     */
    public function addPermission($name, $description = '')
    {
        return (new Permissions([
            'name' => $name,
            'description' => $description
        ]))->createOrReturn();
    }

    /**
     * @param Permissions $permission
     * @param Permissions $childPermission
     *
     * @return bool
     */
    public function addChildPermission(Permissions $permission, Permissions $childPermission)
    {
        $permission->permissions = ArrayHelper::merge($permission->permissions, [$childPermission]);
        return $permission->save();
    }

    /**
     * @param Roles $role
     * @param Permissions[] $permissions
     */
    public function assignRolePermissions(Roles $role, array $permissions)
    {
        $role->permissions = ArrayHelper::merge($role->permissions, $permissions);
        return $role->save();
    }

    /**
     * @param       $user
     * @param Roles $role
     */
    public function assignUserRole($user, Roles $role)
    {
        $user->roles = ArrayHelper::merge($user->roles, [$role]);
        return $user->save();
    }

    /**
     * {@inheritdoc}
     */
    public function attach($owner)
    {
        $this->owner = $owner;
        $owner->on(Controller::EVENT_BEFORE_ACTION, [$this, 'beforeFilter']);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if ($this->owner) {
            $this->owner->off(Controller::EVENT_BEFORE_ACTION, [$this, 'beforeFilter']);
            $this->owner->off(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter']);
            $this->owner = null;
        }
    }

    /**
     * @param ActionEvent $event
     */
    public function beforeFilter($event)
    {
        $event->isValid = $this->beforeAction($event->action);
        if ($event->isValid) {
            // call afterFilter only if beforeFilter succeeds
            // beforeFilter and afterFilter should be properly nested
            $this->owner->on(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter'], null, false);
        } else {
            $event->handled = true;
        }
    }

    /**
     * @param ActionEvent $event
     */
    public function afterFilter($event)
    {
        $event->result = $this->afterAction($event->action, $event->result);
        $this->owner->off(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter']);
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return bool whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        $userId = \Yii::$app->user->getId();
        if ($this->rules) {
            foreach ($this->rules as $ruleConfig) {
                if (empty($ruleConfig['class'])) {
                    $ruleConfig['class'] = AccessRule::class;
                }
                /** @var AccessRule $rule */
                $rule = \Yii::createObject($ruleConfig);
                if ($rule->checkAccess($action)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * This method is invoked right after an action is executed.
     * You may override this method to do some postprocessing for the action.
     * @param Action $action the action just executed.
     * @param mixed $result the action execution result
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        return $result;
    }

    /**
     * Returns an action ID by converting [[Action::$uniqueId]] into an ID relative to the module.
     * @param Action $action
     * @return string
     * @since 2.0.7
     */
    protected function getActionId($action)
    {
        if ($this->owner instanceof Module) {
            $mid = $this->owner->getUniqueId();
            $id = $action->getUniqueId();
            if ($mid !== '' && strpos($id, $mid) === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }

        return $id;
    }
}
