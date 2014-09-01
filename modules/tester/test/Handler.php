<?php

namespace sylma\modules\tester\test;
use sylma\core, sylma\modules\tester, sylma\dom;

class Handler extends tester\Parser implements core\argumentable {

  protected $sTitle = 'Test';

  public function __construct() {

    //$this->setNamespace(self::NS, self::PREFIX);
    $this->setDirectory(__file__);
    //$this->setManager($this);

    parent::__construct();
  }
}

