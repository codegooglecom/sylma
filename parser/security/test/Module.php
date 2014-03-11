<?php

namespace sylma\parser\security\test;
use sylma\modules\tester, sylma\core;

class Module extends tester\Initializer {

  protected $sTitle = 'Security';
  protected $user;

  protected $aUsers = array(
    'tester01' => array(
      'groupe01',
      'groupex',
    ),
    'tester02' => array(
      'groupe02',
      'groupex',
    ),
    'tester03' => array(
      'groupe03',
      'groupex',
    ),
  );

  public function __construct() {

    $this->setDirectory(__file__);

    parent::__construct();
  }

  public function readScript($sPath, $sUser) {

    $this->buildScript($sPath);

    $current = \Sylma::getManager('user');
    \Sylma::setManager('user', $this->createUser($sUser));

    $result = $this->getScript($sPath);

    \Sylma::setManager('user', $current);

    $this->set('result', $result);
  }
}

