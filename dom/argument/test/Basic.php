<?php

namespace sylma\dom\argument\test;
use \sylma\core;

require_once('core/argument/test/Basic.php');

class Basic extends core\argument\test\Basic {

  const NS = 'http://www.sylma.org/core/argument/test';
  protected $sTitle = 'Options';

  public function __construct() {

    $this->getControler('dom');

    $this->setDirectory(__file__);
    $this->setNamespace(self::NS, 'self');

    $this->setArguments('settings.yml');

    $controler = $this->create('controler', array($this, $this->getDirectory('samples')));
    $controler->setArguments($this->getArguments());

    $this->setFiles(array($this->getFile('basic.xml')));
    $this->setControler($controler);
  }
}

