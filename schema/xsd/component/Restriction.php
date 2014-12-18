<?php

namespace sylma\schema\xsd\component;
use sylma\core, sylma\dom, sylma\schema;

class Restriction extends schema\parser\component\Restriction {

  public function parseRoot(dom\element $el) {

    $el = $this->setNode($el);
  }

  protected function loadBase() {

    $result = false;

    if ($sBase = $this->readx('@base')) {

      list($sNamespace, $sName) = $this->getParser()->parseName($sBase, $this->getType(), $this->getNode());
      $result = $this->getParser()->getType($sName, $sNamespace);
    }

    return $result;
  }

  public function getBase() {

    if (is_null($this->base)) {

      $this->base = $this->loadBase();
    }

    return parent::getBase();
  }

  protected function loadBaseRules() {

    if ($this->getBase()) {

      if ($define = $this->getBase()->getDefine(false)) {

        $this->merge($define);
      }
    }
  }

  public function getRules() {

    if (!$this->rules) {

      $this->loadBaseRules();
      $this->loadRules();
    }

    return parent::getRules();
  }

  protected function loadRules() {

    $aResult = array();
    $node = $this->getNode();

    foreach ($node->getChildren() as $el) {

      $this->loadRule($el, $aResult);
    }

    if ($aResult) {

      $this->setRules($this->createArgument($aResult));
    }
  }

  protected function loadRule(dom\element $el, array &$aResult) {

    $aResult[$el->getName()] = $el->readx('@value', array(), false);
  }

  protected function merge(schema\parser\component\Restriction $define) {

    $this->setRules($define->getRules());
  }
}
