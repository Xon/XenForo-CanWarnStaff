<?php

class SV_CanWarnStaff_Listener
{
    const AddonNameSpace = 'SV_CanWarnStaff_';

    public static function load_class($class, array &$extend)
    {
        $extend[] = self::AddonNameSpace.$class;
    }
}
