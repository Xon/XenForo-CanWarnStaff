<?php

class SV_CanWarnStaff_Listener
{
    const AddonNameSpace = 'SV_CanWarnStaff';

    public static function load_class($class, array &$extend)
    {
        switch ($class)
        {
            case 'XenForo_Model_ProfilePost':
            case 'XenForo_Model_Post':
            case 'XenForo_Model_User':
            case 'XenForo_Model_Warning':
                $extend[] = self::AddonNameSpace.'_'.$class;
                break;
        }
    }
}
