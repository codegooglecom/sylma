<?php

namespace sylma\view\parser\crud\test\grouped;
use sylma\core, sylma\modules\tester, sylma\storage\fs;

class Grouped extends tester\Parser implements core\argumentable {

  protected $sTitle = 'CRUD';

  public function __construct() {

    $this->setDirectory(__file__);

    parent::__construct();
  }

  protected function loadResult($manager, fs\file $file, array $aArguments) {

    $this->setFile($file);

    return null;
  }

  public function loadScript(array $aArguments = array()) {

    $manager = $this->getManager(self::PARSER_MANAGER);
    $result = $manager->load($this->getFile(), $aArguments, false);
    $this->getFile()->delete();

    return $result;
  }
}

