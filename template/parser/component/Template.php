<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template\parser;

class Template extends Stringed implements common\arrayable, common\argumentable, parser\template {

  const MATCH_DEFAULT = '[root]';
  const CHECK_RECURSION = false; // if TRUE, disable concat optimization

  protected $aContent;
  protected $bBuilded;
  protected $sMatch;

  protected $tree;

  protected $bCloned = false;
  protected static $aCall = array();
  protected $sID = '';

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowUnknown(true);
    $this->allowForeign(true);
    $this->allowComponent(true);

    $this->setID(uniqid());
    $this->build();
  }

  protected function setID($sID) {

    if ($this->sID) {

      $this->launchException('Template already IDed');
    }

    $this->sID = $sID;
  }

  protected function getID() {

    return $this->sID;
  }

  protected function loadElementUnknown(dom\element $el) {

    $component = $this->loadSimpleComponent('element', $this->getParser());
    $result = $this->loadAttributes($el, $component);

    $component->setTemplate($this);
    $component->parseRoot($el);

    return $result;
  }

  public function parseComponent(dom\element $el) {

    $result = parent::parseComponent($el);
    $result->setTemplate($this);

    return $result;
  }

  public function loadElement(dom\element $el) {

    return $this->loadElementUnknown($el);
  }

  protected function loadAttributes(dom\element $el, Element $component) {

    if ($this->useForeignAttributes($el)) {

      $aForeigns = $this->getForeignAttributes($el, null);
      $mResult = $this->parseAttributesForeign($el, $component, $aForeigns);
      $this->getForeignAttributes($el, null, true);
    }
    else {

      $mResult = $component;
    }

    $aParsers = $this->getAttributeParsers();
    $this->setAttributeParsers();

    foreach ($aParsers as $parser) {

      $parser->onClose($el, $component);
    }

    return $mResult;
  }

  protected function setMatch($sMatch) {

    $this->sMatch = $sMatch;
  }

  protected function getMatch() {

    return $this->sMatch;
  }

  public function setTree(parser\tree $tree) {

    $this->tree = $tree;
  }

  public function getTree() {

    return $this->tree;
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'use' : $result = $this->reflectUse($el); break;
      default :

        $result = parent::parseElementSelf($el);
    }

    return $result;
  }

  protected function reflectUse(dom\element $el) {

    if (!$el->hasChildren() || !$el->isComplex()) {

      $this->launchException(sprintf('%s is not valid', $el->asToken()));
    }

    $child = $el->getFirst();
    $parser = $this->getParser($child->getNamespace());
    $tree = $parser->parseRoot($child);

    // This allow use of unknown parser (like action) with generic argument return
    // There are converted to template\tree

    if ($tree instanceof common\_object) {

      $interface = $tree->getInterface();

      if (!$interface->isInstance('\sylma\core\argument')) {

        $this->launchException(sprintf('Parser object of @class %s must be instance of core\\argument', $interface->getName()));
      }

      $tree = $this->create('tree/argument', array($this->getManager(), $tree));
    }

    $this->getManager()->setTree($tree);
  }

  protected function getMode() {

    return $this->getNode()->readx('@mode', array(), false);
  }

  public function build() {

    if (!$this->bBuilded) {

      $mContent = $this->parseComponentRoot($this->getNode());

      $this->aContent = is_array($mContent) ? $mContent : array($mContent);
    }

    return $this->aContent;
  }

  public function asArray() {

    if (!$this->bCloned && $this->getMatch()) {

      $this->launchException('Template must be cloned');
    }

    if (in_array($this->getID(), self::$aCall)) {

      $this->launchException('Recursive template call');
    }

    self::$aCall[] = $this->getID();

    if (self::CHECK_RECURSION) $result = array($this->toString($this->build()));
    else $result = $this->build();

    array_pop(self::$aCall);

    return $result;
  }

  public function __clone() {

    $this->bCloned = true;
  }

  public function asArgument() {

    return $this->addToResult($this->asArray(), false);
  }

  protected function launchException($sMessage, array $aVars = array(), array $mSender = array()) {

    $mSender[] = $this->getNode()->asToken();
    $aVars[] = $this->getNode();

    parent::launchException($sMessage, $aVars, $mSender);
  }
}

