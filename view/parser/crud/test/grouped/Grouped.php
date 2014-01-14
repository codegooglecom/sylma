<?php

namespace sylma\view\parser\crud\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\fs, sylma\storage\sql;

class Grouped extends tester\Parser implements core\argumentable {

  const DB_MANAGER = 'mysql';

  protected $sTitle = 'CRUD';
  protected static $sArgumentClass = '\sylma\core\argument\Readable';

  public function __construct() {

    $this->setDirectory(__file__);
    $this->resetDB();

    parent::__construct();
  }

  public function load($sPath, array $aArguments = array(), $bUpdate = true) {

    return $this->getManager(self::PARSER_MANAGER)->load($this->getFile($sPath), $aArguments, $bUpdate);
  }

  protected function loadResult($manager, fs\file $file, array $aArguments, $bDelete = true) {

    $this->setFile($file);

    return null;
  }

  public function getFile($sPath = '', $bDebug = true) {

    return parent::getFile($sPath, $bDebug);
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  protected function test(\sylma\dom\element $test, $sContent, $controler, \sylma\dom\document $doc, fs\file $file) {

    $this->setFile($file);
    return parent::test($test, $sContent, $controler, $doc, $file);
  }

  public function set($sPath, $mValue = null) {

    return parent::set($sPath, $mValue);
  }

  public function loadScript(array $aArguments = array(), array $aPosts = array(), array $aContexts = array()) {

    $manager = $this->getManager(self::PARSER_MANAGER);
    $result = $manager->load($this->getFile(), $aArguments, false);

    return $result;
  }

  public function runQuery($sValue, $bMultiple = true) {

    $db = $this->getManager(self::DB_MANAGER)->getConnection(self::DB_CONNECTION);
    return $bMultiple ? $db->query($sValue) : $db->get($sValue);
  }
}

