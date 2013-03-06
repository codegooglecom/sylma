<?php

namespace sylma\template\parser\component;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common, sylma\template\parser as parser;

class Template extends Stringed implements common\arrayable, parser\template {

  const MATCH_DEFAULT = '[root]';

  protected $content;
  protected $tree;

  protected $sMatch = '';

  public function parseRoot(dom\element $el) {

    $this->setNode($el);

    $this->allowUnknown(true);
    $this->allowForeign(true);
    $this->allowComponent(true);
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

  public function getMatch() {

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

      $this->throwException(sprintf('%s is not valid', $el->asToken()));
    }

    $child = $el->getFirst();
    $parser = $this->getParser($child->getNamespace());
    $tree = $parser->parseRoot($child);

    // This allow use of unknown parser (like action) with generic argument return
    // There are converted to template\tree

    if ($tree instanceof common\_object) {

      $interface = $tree->getInterface();

      if (!$interface->isInstance('\sylma\core\argument')) {

        $this->throwException(sprintf('Parser object of @class %s must be instance of core\\argument', $interface->getName()));
      }

      $tree = $this->create('tree/argument', array($this->getManager(), $tree));
    }

    $this->getManager()->setTree($tree);
  }

  public function getContent() {

    return $this->content;
  }

  public function setContent($content) {

    $this->content = $content;
  }

  public function asArray() {

    $el = $this->getNode();
    //$window = $this->getWindow();

    $mContent = $this->parseComponentRoot($el);
    //$this->addToResult($mContent);

    //$this->dsp($el);
    //dsp($result);

    //$loop = $window->createForeach()

    return array($mContent);
  }

  public function asArgument() {

    return $this->asArgumentable()->asArgument();
  }
}

