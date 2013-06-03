<?php

namespace sylma\action\test;
use sylma\core, sylma\modules\tester, sylma\storage\sql;

class Handler extends tester\Parser implements core\argumentable {

  protected $sTitle = 'New Action';

  public function __construct() {

    $arg = $this->createArgument('/#sylma/view/test/database.xml');
    \Sylma::setControler(self::DB_MANAGER, new sql\Manager($arg));

    $this->setDirectory(__file__);

    parent::__construct();
  }
}

