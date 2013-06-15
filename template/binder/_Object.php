<?php

namespace sylma\template\binder;
use sylma\core, sylma\dom, sylma\parser\languages\common;

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
  }

  public function setClass(_Class $class) {

    $this->class = $class;
  }

  public function setName($mName) {

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

    $aResult = array(
      'extend' => $this->getClass()->getExtend(),
      //'binder' => $this->getClass()->getID(),
      'id' => $this->getID(),
    );

    if (!$this->getName()) {

      $this->setName($this->getID());
    }
    else {

      $aResult['name'] = true;
    }

    return array(
      $this->getName(),
      $aResult,
    );
  }

  protected function loadUnique() {

    $window = $this->getParser()->getPHPWindow();
    $result = $window->callFunction('uniqid', 'php-string', array('sylma-'));

    return $window->createVar($result);
  }

  protected function setID($mID) {

    $this->id = $mID;
  }

  protected function getID() {

    return $this->id;
  }

  protected function loadID() {

    if ($sID = $this->readx('@id')) {

      $this->setID($sID);
    }

    return $this->getID();
  }

  public function asArray() {

    $window = $this->getParser()->getPHPWindow();

    if ($mName = $this->readx('@js:name', false)) {

      $this->setName($mName);

      if (!$this->loadID()) {

        $id = $this->loadUnique();
        $aResult[] = $id->getInsert();
        $this->setID($id);
      }
    }
    else {

      $mName = $this->loadUnique();
      //$this->setName($mName);

      if (!$this->loadID()) {

        $aResult[] = $mName->getInsert();
        $this->setID($mName);
      }
    }

    $this->isRoot($this->getParser()->isRoot());

    $this->getParser()->startObject($this);
    $this->startLog($this->asToken());

    $element = $this->getClass()->getElement();
    $element->setAttribute('id', $this->getID());

    $var = $this->getParser()->getObjects();

    $content = $window->parseArrayables(array($element));

    if ($this->isRoot() and $sParent = $this->getParentName()) {

      $aResult[] = $this->getParser()->getObjects()->call('setParentPath', array($sParent))->getInsert();
    }

    $aResult[] = $var->call('startObject', $this->buildObject())->getInsert();
    $aResult[] = $content;
    $aResult[] = $var->call('stopObject')->getInsert();

    $this->getParser()->stopObject();
    $this->stopLog();

    return array(
      $aResult,
    );
  }

  public function asToken() {

    return "Object [{$this->getClass()->getExtend()}]";
  }
}

