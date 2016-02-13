<?php
class SV_CanWarnStaff_XenForo_Model_Warning extends XFCP_SV_CanWarnStaff_XenForo_Model_Warning
{
    public function canDeleteWarning(array $warning, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $canDelete = parent::canDeleteWarning($warning, $errorPhraseKey, $viewingUser);

        if (empty($viewingUser['user_id']))
        {
            return false;
        }

        if (!empty($warning['is_moderator']))
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_mod');
        }

        if (!empty($warning['is_admin']))
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_admin');
        }

        return $canDelete;
    }

    public function canUpdateWarningExpiration(array $warning, &$errorPhraseKey = '', array $viewingUser = null)
    {
        $this->standardizeViewingUserReference($viewingUser);

        $canUpdate = parent::canUpdateWarningExpiration($warning, $errorPhraseKey, $viewingUser);

        if (empty($viewingUser['user_id']))
        {
            return false;
        }

        if (!empty($warning['is_moderator']))
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_mod');
        }

        if (!empty($warning['is_admin']))
        {
            return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'manageWarning_admin');
        }

        return $canUpdate;
    }
}