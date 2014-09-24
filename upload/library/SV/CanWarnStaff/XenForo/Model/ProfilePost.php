<?php
class SV_CanWarnStaff_XenForo_Model_ProfilePost extends XFCP_SV_CanWarnStaff_XenForo_Model_ProfilePost
{
    public function canWarnProfilePost(array $profilePost, array $user, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $canWarn = parent::canWarnProfilePost($profilePost,$user, $errorPhraseKey, $viewingUser);
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
            // allow moderators & admins if they have the right permission
            $this->standardizeViewingUserReference($viewingUser);

            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'profilePost', 'warn'));
        }
        return false;
    }
}