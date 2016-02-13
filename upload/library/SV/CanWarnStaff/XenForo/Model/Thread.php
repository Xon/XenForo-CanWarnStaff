<?php
class SV_CanWarnStaff_XenForo_Model_Thread extends XFCP_SV_CanWarnStaff_XenForo_Model_Thread
{
    public function canReplyBanUserFromThread(array $user = null, array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
    {
        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        $canReplyBan = parent::canReplyBanUserFromThread($user, $thread, $forum, $errorPhraseKey, $nodePermissions, $viewingUser);

        if ($canReplyBan)
        {
            return true;
        }

        if (!empty($user))
        {
            if (!empty($user['is_admin']))
            {
                return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_admin');
            }

            if (!empty($user['is_moderator']))
            {
                return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_mod');
            }

            if (!empty($user['is_staff']))
            {
                return true;
            }
        }

        return false;
    }

    public function deleteThreadReplyBan(array $thread, array $user)
    {
        $this->standardizeViewingUserReference($viewingUser);

        if (!empty($user['is_moderator']) && !XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_mod'))
        {
            return false;
        }

        if (!empty($user['is_admin']) && !XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_admin'))
        {
            return false;
        }

        return parent::deleteThreadReplyBan($thread, $user);
    }

    protected function _getUserModel()
    {
        return $this->getModelFromCache('XenForo_Model_User');
    }
}