<?php

namespace sylma\view\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\sql;

class Grouped extends tester\Parser implements core\argumentable {

  const DB_MANAGER = 'mysql';

  protected $sTitle = 'Grouped';

  public function __construct() {

    $this->setDirectory(__file__);

    \Sylma::setControler('mysql', new sql\Manager($this->createArgument('../database.xml')));

    parent::__construct();
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function runQuery($sValue, $bMultiple = true) {
    
    $db = $this->getManager(self::DB_MANAGER);
    return $bMultiple ? $db->query($sValue) : $db->get($sValue);
  }
}

