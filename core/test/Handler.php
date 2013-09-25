<?php

namespace sylma\core\test;
use sylma\core, sylma\modules\tester, sylma\dom;

class Handler extends tester\Parser implements core\argumentable {

  protected $sTitle = 'Core';

  protected $aManagers = array();

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
    $this->setArguments('settings.xml');

    parent::__construct();
  }

  public function createDocument($mContent = null) {

    return parent::createDocument($mContent);
  }

  public function createAction($sPath) {

    $this->loadDefaultSettings();

    return $this->create('action', array($this->getFile($sPath)));
  }

  protected function prepareTest(dom\element $test, $controler) {

    $this->restoreSylma();

    return parent::prepareTest($test, $controler);
  }

  public function createUser($sAlias = '') {

    $manager = $this->getManager('user')->getManager();

    if (!$sAlias) {

      $result = $this->create('user', array($manager));
      $result->loadPublic();
    }
    else {

      if (!array_key_exists($sAlias, $this->aUsers)) {

        $this->launchException('Unknown test user : ' . $sAlias);
      }

      $aGroups = $this->aUsers[$sAlias];

      $result = $this->create('user', array($manager, $sAlias, $aGroups));
    }

    return $result;
  }

  public function clearSylma(core\Initializer $init, core\user $user = null) {

    if (!$user) {

      $user = $this->createUser();
    }

    \Sylma::setManagers(array(
      //'parser' => \Sylma::getManager('parser'),
      'init' => $init,
      'user' => $user,
    ));
  }

  public function restoreSylma() {

    \Sylma::setManagers($this->getManagers());
  }

  public function asArgument() {

    $this->setManagers(\Sylma::getManagers());
    $args = \Sylma::getSettings();
    $args->set('debug/enable', false);

    $result = parent::asArgument();

    $this->restoreSylma();
    $args->set('debug/enable', true);

    return $result;
  }
}

