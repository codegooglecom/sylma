<?php

namespace sylma\storage\xml;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template, sylma\storage\fs;

class Resource extends reflector\handler\Elemented implements reflector\elemented {

  protected $tree;
  protected $sReflector;

  const NS = 'http://2013.sylma.org/storage/xml';
  const PREFIX = 'dom';

  const VIEW_NS = 'http://2013.sylma.org/view';

  public function parseRoot(dom\element $el) {

    $this->setDirectory(__FILE__);

    $this->setNamespace($el->getNamespace(), self::PREFIX);

    $this->loadDefaultArguments();

    return $this->parseElement($el);
  }

  public function setReflector($sClass) {

    $this->sReflector = $sClass;
  }

  protected function getReflector() {

    return $this->sReflector;
  }

  protected function parseElementSelf(dom\element $el) {

    if ($el->getName() === 'resource') {

      $result = $this->reflectResource($el);
    }
    else {

      $result = parent::parseElementSelf($el);
    }

    return $result;
  }

  protected function reflectResource(dom\element $el) {

    $this->setNode($el);

    return $this;
  }

  protected function build() {

    $options = $this->createOptions((string) $this->getSourceFile($this->readx('@file', true)));
    $view = $this->lookupParser(self::VIEW_NS);

    if ($sClass = $this->getReflector()) {

      $root = new $sClass($view);
    }
    else {

      $root = $this->loadSimpleComponent('tree', $view);
    }

    $root->setOptions($options);

    $root->isRoot(true);
    $this->setTree($root);
  }

  protected function setTree(template\parser\tree $tree) {

    $this->tree = $tree;
  }

  public function getTree() {

    if (is_null($this->tree)) {

      $this->build();
      if (is_null($this->tree)) $this->tree = false;
    }

    return $this->tree;
  }

  public function getCurrentTree() {

    $current = $this->getSchema()->getView()->getCurrentTemplate()->getTree(false);

    return $current ? $current : $this->getTree();
  }

  public function setMode() {

    return null;
  }
}
