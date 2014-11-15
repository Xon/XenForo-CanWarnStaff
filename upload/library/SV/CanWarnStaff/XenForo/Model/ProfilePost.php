<?php
class SV_CanWarnStaff_XenForo_Model_ProfilePost extends XFCP_SV_CanWarnStaff_XenForo_Model_ProfilePost
{
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
               
        if (SV_Helper_Permissions::CheckGlobalPermission($this->_getUserModel(), $viewingUser, $profilePost, 'profilePost', 'prevent_warning' ))
        {
            return false;
        } 
        
        $canWarn = parent::canWarnProfilePost($profilePost, $user, $errorPhraseKey, $viewingUser);

        if ($canWarn)
        {
            return true;
        }
       
        if (!empty($profilePost['is_admin']) && $profilePost['is_admin'])
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_admin');
        }
        
        if (!empty($profilePost['is_moderator']) && $profilePost['is_moderator'])
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_mod');
        }
        
        return false;
    }
}