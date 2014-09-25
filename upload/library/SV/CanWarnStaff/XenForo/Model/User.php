<?php
class SV_CanWarnStaff_XenForo_Model_User extends XFCP_SV_CanWarnStaff_XenForo_Model_User
{
	public function canWarnUser(array $user, &$errorPhraseKey = '', array $viewingUser = null)
	{
        $canWarn = parent::canWarnUser($user, $errorPhraseKey, $viewingUser);
        if ($canWarn)
        {
            return true;
        }

        if (empty($user['user_id']))
        {
        
            return false;
        }
       
        if (!empty($user['is_admin']) && $user['is_admin'])
        {
            $this->standardizeViewingUserReference($viewingUser);

            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_admin'));
        }        
       
        if (!empty($user['is_moderator']) && $user['is_moderator'])
        {
            $this->standardizeViewingUserReference($viewingUser);

            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'warn_mod'));
        }
        
        return false;
	}
}