<?php
class SV_CanWarnStaff_XenForo_Model_Warning extends XFCP_SV_CanWarnStaff_XenForo_Model_Warning
{   
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}
    
	public function canDeleteWarning(array $warning, &$errorPhraseKey = '', array $viewingUser = null)
	{
        $this->standardizeViewingUserReference($viewingUser);
        
        $canDelete = parent::canDeleteWarning($warning, $errorPhraseKey, $viewingUser);

        if (!empty($warning['is_moderator']) && $warning['is_moderator'])
        {
            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_mod'));
        }
        
        if (!empty($warning['is_admin']) && $warning['is_admin'])
        {
            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_admin'));
        }
        
        return $canDelete;
	}

	public function canUpdateWarningExpiration(array $warning, &$errorPhraseKey = '', array $viewingUser = null)
	{
        $this->standardizeViewingUserReference($viewingUser);
        
		$canUpdate = parent::canUpdateWarningExpiration($warning, $errorPhraseKey, $viewingUser);
        
        if (!empty($warning['is_moderator']) && $warning['is_moderator'])
        {
            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_mod'));
        }
        
        if (!empty($warning['is_admin']) &&  $warning['is_admin'])
        {
            return ($viewingUser['user_id'] && XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_admin'));
        }
        
        return $canUpdate;
	}
}