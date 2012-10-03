<?php

namespace sylma\parser;
use sylma\core, sylma\parser;

class Manager extends core\module\Domed {

  protected $aNamespaces = array();
  protected $aParsers = array();
  protected $aContexts = array();

  public function __construct() {

    $this->setDirectory(__FILE__);
    $this->setArguments('manager.yml');

    $this->loadNamespaces($this->getArgument('namespaces'));

  }

  protected function loadNamespaces(core\argument $namespaces) {

    $this->aNamespaces = $namespaces->query();
  }

  public function getParser($sNamespace, $parent, $bDebug = true) {

    $result = null;

    if (array_key_exists($sNamespace, $this->aParsers)) {

      $result = $this->aParsers[$sNamespace];
    }
    else if (array_key_exists($sNamespace, $this->aNamespaces)) {

      $result = $this->create($this->aNamespaces[$sNamespace], array($parent));
    }
    else if ($bDebug) {

      $this->throwException(sprintf('No parser associated to namespace %s', $sNamespace));
    }

    return $result;
  }

  public function getContext($sName) {

    if (!array_key_exists($sName, $this->aContexts)) {

      $this->aContexts[$sName] = $this->create($sName);
    }

    return $this->aContexts[$sName];
  }
}
