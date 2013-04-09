<?php

namespace sylma\parser\reflector\handler;
use \sylma\core, sylma\dom, sylma\parser\reflector;

abstract class Parsed extends reflector\basic\Foreigner {

  /**
   * Sub parsers
   * @var array
   */
  protected $aParsers = array();
  protected $parent;

  protected function setParent(reflector\elemented $parent) {

    if ($parent === $this) {

      $this->throwException('Cannot set itself as parent');
    }

    //if ($this->getParent()) $this->throwException('Cannot set parent twice');

    $this->parent = $parent;
  }

  protected function getParent($bDebug = true) {

    if ($bDebug && !$this->parent) {

      $this->launchException('No parent defined');
    }

    return $this->parent;
  }

  protected function loadElementForeign(dom\element $el) {

    if ($parser = $this->getParser($el->getNamespace())) {

      $mResult = $parser->parseFromParent($el);
    }
    else if ($parser = $this->lookupParserForeign($el->getNamespace())) {

      $mResult = $parser->parseFromChild($el);
    }
    else if ($parser = $this->createParser($el->getNamespace())) {

      $mResult = $parser->parseRoot($el);
    }
    else {

      $mResult = $this->parseElementUnknown($el);
    }

    return $mResult;
  }

  public function parseRoot(dom\element $el) {

    return parent::parseRoot($el);
  }

  public function parseFromParent(dom\element $el) {

    return parent::parseRoot($el);
  }

  public function parseFromChild(dom\element $el) {

    return $this->parseElementSelf($el);
  }

  protected function lookupParserForeign($sNamespace) {

    $result = null;

    if ($this->getParent(false)) {

      $result = $this->getParent()->lookupParser($sNamespace);
    }

    return $result;
  }

  public function lookupParser($sNamespace) {

    $result = null;

    if ($this->useNamespace($sNamespace)) {

      $result = $this;
    }
    else if (!$result = $this->getParser($sNamespace)) {

      $result = $this->lookupParserForeign($sNamespace);

      if ($result) {

        $result->setParent($this);
      }
    }

    return $result;
  }

  protected function getParser($sNamespace) {

    $result = null;

    if (array_key_exists($sNamespace, $this->aParsers)) {

      $result = $this->aParsers[$sNamespace];
    }

    return $result;
  }

  public function createParser($sNamespace) {

    $manager = $this->getManager('parser');
    $result = $manager->getParser($sNamespace, $this->getRoot(), $this, false);

    if ($result) {

      $this->addParser($result, $result->getUsedNamespaces());
    }

    return $result;
  }

  /**
   * Set local parsers, with associated namespaces
   * @param parser\reflector\domed $parser
   * @param array $aNS
   */
  protected function addParser(reflector\domed $parser, array $aNS) {

    $aResult = array();

    foreach ($aNS as $sNamespace) {

      $aResult[$sNamespace] = $parser;
    }

    $this->aParsers = array_merge($this->aParsers, $aResult);
  }


}
