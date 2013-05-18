<?php

namespace sylma\view\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\sql;

class Grouped extends tester\Parser implements core\argumentable {

  const DB_MANAGER = 'mysql';

  protected $sTitle = 'Grouped';

  public function __construct() {

    $this->setDirectory(__file__);

    $arg = $this->createArgument('../database.xml');
    \Sylma::setControler(self::DB_MANAGER, new sql\Manager($arg));

    $this->runQuery($arg->read('script'));

    parent::__construct();
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function runQuery($sValue, $iMode = 1) {

    $db = $this->getManager(self::DB_MANAGER);

    if (!$iMode) {

      $result = $db->get($sValue);
    }
    else if ($iMode & 1) {

      $result = $db->query($sValue);
    }
    else if ($iMode & 2) {

      $result = $db->read($sValue);
    }

    return $result;
  }
}

