<?php

namespace sylma\core\test;
use sylma\core, sylma\modules\tester, sylma\dom;

class Handler extends tester\Initializer implements core\argumentable {

  protected $sTitle = 'Core';

  protected $aUsers = array(
    'tester01' => array(
      'test01',
      'test00',
    ),
    'tester02' => array(
      'test02',
      'test00',
    ),
    'tester03' => array(
      'test03',
      'test00',
    ),
  );

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setSettings('settings.xml');

    parent::__construct();
  }
}

