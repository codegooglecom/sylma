<?php

namespace sylma\schema\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\sql;

class Grouped extends tester\Parser implements core\argumentable {

  protected $sTitle = 'Grouped';

  public function __construct() {

    $this->setDirectory(__file__);

    parent::__construct();
  }

  protected function buildResult($manager, \sylma\storage\fs\file $file, array $aArguments) {

    $builder = $manager->loadBuilder($file, $this->getExportDirectory());

    return $builder->getSchema($file);
  }
}

