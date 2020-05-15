<?php

/**
 * Created by PhpStorm.
 * User: Chakratos
 * Date: 30.04.2017
 * Time: 17:11
 */
class BuildWave extends DataObject
{
    public function __construct()
    {
        $this->tablename = 'buildwaves';
    }

    public function save()
    {
        parent::save();
        $oDBH = Database::getInstance();
        return $oDBH->lastInsertId();
    }
}