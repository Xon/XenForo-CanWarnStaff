<?php
class SV_CanWarnStaff_XenForo_Model_Post extends XFCP_SV_CanWarnStaff_XenForo_Model_Post
{
    public function canWarnPost(array $post, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        $canWarn = parent::canWarnPost($post, $thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser);
        if ($canWarn)
        {
            return true;
        }

        if ($post['warning_id'] || empty($post['user_id']))
        {
            return false;
        }
        
        if (!empty($post['is_admin']) && $post['is_admin'])
        {
            $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_admin'));
        }
        
        if (!empty($post['is_moderator']) && $post['is_moderator'])
        {
            $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_mod'));
        }
        
        return false;
    }
}