<?php

namespace sylma\schema\cached\form;
use sylma\core;

class Sub extends Form {

  protected $bID = false;
  protected $iKey;

  public function setSub($sAlias, $iKey, $parent, $sID) {

    $this->setName($sAlias);
    $this->setKey($iKey);
    $this->setParent($parent);
    $this->useID((bool) $sID);
  }

  protected function useID($bValue = null) {

    if (is_bool($bValue)) $this->bID = $bValue;

    return $this->bID;
  }

  public function getID() {

    $el = $this->getElement('id', false);

    return $el ? $el->getValue() : null;
  }

  protected function setKey($iKey) {

    $this->iKey = $iKey;
  }

  public function getKey() {

    return $this->iKey;
  }

  protected function setParent(Form $parent) {

    $this->parent = $parent;
  }

  protected function getParent() {

    return $this->parent;
  }

  public function validate() {

    if ($this->useID()) {

      $iID = $this->getID();

      if ($iID) {

        if ($iID < 0) {

          $this->setMode('delete');

          $id = $this->getElement('id');
          $id->setValue(abs($iID));

          $this->aElements = array('id' => $id);
        }
        else {

          $this->setMode('update');
        }
      }
      else {

        $this->setMode('insert');
        unset($this->aElements['id']);
      }
    }

    $bResult = $this->validateElements();

    if (!$bResult) {

      $this->getParent()->isValid(false);
    }

    return $bResult;
  }

  public function isUsed() {

    return true;
  }

  public function asString() {

    $sResult = $this->getMode() === 'insert' ? $this->buildInsert() : $this->buildUpdate();

    return $sResult;
  }
}

