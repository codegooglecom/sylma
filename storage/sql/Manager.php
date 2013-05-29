<?php

namespace sylma\storage\sql;
use sylma\core;

class Manager extends core\module\Argumented {

  const SUCCESS_CODE = '00000';

  public function __construct(core\argument $arg) {

    $this->setArguments($arg);
    $this->db = $this->connect($this->getArguments());
  }

  protected function connect(core\argument $arg) {

    try {

      $sLink = 'mysql:dbname=' . $arg->read('database') . ';host=' .  $arg->read('host');
      $result = new \PDO($sLink, $arg->read('user'), $arg->read('password'));

    } catch (\PDOException $e) {

      $this->throwException(sprintf('Connection failed : %s', $e->getMessage()));
    }

    return $result;
  }

  public function getDatabase() {

    return $this->db;
  }

  protected function catchError(array $aVars = array()) {

    if ($this->getDatabase()->errorCode() !== self::SUCCESS_CODE) {

      $aError = $this->getDatabase()->errorInfo();
      $this->launchException(sprintf('SQL Error : %s', "$aError[2] ({$aError[0]}, {$aError[1]})"), $aVars);
    }
  }

  protected function launchExceptionEmpty() {

    $this->launchException("Empty query result");
  }

  protected function logQuery($sQuery) {

    if ($this->readArgument('debug/show')) {

      dsp($sQuery);
    }
  }

  public function extract($sQuery, $bDebug = true) {

    return $this->query($sQuery, $bDebug, \PDO::FETCH_COLUMN);
  }

  public function query($sQuery, $bDebug = true, $iFormat = \PDO::FETCH_ASSOC) {

    $result = $this->getDatabase()->query($sQuery);
    $this->logQuery($sQuery);

    $this->catchError();

    if (!$result) {

      if ($bDebug) $this->launchExceptionEmpty();
      $result = array();
    }
    else {

      $result = new Argument($result->fetchAll($iFormat));
    }

    return $result;
  }

  public function insert($sQuery, $bDebug = true) {

    $bResult = $this->getDatabase()->exec($sQuery);
    $this->catchError();

    if ($bResult) {

      $result = $this->getDatabase()->lastInsertId();
      $this->logQuery($sQuery);
    }
    else {

      $result = false;
    }

    return $result;
  }

  public function read($sQuery, $bDebug = true) {

    $stat = $this->getDatabase()->query($sQuery);
    $this->logQuery($sQuery);

    $this->catchError();

    if (!$stat) {

      if ($bDebug) $this->launchExceptionEmpty();
      $result = $stat;
    }
    else {

      $result = $stat->fetch();
    }

    return $result ? current($result) : $result;
  }

  public function get($sQuery, $bDebug = true) {

    $result = $this->getDatabase()->query($sQuery);
    $this->logQuery($sQuery);

    $this->catchError();

    $result = is_object($result) ? $result->fetch(\PDO::FETCH_ASSOC) : null;

    if (!$result) {

      if ($bDebug) $this->launchExceptionEmpty();
    }
    else {

      $result = new Argument($result);
    }

    return $result;
  }
}

