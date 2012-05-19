<?php

namespace sylma\storage\fs\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('modules/tester/Prepare.php');

class Security extends tester\Prepare {

  const NS = 'http://www.sylma.org/storage/fs/test';
  protected $sTitle = 'Security';
  protected $user;

  protected $aUsers = array(
    'tester01' => array(
      'group01',
      'group00',
    ),
    'tester02' => array(
      'group02',
      'group00',
    ),
    'tester03' => array(
      'group03',
      'group00',
    ),
  );

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setArguments('../settings.yml');

    //$dir = $this->getDirectory();

    //$this->setControler($this->createControler((string) $dir), 'fs/test');
    $this->setControler($this);

    $this->setFiles(array($this->getFile('security.xml')));
  }

  protected function createControler($sPath) {

    $result = $this->create('controler', array(\Sylma::ROOT));
    $result->loadDirectory($sPath);

    return $result;
  }

  public function getUser() {

    return $this->user;
  }

  public function setUser($sName) {

    if (!array_key_exists($sName, $this->aUsers)) {

      $this->throwException(sprintf('Unknown test user : %s', $sName));
    }

    $user = $this->getControler('user')->getControler();

    $aGroups = $this->aUsers[$sName];
    $this->user = $user->create('user', array($user, $sName, $aGroups));
  }

  public function onPrepared($mResult) {

    $this->getControler('fs/test')->setControler($this->getUser(), 'user');
  }

  protected function test(dom\element $test, $controler, dom\document $doc, fs\file $file) {

    $this->setControler($this->createControler((string) $this->getDirectory()), 'fs/test');

    $result = parent::test($test, $this, $doc, $file);

    return $result;
  }
}


