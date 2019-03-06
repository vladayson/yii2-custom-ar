<?php

namespace vladayson\AccessRules;

use vladayson\AccessRules\models\Permissions;

/**
 * Class AccessChecker.
 *
 * @package vladayson\AccessRules
 */
class AccessChecker
{
    /**
     * @param       $userId
     * @param       $permissionName
     * @param array $params
     *
     * @return bool
     */
    public static function checkAccess($userId, $permissionName, $params = [])
    {
        return (bool)Permissions::getUserPermissions($userId, $permissionName);
    }
}
