<?php

namespace sylma\parser\caller;
use sylma\core, sylma\parser, sylma\dom, sylma\parser\action\php, sylma\storage\fs;

require_once('core/module/Domed.php');
require_once('parser/domed.php');

class Controler extends core\module\Domed implements parser\elemented {

  protected $aInterfaces = array();
  protected $aFiles = array();

  protected $parent;

  public function __construct() {

    require_once('parser/caller.php');
    $this->setNamespace(parser\caller::NS);

    $this->setDirectory(__FILE__);
    $this->setArguments('controler.yml');
  }

  public function getInterface($sPath, fs\directory $directory = null) {

    $result = null;

    $file = $this->getControler('fs')->getFile($sPath, $directory);
    $sFile = (string) $file;

    if (!array_key_exists($sFile, $this->aFiles)) {

      $result = $this->create('interface', array($this, $file));
      $sClass = $result->getName();

      $this->aFiles[$sFile] = $sClass;
      $this->aInterfaces[$sClass] = $result;
    }
    else {

      $result = $this->aInterfaces[$this->aFiles[$sFile]];
    }

    return $result;
  }

  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  public function getInterfaceFromClass($sName, $sFile = '') {

    $result = null;

    if (!array_key_exists($sName, $this->aInterfaces)) {

      if ($sFile) {

        $sDocument = substr($sFile, 0, -4) . '.iml';
      }
      else {

        $sDocument = str_replace('\\', '/', strtolower($sName)) . '.iml';
      }

      $result = $this->getInterface($sDocument);
    }
    else {

      $result = $this->aInterfaces[$sName];
    }

    return $result;
  }

  public function getParent() {

    return $this->parent;
  }

  public function setParent(parser\elemented $parent) {

    $this->parent = $parent;
  }

  public function parse(dom\node $node) {

    if ($node->getType() != dom\node::ELEMENT || $node->getName() != 'call' || $node->getNamespace() != $this->getNamespace()) {

      $this->throwException(sprintf('Invalid %s, call expected', $node->asToken()));
    }

    $window = $this->getParent()->getWindow();
    $interface = $this->loadObject($window->getScope());

    return $interface->parseCall($node, $window->getScope());
  }

  /**
   * Find interface corresponding to object given as argument
   * @param php\_object $obj
   * @return parser\caller
   */
  public function loadObject(php\_object $obj) {

    $interface = $obj->getInstance()->getInterface();

    return $this->getInterfaceFromClass($interface->getName(), $interface->getFile());
  }
}