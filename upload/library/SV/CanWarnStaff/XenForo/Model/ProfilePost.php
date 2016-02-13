<?php
class SV_CanWarnStaff_XenForo_Model_ProfilePost extends XFCP_SV_CanWarnStaff_XenForo_Model_ProfilePost
{
    public function getPermissionBasedProfilePostConditions(array $user, array $viewingUser = null)
    {
        if (isset($user['permission_combination_id']))
        {
            $this->standardizeViewingUserReference($viewingUser);
            $this->_getUserModel()->_CheckGlobalPermission($viewingUser, $user, '', '');
        }
        return parent::getPermissionBasedProfilePostConditions($user, $viewingUser);
    }


    public function getProfilePostsByIds(array $messageIds, array $fetchOptions = array())
    {
        $profilePosts = parent::getProfilePostsByIds($messageIds, $fetchOptions);

        if ($profilePosts)
        {
            $permissionCombinations = array_unique(XenForo_Application::arrayColumn($profilePosts, 'permission_combination_id'));
            $this->_getUserModel()->_preloadGlobalPermissions($permissionCombinations);
        }

        return $profilePosts;
    }

    public function getProfilePostsForUserId($userId, array $conditions = array(), array $fetchOptions = array())
    {
        $profilePosts = parent::getProfilePostsForUserId($userId, $conditions, $fetchOptions);

        if ($profilePosts)
        {
            $permissionCombinations = array_unique(XenForo_Application::arrayColumn($profilePosts, 'permission_combination_id'));
            $this->_getUserModel()->_preloadGlobalPermissions($permissionCombinations);
        }

        return $profilePosts;
    }

    public function getLatestProfilePosts(array $conditions = array(), array $fetchOptions = array())
    {
        $profilePosts = parent::getLatestProfilePosts($conditions, $fetchOptions);

        if ($profilePosts)
        {
            $permissionCombinations = array_unique(XenForo_Application::arrayColumn($profilePosts, 'permission_combination_id'));
            $this->_getUserModel()->_preloadGlobalPermissions($permissionCombinations);
        }

        return $profilePosts;
    }


    public function canWarnProfilePost(array $profilePost, array $user, &$errorPhraseKey = '', array $viewingUser = null)
    {
        if ($profilePost['warning_id'] || empty($profilePost['user_id']))
        {
            return false;
        }

        $this->standardizeViewingUserReference($viewingUser);

        if (empty($viewingUser['user_id']))
        {
            return false;
        }

        if ($this->_getUserModel()->_CheckGlobalPermission($viewingUser, $profilePost, 'profilePost', 'prevent_warning' ))
        {
            return false;
        }

        $canWarn = parent::canWarnProfilePost($profilePost, $user, $errorPhraseKey, $viewingUser);

        if ($canWarn)
        {
            return true;
        }

        if (!empty($profilePost['is_admin']))
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_admin');
        }

        if (!empty($profilePost['is_moderator']))
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_mod');
        }

        return false;
    }
}