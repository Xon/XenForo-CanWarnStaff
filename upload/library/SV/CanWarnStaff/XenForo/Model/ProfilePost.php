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

        if ($profilePost['warning_id'] || empty($profilePost['user_id']))
        {
            return false;
        }
       
        if (!empty($profilePost['is_admin']) && $profilePost['is_admin'])
        {
            // allow moderators & admins if they have the right permission
            $this->standardizeViewingUserReference($viewingUser);

            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_admin'));
        }
        
        if (!empty($profilePost['is_moderator']) && $profilePost['is_moderator'])
        {
            // allow moderators & admins if they have the right permission
            $this->standardizeViewingUserReference($viewingUser);

            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_mod'));
        }
        
        return false;
    }
}