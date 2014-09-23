<?php

class SV_CanWarnStaff_Listener
{
	public static function loadClassModel($class, &$extend)
	{
		switch ($class)
		{
            case 'XenForo_Model_ProfilePost':
                $extend[] = 'SV_ViewOwnWarnings_XenForo_Model_ProfilePost';
                break;
            case 'XenForo_Model_Post':
                $extend[] = 'SV_ViewOwnWarnings_XenForo_Model_Post';
                break;
            case 'XenForo_Model_User':
                $extend[] = 'SV_ViewOwnWarnings_XenForo_Model_User';
                break;
		}      
	}
}
