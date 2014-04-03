<?php

namespace sylma\action\component;
use sylma\core, sylma\dom;

class Caller extends Basic {

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);
  }

  protected function loadArguments() {

    $aResult = $this->getNode()->hasChildren() ? $this->parseChildren($this->getNode()->getChildren()) : array();

    return $aResult;
  }

  protected function loadPath($sPath) {

    $path = $this->create('path', array($sPath, $this->getSourceDirectory()));
    $path->parse();

    return $path;
  }

  protected function addParsedChild(dom\element $el, array &$aResult, $mContent) {

    $mContent = current($this->getWindow()->parseArrayables(array($mContent)));

    if ($sKey = $el->readx('@action:name', array(), false)) {

      $aResult[$sKey] = $mContent;
    }
    else {

      $aResult[] = $mContent;
    }
  }
}

