<?php

namespace sylma\parser\languages\js\basic;
use sylma\parser\action, sylma\core, sylma\dom, sylma\parser\languages\php, sylma\parser\languages\common;

class Window extends common\basic\Window implements common\_window, core\controled {

  const NS = 'http://www.sylma.org/parser/languages/js';

  protected $aDefaultVariables = array(
    '$' => array('mootools\element', null),
    '$$' => 'mootools\elements',
  );

  protected $aVariables = array();

  public function __construct(action\compiler $controler, core\argument $args, $sClass) {

    $this->setControler($controler);
    $this->setArguments($args);
    $this->setNamespace(self::NS);

    //$self = $this->loadInstance($sClass);
    //$this->self = $this->create('var', array($this, $self, 'this'));

    $root = $this->createFunction();
    $this->setScope($this);

    //$node = $this->loadInstance('\sylma\dom\node', '/sylma/dom/node.php');
    //$this->setInterface($node->getInterface());

    //$this->sylma = $this->create('class-static', array('\Sylma'));
  }

  protected function loadDefaultVariables() {

    foreach ($this->aDefaultVariables as $sName => $mReturn) {

      $this->aVariables[$sName] = (array) $mReturn;
    }
  }

  public function createObject(array $aProperties = array()) {

    return $this->create('object', array($aProperties));
  }

  public function getVar($sName) {

    if (array_key_exists($sName, $this->aVariables)) {

      $result = $this->aVariables[$sName];
    }
    else {

      $this->throwException(sprintf('No variable with name %s', $sName));
    }

    return $result;
  }
}