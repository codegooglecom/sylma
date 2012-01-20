<?php

namespace sylma\parser\caller;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\action\php;

require_once('core/module/Domed.php');
require_once('parser/domed.php');

class Controler extends core\module\Domed implements parser\domed {

  const NS = 'http://www.sylma.org/parser/caller';

  protected $aInterfaces = array();
  protected $parent;

  public function __construct() {

    $this->setNamespace(self::NS);

    $this->setDirectory(__FILE__);
    $this->setArguments('controler.yml');
  }

  protected function loadInterface($sName) {

    if (!array_key_exists($sName, $this->aInterfaces)) {

      $sDocument = str_replace('\\', '/', $sName) . '.iml';

      $doc = $this->getDocument($sDocument);

      $this->aInterfaces[$sName] = $this->create('interface', array($this, $this->createArgument($doc)));
    }

    return $this->aInterfaces[$sName];
  }

  protected function getParent() {

    return $this->parent;
  }

  public function setParent(parser\domed $parent) {

    $this->parent = $parent;
  }

  public function parse(dom\node $node) {

    if ($node->getType() != dom\node::ELEMENT || $node->getName() != 'call' || $node->getNamespace() != $this->getNamespace()) {

      $this->throwException(txt('Invalid %s, call expected', $node->asToken()));
    }

    $window = $this->getParent()->getWindow();
    $interface = $this->loadObject($window->getScope());

    return $interface->parseCall($node, $window->getScope());
  }

  protected function loadObject(php\_object $obj) {

    $instance = $obj->getObject();

    return $this->loadInterface($instance->getInterface()->getName());
  }
}