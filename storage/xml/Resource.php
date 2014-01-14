<?php

namespace sylma\storage\xml;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template;

class Resource extends reflector\handler\Elemented implements reflector\elemented {

  protected $tree;
  protected $reflector;

  const NS = 'http://2013.sylma.org/storage/xml';
  const PREFIX = 'dom';

  const VIEW_NS = 'http://2013.sylma.org/view';

  public function parseRoot(dom\element $el) {

    $this->setDirectory(__FILE__);

    $this->setNamespace($el->getNamespace(), self::PREFIX);

    return $this->parseElement($el);
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

  public function setSchema() {

    $this->launchException('Not yet ready');
  }

  public function setSettings($args = null, $bMerge = true) {

    return parent::setSettings($args, $bMerge);
  }

  protected function build() {

    $view = $this->lookupParser(self::VIEW_NS);
    $root = $this->loadSimpleComponent("tree", $view);

    $this->initTree($root);
  }

  protected function initTree(template\parser\tree $tree) {

    $tree->parseRoot($this->getNode());

    $tree->isRoot(true);
    $this->setTree($tree);
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
