<?php

namespace sylma\storage\xml\test;
use sylma\core, sylma\modules\tester;

class Handler extends tester\Parser implements core\argumentable {

  protected $sTitle = 'XML Template';

  public function __construct() {

    $this->setDirectory(__file__);

    parent::__construct();
  }
}

