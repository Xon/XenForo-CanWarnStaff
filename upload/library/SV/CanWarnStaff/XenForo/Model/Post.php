<?php
class SV_CanWarnStaff_XenForo_Model_Post extends XFCP_SV_CanWarnStaff_XenForo_Model_Post
{
    public function getPostsInThread($threadId, array $fetchOptions = array())
    {
        $posts = parent::getPostsInThread($threadId, $fetchOptions);

        if ($posts)
        {
            $permissionCombinations = array_unique(XenForo_Application::arrayColumn($posts, 'permission_combination_id'));
            $this->_getUserModel()->_preloadGlobalPermissions($permissionCombinations);
        }

        return $posts;
    }

    public function canWarnPost(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        if (empty($post['user_id']))
        {
            return false;
        }

        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (empty($viewingUser['user_id']))
        {
            return false;
        }

        if ($this->_getUserModel()->_CheckGlobalPermission($viewingUser, $post, 'forum', 'prevent_warning' ))
        {
            return false;
        }

        $canWarn = parent::canWarnPost($post, $thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser);

        if ($canWarn)
        {
            return true;
        }

        if ($post['warning_id'] || empty($post['user_id']) || empty($viewingUser['user_id']))
        {
            return false;
        }

        if (!empty($post['is_admin']) && $post['is_admin'])
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_admin');
        }

        if (!empty($post['is_moderator']) && $post['is_moderator'])
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_mod');
        }

        return false;
    }
}