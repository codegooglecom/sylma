<?php

namespace sylma\view\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\sql;

class Grouped extends tester\Parser implements core\argumentable {

  protected $sTitle = 'Grouped';

  public function __construct() {

    $this->setDirectory(__file__);

    \Sylma::setControler('mysql', new sql\Manager($this->createArgument('../database.xml')));

    parent::__construct();
  }
}

