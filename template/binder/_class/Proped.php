<?php

namespace sylma\template\binder\_class;
use sylma\core, sylma\parser\languages\common, sylma\template;

abstract class Proped extends template\binder\Basic implements common\arrayable {

  const CONTEXT_ALIAS = 'js/binder/context';
  const JS_OBJECTS_PATH = 'sylma.ui.tmp';

  /**
   * @var common\js\_object
   */
  protected $object;

  /**
   * @see isRoot()
   */
  protected $bRoot = false;

  /**
   * @see setExtend()
   */
  protected $sExtend = '';

  /**
   * @see setID(), loadID()
   */
  protected $sID;

  /**
   * Extension name generated only once
   */
  protected $bExtended = false;

  protected function loadName() {

    $this->sName = uniqid('sylma');

    return $this->sName;
  }

  protected function loadParent() {

    return $this->readx('@js:parent');
  }

  protected function loadParentName() {

    return $this->readx('@js:parent-name');
  }

  protected function setObject(common\_object $obj) {

    $this->object = $obj;
  }

  protected function getObject() {

    if (!$this->object) {

      $this->launchException('No object defined');
    }

    return $this->object;
  }

  public function isRoot($bVal = null) {

    if (is_bool($bVal)) $this->bRoot = $bVal;

    return $this->bRoot;
  }

  /**
   * @see setExtend() This function can be run only once.
   */
  protected function loadExtend() {

    if (!$this->bExtended) {

      $this->setExtend('sylma.binder.classes.' . $this->getID());
      $this->bExtended = true;
    }
  }

  /**
   * Classe's name used in script
   * @see loadExtend()
   */
  protected function setExtend($sExtend) {

    $this->sExtend = $sExtend;
  }

  public function getExtend() {

    return $this->sExtend;
  }

  public function setProperty($sName, $val) {

    $this->getObject()->setProperty($sName, $val);
  }

  protected function loadID() {

    $sID = uniqid('sylma');
    $this->setID($sID);

    return $this->getID();
  }

  protected function setID($sID) {

    $this->sID = $sID;
  }

  public function getID() {

    return $this->sID;
  }
}

