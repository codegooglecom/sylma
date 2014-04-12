<?php

namespace sylma\storage\sql;
use sylma\core;

class Connection extends core\module\Argumented {

  const SUCCESS_CODE = '00000';

  protected $db;
  protected $aSettings = array(
    'debug' => array(
      'show' => false,
    ),
  );

  public function __construct(Manager $manager, core\argument $arg) {

    $this->setManager($manager);
    $this->setArguments($this->aSettings);
    $this->setArguments($arg);

    $this->db = $this->connect($this->getArguments());
    $this->getDatabase()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
  }

  protected function connect(core\argument $arg) {

    try {

      $sLink = 'mysql:dbname=' . $arg->read('database') . ';host=' .  $arg->read('host') . ';charset=UTF8';
      $result = new \PDO($sLink, $arg->read('user'), $arg->read('password'), array(
        \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
      ));

    } catch (\PDOException $e) {

      $this->throwException(sprintf('Connection failed : %s', $e->getMessage()));
    }

    return $result;
  }

  public function getDatabase() {

    return $this->db;
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

    $result = null;

    try {

      $result = $this->getDatabase()->query($sQuery);

      if (!$result) {

        if ($bDebug) $this->launchExceptionEmpty();
        $result = array();
      }
      else {

        $result = new Argument($result->fetchAll($iFormat));
      }
    }
    catch (\PDOException $e) {

      throw \Sylma::loadException($e);
    }

    $this->logQuery($sQuery);

    //$this->catchError();

    return $result;
  }

  public function execute($sQuery, $bDebug = true) {

    try {

      $this->logQuery($sQuery);
      // https://bugs.php.net/bug.php?id=61613 : Cannot handle errors with multiple request
      $result = $this->getDatabase()->query($sQuery);
    }
    catch (\PDOException $e) {

      throw \Sylma::loadException($e);
    }

    return $result;
  }

  public function insert($sQuery, $bDebug = true) {

    try {

      $bResult = $this->getDatabase()->exec($sQuery);
    }
    catch (\PDOException $e) {

      throw \Sylma::loadException($e);
    }

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

    try {

      $stat = $this->getDatabase()->query($sQuery);
      $this->logQuery($sQuery);

      if (!$stat) {

        if ($bDebug) $this->launchExceptionEmpty();
        $result = $stat;
      }
      else {

        $result = $stat->columnCount() ? $stat->fetch() : array($stat->rowCount());
      }
    }
    catch (\PDOException $e) {

      throw \Sylma::loadException($e);
    }

    return $result ? current($result) : $result;
  }

  public function get($sQuery, $bDebug = true, $bArgument = true) {

    try {

      $result = $this->getDatabase()->query($sQuery);
      $this->logQuery($sQuery);

      $result = is_object($result) ? $result->fetch(\PDO::FETCH_ASSOC) : null;
    }
    catch (\PDOException $e) {

      throw \Sylma::loadException($e);
    }

    if (!$result && $bDebug) $this->launchExceptionEmpty();

    if ($bArgument) {

      $result = $result ? new Argument($result) : new Argument;
    }

    return $result;
  }

  public function escape($mVal) {

    if (is_array($mVal)) {

      $mResult = array();

      foreach ($mVal as $sub) {

        $mResult[] = $this->escape($sub);
      }
    }
    else {

      $mResult = $this->getDatabase()->quote($mVal);
    }

    return $mResult;
  }
}

