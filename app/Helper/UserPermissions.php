<?php

namespace App\Helper;

class UserPermissions
{
    /**
     * @param array $userRights
     * @param array $groupRights
     *
     * @return bool
     */
    public static function isCurrentUserHaveRights($userRights = array(), $groupRights = array())
    {
        $userId      = (int)$_SESSION['user'];
        $userGroupId = (int)$_SESSION['tip'];
        
        if (in_array($userId, $userRights)) {
            return true;
        }
        
        if (empty($groupRights) && empty($userRights)) {
            return true;
        }
        
        if (in_array($userGroupId, $groupRights)) {
            return true;
        }
        
        return false;
    }
}
