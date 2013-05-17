<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\schema\parser;

class Particle extends parser\component\Particle  {

  protected $aChildren = array();

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
  }

  public function getElement($sName, $sNamespace) {

    $this->launchException('Not yet ready');
    
    $result = null;

    if ($el = $this->getx("self:element[@name='$sName']")) {

      $result = $this->getParser()->parseComponent($el);
      $result->loadNamespace($sNamespace);
    }

    return $result;
  }
}

