<?php

namespace sylma\template\parser\component\element;
use sylma\core, sylma\dom, sylma\parser\languages\common, sylma\template;

abstract class Domed extends template\parser\component\Unknowned implements template\parser\component {

  const TARGET_PREFIX = 'target';

  protected $aContent = array();
  protected $bBuilded = false;
  protected $aBefore = array();

  protected $aAllowedNamespaces = array();
  protected $sID = '';

  public function parseRoot(dom\element $el) {

    $this->allowUnknown(true);
    $this->allowText(true);

    $this->aAllowedNamespaces[] = \Sylma::read('namespaces/html');

    $this->sID = uniqid();
    $this->build($el);
  }

  /**
   * @usedby \sylma\template\binder\_class\Builder::setEvent()
   */
  public function getID() {

    return $this->sID;
  }

  public function build(dom\element $el = null) {

    if (!$this->bBuilded) {

      $this->setNode($el, true, false);
      $this->start();

      $this->buildContent();

      $this->stop();

      $this->bBuilded = true;
    }

    return $this->aContent;
  }

  protected function buildContent() {

    $el = $this->getNode();

    if ($el->countChildren()) {

      if ($el->countChildren() > 1) {

        $aContent = $this->parseComponentRoot($el);
      }
      else {

        $aContent = array($this->parseComponentRoot($el));
      }

      $this->aContent = $aContent;
    }
  }

  protected function start() {

    return $this->getRoot()->startElement($this);
  }

  protected function stop() {

    return $this->getRoot()->stopElement();
  }

  protected function loadName(dom\element $el) {

    //$sName = ($el->getPrefix() ? $el->getPrefix() . ':' : '') . $el->getName();
    //$sName = $this->getPrefix($el->getNamespace()) . $el->getName();
    $sName = $el->getName();

    return $sName;
  }

  public function asToken() {

    return $this->getNode(false) ? $this->getNode()->asToken() : '[No node defined]';
  }
}

