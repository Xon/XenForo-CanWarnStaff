<?php
class SV_CanWarnStaff_XenForo_Model_User extends XFCP_SV_CanWarnStaff_XenForo_Model_User
{
    public function __getPermissionsForUser(array &$viewingUser, array &$user)
    {
        $permissions = array();
        if ($viewingUser['user_id'] == $user['user_id'] && !empty($viewingUser['permissions']))
        {
            $permissions = $viewingUser['permissions'];
        }
        else if (!empty($user['permissions']))
        {
            $permissions = $user['permissions'];
        }
        else if (!empty($user['global_permission_cache']))
        {
            $permissions = XenForo_Permission::unserializePermissions($user['global_permission_cache']);
        }

        if (empty($permissions))
        {
            $permUser = $this->_getDb()->fetchRow('
                SELECT user.permission_combination_id, permission_combination.cache_value AS global_permission_cache
                FROM xf_user user
                LEFT JOIN xf_permission_combination AS permission_combination ON
                            (permission_combination.permission_combination_id = user.permission_combination_id)
                WHERE user.user_id = ?
            ', $user['user_id']);

            $user_w_perms = array();
            if ($permUser)
            {
                $user_w_perms['permission_combination_id'] = $permUser['permission_combination_id'];
                $user_w_perms['global_permission_cache'] = $permUser['global_permission_cache'];
            }

            if(!empty($user_w_perms['global_permission_cache']))
            {
                $permissions = XenForo_Permission::unserializePermissions($user_w_perms['global_permission_cache']);
            }
            else if (isset($user_w_perms['permission_combination_id']))
            {
                // force a rebuild if we don't have the perm cache
                $perms = $this->getModelFromCache('XenForo_Model_Permission')->rebuildPermissionCombinationById(
                    $user_w_perms['permission_combination_id']
                );
                $permissions = $perms ? $perms : array();
            }
        }
        return $permissions;
    }

    protected static $__permissionCache = array();

    public function _preloadGlobalPermissions(array $permissionCombinations = null)
    {
        if (empty($permissionCombinations))
        {
            return;
        }

        $viewingUser = XenForo_Visitor::getInstance()->toArray();
        if (isset($viewingUser['permission_combination_id']) && !isset(self::$__permissionCache[$viewingUser['permission_combination_id']]))
        {
            self::$__permissionCache[$viewingUser['permission_combination_id']] = $viewingUser['permissions'];
        }

        $toLoad = array_diff($permissionCombinations, array_keys(self::$__permissionCache));
        if ($toLoad)
        {
            $list = implode(',', array_fill(0, count($toLoad), '?'));
            $permUsers = $this->_getDb()->fetchAll('
                SELECT permission_combination_id, cache_value
                FROM xf_permission_combination AS permission_combination
                WHERE permission_combination_id in ('.$list.')
            ', $toLoad);

            if ($permUsers)
            foreach($permUsers as &$permUser)
            {
                self::$__permissionCache[$permUser['permission_combination_id']] = XenForo_Permission::unserializePermissions($permUser['cache_value']);
            }
        }
    }

    public function _CheckGlobalPermission(array &$viewingUser, array &$user, $key, $permission)
    {
        if (!isset($user['permission_combination_id']))
        {
            return false;
        }

        if (!isset(self::$__permissionCache[$user['permission_combination_id']]))
        {
            self::$__permissionCache[$user['permission_combination_id']] = $this->__getPermissionsForUser($viewingUser, $user);
        }
        return XenForo_Permission::hasPermission(self::$__permissionCache[$user['permission_combination_id']], $key, $permission);
    }

    public function canReportUser(array $user, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $canReport = parent::canReportUser($user, $errorPhraseKey, $viewingUser);
        if ($canReport)
        {
            return $canReport;
        }

        if ($user['is_staff'])
        {
            $canReport = $this->canReportContent($errorPhraseKey, $viewingUser);
        }

        return $canReport;
    }

    public function canWarnUser(array $user, &$errorPhraseKey = '', array $viewingUser = null)
    {
        if (empty($user['user_id']))
        {
            return false;
        }

        $this->standardizeViewingUserReference($viewingUser);

        if (empty($viewingUser['user_id']))
        {
            return false;
        }

        if ($this->_CheckGlobalPermission($viewingUser, $user, 'general', 'prevent_warning' ))
        {
            return false;
        }

        $canWarn = parent::canWarnUser($user, $errorPhraseKey, $viewingUser);

        if ($canWarn)
        {
            return true;
        }

        if (!empty($user['is_admin']) && $user['is_admin'])
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_admin');
        }

        if (!empty($user['is_moderator']) && $user['is_moderator'])
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_mod');
        }

        return false;
    }
}