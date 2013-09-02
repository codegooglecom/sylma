<?php

namespace sylma\action\test;
use sylma\core, sylma\modules\tester, sylma\storage\sql;

class Handler extends tester\Parser implements core\argumentable {

  const DB_CONNECTION = 'test';
  protected $sTitle = 'New Action';

  public function __construct() {

    $this->resetDB();

    $this->setDirectory(__file__);

    parent::__construct();
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }
}

