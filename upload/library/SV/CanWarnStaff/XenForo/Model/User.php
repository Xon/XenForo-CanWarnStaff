<?php
class SV_CanWarnStaff_XenForo_Model_User extends XFCP_SV_CanWarnStaff_XenForo_Model_User
{
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
           
        if (SV_Helper_Permissions::CheckGlobalPermission($this, $viewingUser, $user, 'general', 'prevent_warning' ))
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