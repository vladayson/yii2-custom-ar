<?php

namespace vladayson\AccessRules;

use app\models\Users;
use vladayson\AccessRules\models\Permissions;
use yii\base\Action;
use yii\base\Behavior;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use vladayson\AccessRules\models\Roles;
use yii\base\Controller;
use yii\base\Module;
use yii\helpers\StringHelper;
use yii\web\ForbiddenHttpException;

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
    public function beforeAction(Action $action)
    {
        if ($this->rules) {
            $user = \Yii::$app->user;
            $roleUser = Roles::getUserRole($user->id);

            foreach ($this->rules as $ruleConfig) {
                $role = key($ruleConfig);

                if ($role !== $roleUser && mb_strlen($role) > 3)
                {
                    continue;
                }

                $ruleClass = AccessRule::class;
                /** @var AccessRule $rule */
                $rule = \Yii::createObject($ruleClass);

                /** @var $result (bool - true/false || 'auth') */
                $result = $rule->checkAccess($action, $ruleConfig);

                if ($result === true)
                {
                    return true;
                }
            }

            $this->denyAccess($user);
        }
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

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param User|false $user the current user or boolean `false` in case of detached User component
     * @throws ForbiddenHttpException if the user is already logged in or in case of detached User component.
     */
    protected function denyAccess($user)
    {
        if ($user !== false && $user->getIsGuest()) {
            $user->loginRequired();
        } else {
            throw new ForbiddenHttpException(\Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }
}
