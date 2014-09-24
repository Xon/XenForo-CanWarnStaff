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

        if (!empty($post['is_admin']) || !empty($post['is_moderator']))
        {
            $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

            return ($viewingUser['user_id'] && XenForo_Permission::hasContentPermission($nodePermissions, 'warn'));
        }
        return false;
    }
}