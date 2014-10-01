<?php

class SV_Helper_Permissions
{
    public static function LoadPermissionsForUser(XenForo_Model_User $UserModel,  array &$viewingUser, array &$user)
    {    
        $permissions = array();
        if ($viewingUser['user_id'] == $user['user_id'] && isset($viewingUser['permissions']) && $viewingUser['permissions'])
        {
            $permissions = $viewingUser['permissions'];
        }
        else if (isset($user['permissions']) && $user['permissions'])
        {
            $permissions = $user['permissions'];
        }

        if (empty($permissions))
        {
            $user_w_perms = $UserModel->setPermissionsFromUserId($user, $user['user_id']);

            if (!$user_w_perms['global_permission_cache'])
            {
                // force a rebuild if we don't have the perm cache
                $perms = XenForo_Model::create('XenForo_Model_Permission')->rebuildPermissionCombinationById(
                    $user_w_perms['permission_combination_id']
                );
                $permissions = $perms ? $perms : array();
            }
            else
            {
                $permissions = XenForo_Permission::unserializePermissions($user_w_perms['global_permission_cache']);
            }
        }
        return $permissions;
    }

    protected static $_permissionCache = array();

    public static function CheckGlobalPermission(XenForo_Model_User $UserModel, array &$viewingUser, array &$user, $key, $permission)
    {  
        if (!isset(self::$_permissionCache[$user['user_id']]))
        {
            self::$_permissionCache[$user['user_id']] = $permissions = SV_Helper_Permissions::LoadPermissionsForUser($UserModel, $viewingUser, $user);
        }
        else
        {
            $permissions = self::$_permissionCache[$user['user_id']];
        }
        return XenForo_Permission::hasPermission($permissions, $key, $permission);
    }
}