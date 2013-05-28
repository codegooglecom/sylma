<?php

namespace sylma\storage\fs\test;
use \sylma\modules\tester, \sylma\core, \sylma\dom, \sylma\storage\fs;

class Basic extends tester\Basic implements core\argumentable {

  const NS = 'http://www.sylma.org/storage/fs/test';
  protected $sTitle = 'Basic';

  public function __construct() {

    \Sylma::getControler('dom');

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setArguments('../settings.yml');
    //$this->setArguments(\Sylma::load('../settings.xml.php', __DIR__));

    $dir = $this->getDirectory();

    $this->setControler($this->createControler((string) $dir));

    $this->setFiles(array($this->getFile('basic.xml'), $this->getFile('tokened.xml')));
  }

  protected function createControler($sPath) {

    $result = $this->create('controler', array(\Sylma::ROOT));
    $result->loadDirectory($sPath);

    return $result;
  }

  protected function test(dom\element $test, $sContent, $controler, dom\document $doc, fs\file $file) {

    $result = parent::test($test, $sContent, $controler, $doc, $file);

    $sPath = $this->getControler()->getDirectory()->getName();

    $this->setControler($this->createControler($sPath));

    return $result;
  }
}


