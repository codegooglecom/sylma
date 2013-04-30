<?php

namespace sylma\storage\sql;
use sylma\core;

class Manager extends core\module\Argumented {

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

    $aError = $this->getDatabase()->errorInfo();
    $this->launchException(sprintf('SQL Error : %s', "$aError[2] ({$aError[0]}, {$aError[1]})"), $aVars);
  }

  public function query($sQuery, $bDebug = true) {

    if (!$result = $this->getDatabase()->query($sQuery)) {

      $this->catchError();
    }

    return new Argument($result);
  }

  public function insert($sQuery, $bDebug = true) {

    if ($this->getDatabase()->exec($sQuery)) {

      $result = $this->getDatabase()->lastInsertId();
    }
    else {

      $result = false;
    }

    return $result;
  }

  public function read($sQuery, $bDebug = true) {

    $result = $this->getDatabase()->exec($sQuery);

    return $result;
  }

  public function get($sQuery, $bDebug = true) {

    if (!$result = $this->getDatabase()->query($sQuery) and $bDebug) {

      $this->catchError(get_defined_vars());
    }

    return new Argument($result->fetch(\PDO::FETCH_ASSOC));
  }
}

