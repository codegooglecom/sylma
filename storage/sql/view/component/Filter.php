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

    if (!$query = $tree->getQuery(false)) {

      $query = $tree->getParent()->getQuery();
    }

    $bEscape = !$this->readx('@function');
    $bOptional = $this->readx('@optional');

    if (!$sOP = $this->readx('@op')) {

      $sOP = '=';
    }

    $bIN = strtolower(trim($sOP)) == 'in';
    $container = $this->getNode();

    if ($sName = $this->readx('@name')) {

      $field = $tree->getElement($sName, $tree->getNamespace());
    }
    else {

      $field = $tree;
    }

    if ($container->isComplex()) {

      $content = $this->parseComponentRoot($container);

      if ($bEscape && !$bOptional && !$bIN) {

        $content = $this->reflectEscape($this->getWindow()->toString($content));
      }
      else {

        $content = $this->getWindow()->parse($content);
      }
    }
    else if (!$bIN) {

      if ($bOptional) {

        $this->launchException('Optional filter must be complex');
      }

      $content = $bEscape ? "'{$container->readx()}'" : $container->readx();
    }
    else {

      $content = $container->readx();
    }

    if ($bIN && !$bOptional) {

      $window = $this->getWindow();

      if ($bEscape) {

        $escape = $window->addControler(self::DB_MANAGER)->call('getConnection')->call('escape', array($window->parse($content)));
      }
      else {

        $escape = $window->parse($content, true);
      }

      $content = array('(', $window->callFunction('implode', 'php-string', array(',', $escape)), ')');
    }


    $this->log("SQL : filter");

    if ($bOptional) {

      $sDefault = $this->readx('@default');
      $query->setOptionalWhere($field, $sOP, $this->getWindow()->toString(array($content)), $sDefault);
    }
    else {

      $query->setWhere($field, $sOP, $content);
    }
  }

  public function asArray() {

    if (!$this->bBuilded) {

      $aResult = array($this->build());
      //$this->bBuilded = true;
    }
    else {

      $aResult = array();
    }

    return $aResult;
  }

}

