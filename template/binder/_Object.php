<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\reflector, sylma\parser\languages\common;

class _Object extends Basic implements common\arrayable, core\tokenable {

  const JS_LOAD_CONTEXT = 'js-load';
  const PARENT_PATH = 'sylma.ui.tmp';

  protected $bRoot = true;

  protected $name;
  protected $id;

  protected $class;
  protected $var;
  protected $sJSClass;

  public function parseRoot(dom\element $el) {

    $this->setNode($el);
    $this->loadParentName();
    $this->setID($this->loadUnique());
  }

  public function setClass(_Class $class) {

    $this->class = $class;
  }

  protected function loadName() {

    if (!$mName = $this->readx('@js:name', false)) {

      $mName = $this->loadUnique();
    }

    $this->setName($mName);
    return $this->getName();
  }

  protected function setName($mName) {

    $this->name = $mName;
  }

  protected function getName() {

    return $this->name;
  }

  protected function getClass() {

    return $this->class;
  }

  protected function getJSClass() {

    return $this->sJSClass;
  }

  protected function setJSClass($sName) {

    $this->sJSClass = $sName;
  }

  protected function isRoot($mValue = null) {

    if (is_bool($mValue)) $this->bRoot = $mValue;

    return $this->bRoot;
  }

  protected function loadParentName() {

    if (!$sParent = $this->readx('@js:parent')) {

      $sParent = self::PARENT_PATH;
    }

    $this->setParentName($sParent);
    return $this->getParentName();
  }

  protected function getParentName() {

    return $this->sParent;
  }

  protected function setParentName($sParent) {

    $this->sParent = $sParent;
  }

  protected function buildObject() {

    return array(
      $this->loadName(),
      array(
        'extend' => $this->getClass()->getExtend(),
        'binder' => $this->getClass()->getID(),
        'id' => $this->getID(),
      ),
    );
  }

  protected function loadUnique() {

    $window = $this->getParser()->getPHPWindow();
    $result = $window->addVar($window->callFunction('uniqid', 'php-string', array('sylma-')));

    return $result;
  }

  protected function setID($mID) {

    $this->id = $mID;
  }

  protected function getID() {

    return $this->id;
  }

  public function asArray() {

    $this->isRoot($this->getParser()->isRoot());

    $this->getParser()->startObject($this);
    $this->startLog($this->asToken());

    $element = $this->getClass()->getElement();
    $element->setAttribute('id', $this->getID());

    $var = $this->getParser()->getObjects();
    $var->call('startObject', $this->buildObject())->insert();
    $aElement[] = $this->getParser()->getPHPWindow()->parseArrayables(array($element));
    $aElement[] = $var->call('stopObject');

    if ($this->isRoot() and $sParent = $this->getParentName()) {

      $this->getParser()->getObjects()->call('setParentPath', array($sParent))->insert();
    }

    $this->getParser()->stopObject();
    $this->stopLog();

    return array(
      $aElement,
    );
  }

  public function asToken() {

    return "Object [{$this->getClass()->getExtend()}]";
  }
}

