<?php

namespace sylma\view\test\grouped;
use sylma\core, sylma\modules\tester;

class Grouped extends tester\Parser implements core\argumentable {

  protected $sTitle = 'Grouped';

  public function __construct() {

    $this->setDirectory(__file__);

    parent::__construct();
  }
}

