<?php

namespace sylma\parser\security\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs, \sylma\parser;

require_once('modules/tester/Prepare.php');

class Module extends tester\Prepare {

  const NS = 'http://www.sylma.org/parser/security/test';

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

  public function __construct(parser\action\Controler $controler = null) {

    \Sylma::getControler('dom');

    require_once('parser/action.php');

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');
    $this->setNamespace(parser\action::NS, 'le', false);

    if (!$controler) $controler = $this;
    //if (!$controler) $controler = \Sylma::getControler('action');

    $this->setControler($controler);
  }

  public function getArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::getArgument($sPath, $mDefault, $bDebug);
  }

  public function setArgument($sPath, $mValue) {

    return parent::setArgument($sPath, $mValue);
  }

  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {

    if ($node = $test->getx('self:node', array(), false)) {

      $this->setArgument('node', $node->getFirst());
    }

    return parent::test($test, $controler, $doc, $file);
  }

  public function onPrepared($mResult) {

    if ($action = $this->getArgument('action')) {

      $action->setControler($this->getUser(), 'user');
    }
  }

  public function getUser() {

    return $this->user;
  }

  public function setUser($sName) {

    if (!array_key_exists($sName, $this->aUsers)) {

      $this->throwException(txt('Unknown test user : %s', $sName));
    }

    $user = $this->getControler('user')->getControler();

    $aGroups = $this->aUsers[$sName];
    $this->user = $user->create('user', array($user, $sName, $aGroups));
  }

  public function getAction($sPath, array $aArguments = array()) {

    return parent::getAction($sPath, $aArguments);
  }
}

