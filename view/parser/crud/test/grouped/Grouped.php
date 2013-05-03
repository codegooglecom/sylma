<?php

namespace sylma\view\parser\crud\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\fs, sylma\storage\sql;

class Grouped extends tester\Parser implements core\argumentable {

  const DB_MANAGER = 'mysql';

  protected $sTitle = 'CRUD';
  protected static $sArgumentClass = '\sylma\core\argument\Readable';

  public function __construct() {

    $this->setDirectory(__file__);

    $arg = $this->createArgument('/#sylma/view/test/database.xml');
    \Sylma::setControler(self::DB_MANAGER, new sql\Manager($arg));

    $this->runQuery($arg->read('script'));

    parent::__construct();
  }

  public function load($sPath, array $aArguments = array(), $bUpdate = true) {

    return $this->getManager(self::PARSER_MANAGER)->load($this->getFile($sPath), $aArguments, $bUpdate);
  }

  protected function loadResult($manager, fs\file $file, array $aArguments) {

    $this->setFile($file);

    return null;
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function set($sPath, $mValue = null) {

    return parent::set($sPath, $mValue);
  }

  public function loadScript(array $aArguments = array()) {

    $manager = $this->getManager(self::PARSER_MANAGER);
    $result = $manager->load($this->getFile(), $aArguments, false);

    return $result;
  }

  public function runQuery($sValue, $bMultiple = true) {

    $db = $this->getManager(self::DB_MANAGER);
    return $bMultiple ? $db->query($sValue) : $db->get($sValue);
  }
}

