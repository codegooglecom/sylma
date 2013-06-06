<?php

namespace sylma\template\binder\context;
use sylma\core, sylma\dom, sylma\modules;

class Objects extends modules\html\context\JS implements core\stringable {

  const PARENT_PATH = 'sylma.tmp';

  protected $sPath = '';
  protected $aObjects = array();

  protected function parsePath($sPath) {

    if (strpos($sPath, '.') !== false) $aResult = explode('.', $sPath);
    else $aResult = array($sPath);

    return $aResult;
  }

  public function setParentPath($sPath) {

    $this->sPath = $sPath;
  }

  protected function setCurrent(core\argument $obj) {

    $this->aObjects[] = $obj;
  }

  protected function getCurrent() {

    return end($this->aObjects);
  }

  public function addOption($sName, $val) {

    if (!$this->getCurrent()) {

      $this->launchException('Cannot add option, no object defined');
    }

    $this->getCurrent()->set('options.' . $sName, $val);

    return '';
  }

  public function startObject($sName, array $aContent) {

    if ($this->getCurrent()) {

      $obj = $this->getCurrent()->set('objects.' . $sName, $aContent, true);
    }
    else {

      $obj = $this->set($sName, $aContent, true);
    }

    $this->setCurrent($obj);

    return '';
  }

  public function stopObject() {

    array_pop($this->aObjects);

    return '';
  }

  protected function getParentPath() {

    return $this->sPath ? $this->sPath : self::PARENT_PATH;
  }

  public function asString() {

    $sParent = $this->query() ? $this->getParentPath() : "''";

    return 'sylma.ui.load(' . $sParent . ', ' . $this->asJSON() . ')';
  }
}

