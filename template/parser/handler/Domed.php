<?php

namespace sylma\template\parser\handler;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\template, sylma\parser\languages\common;

class Domed extends Templated implements reflector\elemented {

  const NS = 'http://2013.sylma.org/template';

  protected $aRegistered = array();
  protected $aTemplates = array();
  protected $aElements = array();

  protected $result;

  public function parseRoot(dom\element $el) {

    $this->setNode($el, false);

    if ($el->getName() !== 'stylesheet') {

      $this->throwException('Bad root');
    }

    $this->loadTemplates($el);
    $this->loadResult();
  }

  public function parseFromChild(dom\element $el) {

    $this->getCurrentTemplate()->parseComponent($el);
  }

  public function getElement() {

    if (!$this->aElements) {

      $this->launchException('No element defined');
    }

    return end($this->aElements);
  }

  public function startElement(template\element $el) {

    $this->aElements[] = $el;
  }

  public function stopElement() {

    array_pop($this->aElements);
  }

  public function lookupNamespace($sPrefix = '') {

    return $this->getNode()->lookupNamespace($sPrefix);
  }

  protected function loadResult() {

    $window = $this->getWindow();

    $result = $window->addVar($window->argToInstance(''));
    $this->result = $result;
  }

  /**
   *
   * @return common\_var
   */
  public function getResult() {

    return $this->result;
  }

  public function addToResult($mContent, $bAdd = true) {

    return $this->getWindow()->addToResult($mContent, $this->getResult(), $bAdd);
  }

  public function register($obj) {

    $this->aRegistered[] = $obj;
  }

  public function getRegistered() {

    return $this->aRegistered;
  }
}
