<?php

namespace sylma\storage\sql;
use sylma\core;

class Manager extends core\module\Filed {

  const SUCCESS_CODE = '00000';
  const CONNECTION_DEFAULT = 'default';

  protected $aConnections = array();

  public function __construct(core\argument $arg) {

    $this->setDirectory(__FILE__);

    $this->setSettings($arg);
    $this->setSettings('manager.xml');
  }

  protected function setConnection($sName, \PDO $connection) {

    $this->aConnections[$sName] = $connection;
  }

  public function getConnection($sName = '') {

    if (!$sName) $sName = self::CONNECTION_DEFAULT;

    if (!array_key_exists($sName, $this->aConnections)) {

      $result = $this->create('connection', array($this, $this->get($sName)));
      $this->aConnections[$sName] = $result;
    }

    return $this->aConnections[$sName];
  }
/*
  public function addConnection($sName, core\argument $arg) {

    $this->set($sName, $arg);
  }
 */
}

