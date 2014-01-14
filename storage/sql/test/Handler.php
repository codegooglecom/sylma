<?php

namespace sylma\storage\sql\test;
use sylma\core, sylma\modules\tester;

class Handler extends tester\Formed implements core\argumentable {

  protected $sTitle = 'SQL';

  public function __construct() {

    $this->setDirectory(__file__);

    parent::__construct();
  }

  public function getSchema($sPath) {

    $file = $this->getFile($sPath);
    $builder = $this->getManager(self::PARSER_MANAGER)->loadBuilder($file, $this->getExportDirectory());

    return $builder->getSchema();
  }
}

