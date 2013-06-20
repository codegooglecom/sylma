<?php

namespace sylma\storage\sql\view;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class Filter extends reflector\component\Foreigner implements reflector\component, common\arrayable {

  protected $bBuilded = false;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->allowForeign(true);

  }

  protected function build() {

    $tree = $this->getParser()->getCurrentTree();
    $query = $tree->getQuery();

    $bEscape = !$this->readx('@function');
    $bOptional = $this->readx('@optional');

    if ($this->getNode()->isComplex()) {

      $content = $this->parseComponentRoot($this->getNode());
      $content = $bEscape && !$bOptional ? $this->reflectEscape($content) : $content;
    }
    else {

      if ($bOptional) {

        $this->launchException('Optional filter must be complex');
      }

      $content = $bEscape ? "'{$this->readx()}'" : $this->readx();
    }

    $sName = $this->readx('@name', true);

    if (!$sOP = $this->readx('@op')) {

      $sOP = '=';
    }

    $this->log("SQL : filter [$sName]");

    if ($bOptional) {

      $query->setOptionalWhere($tree->getElement($sName, $tree->getNamespace()), $sOP, $this->getWindow()->toString(array($content)));
    }
    else {

      $query->setWhere($tree->getElement($sName, $tree->getNamespace()), $sOP, $content);
    }
  }

  public function asArray() {

    if (!$this->bBuilded) {

      $aResult = array($this->build());
      $this->bBuilded = true;
    }
    else {

      $aResult = array();
    }

    return $aResult;
  }

}

