<?php

namespace sylma\storage\sql\view\component;
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

    if (!$sOP = $this->readx('@op')) {

      $sOP = '=';
    }

    $bIN = strtolower(trim($sOP)) == 'in';

    if ($this->getNode()->isComplex()) {

      $content = $this->parseComponentRoot($this->getNode());

      if ($bEscape && !$bOptional && !$bIN) {

        $content = $this->reflectEscape($this->getWindow()->toString($content));
      }
    }
    else if (!$bIN) {

      if ($bOptional) {

        $this->launchException('Optional filter must be complex');
      }

      $content = $bEscape ? "'{$this->readx()}'" : $this->readx();
    }

    if ($bIN && !$bOptional) {

      $window = $this->getWindow();
      $escape = $window->addControler(self::DB_MANAGER)->call('escape', array($content));
      $content = array('(', $window->callFunction('implode', 'php-string', array(',', $escape)), ')');
    }

    $sName = $this->readx('@name', true);

    $this->log("SQL : filter [$sName]");

    if ($bOptional) {

      $sDefault = $this->readx('@default');
      $query->setOptionalWhere($tree->getElement($sName, $tree->getNamespace()), $sOP, $this->getWindow()->toString(array($content)), $sDefault);
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

