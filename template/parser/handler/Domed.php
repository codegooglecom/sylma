<?php

namespace sylma\template\parser\handler;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\storage\fs, sylma\parser\languages\common, sylma\template;

class Domed extends Templated implements reflector\elemented, template\parser\handler {

  const NS = 'http://2013.sylma.org/template';
  const PREFIX = 'tpl';

  protected $aRegistered = array();
  protected $aTemplates = array();
  protected $aConstants = array();

  protected $result;

  public function parseRoot(dom\element $el) {

    $this->launchException('Not ready');

    $this->setNode($el, false);

    if ($el->getName() !== 'stylesheet') {

      $this->throwException('Bad root');
    }

    $this->loadTemplates($el);
    $this->loadResult();
  }

  public function parseFromChild(dom\element $el) {

    return $this->getCurrentTemplate()->parseComponent($el);
  }

  public function importFile(fs\file $file) {

    $this->log("Import : " . $file->asToken());

    $doc = $file->getDocument();
    $this->parseChildren($doc->getChildren());
  }

  protected function parseElementSelf(dom\element $el) {

    switch ($el->getName()) {

      case 'template' :

        $this->loadTemplate($el);
        $result = null;
        break;

      default :

        $result = parent::parseElementSelf($el);
    }

    return $result;
  }

  public function lookupNamespace($sPrefix = '') {

    return $this->getNode()->lookupNamespace($sPrefix);
  }

  protected function loadResult() {

    $window = $this->getWindow();

    $result = $window->addVar($window->argToInstance(''));
    $this->result = $result;
  }

  public function applyArrayTo($target, array $aPath, $sMode, array $aArguments = array()) {

    $pather = $this->getPather();
    $pather->setSource($target);

    return $aPath ? $pather->parsePathTokens($aPath, $sMode, $aArguments) : $target->reflectApply($sMode, $aArguments);
  }

  public function applyPathTo($target, $sPath, $sMode, array $aArguments = array()) {

    $pather = $this->getPather();
    $pather->setSource($target);

    return $pather->applyPath($sPath, $sMode, $aArguments);
  }

  protected function getPather() {

    return $this->getCurrentTemplate()->getPather();
  }

  public function importTree($sPath, $sMode = '') {

    switch ($sMode) {

      case 'argument' :

        $content = $this->createArgumentFromString($sPath);
        break;

      default :
      case 'document' :

        $this->loadDefaultArguments();
        $content = $this->create('options', array($this->getSourceFile($sPath)->getDocument()));
        break;
    }

    return $this->create('tree', array($this, $content));
  }

  public function trimString($sValue) {

    return parent::trimString($sValue);
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

  public function setConstant($sName, $sValue) {

    $this->aConstants[$sName] = $sValue;
  }

  public function getConstant($sName) {

    return $this->aConstants[$sName];
  }
}
