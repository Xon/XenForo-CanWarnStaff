<?php
class SV_CanWarnStaff_XenForo_Model_User extends XFCP_SV_CanWarnStaff_XenForo_Model_User
{
    public function _getPermissionsForUser(array &$viewingUser, array &$user)
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

    protected $_permissionCache = array();

    public function CheckGlobalPermission(array &$viewingUser, array &$user, $key, $permission)
    {
        if (!isset($this->_permissionCache[$user['user_id']]))
        {
            $this->_permissionCache[$user['user_id']] = $this->_getPermissionsForUser($viewingUser, $user);
        }
        return XenForo_Permission::hasPermission($this->_permissionCache[$user['user_id']], $key, $permission);
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

        if ($this->CheckGlobalPermission($viewingUser, $user, 'general', 'prevent_warning' ))
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