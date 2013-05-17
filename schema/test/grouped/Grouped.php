<?php

namespace sylma\schema\test\grouped;
use sylma\core, sylma\dom, sylma\modules\tester, sylma\storage\fs;

class Grouped extends tester\Parser implements core\argumentable {

  protected $sTitle = 'Grouped';

  public function __construct() {

    $this->setDirectory(__file__);

    parent::__construct();
  }

  protected function buildResult(dom\element $test, $manager, fs\file $file, array $aArguments) {

    $builder = $manager->loadBuilder($file, $this->getExportDirectory());

    return $builder->getSchema($file);
  }
}

