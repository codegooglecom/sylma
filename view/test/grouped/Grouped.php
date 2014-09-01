<?php

namespace sylma\view\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\sql;

class Grouped extends tester\Formed implements core\argumentable {

  protected $sTitle = 'Grouped';

  public function __construct() {

    $this->setDirectory(__file__);
    //$this->resetDB();
    $this->resetToken();

    parent::__construct();
  }

}

