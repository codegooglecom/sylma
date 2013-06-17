<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Group extends Controled implements common\argumentable, common\instruction {

  protected $aValues;

  public function __construct(common\_window $window, array $aValues) {

    $this->setWindow($window);
    $this->setValues($this->toInstruction($aValues));
    //$this->setValues($aValues);
  }

  protected function setValues(array $aValues) {

    $this->aValues = $aValues;
  }

  protected function toInstruction(array $aValues) {

    $aResult = array();

    foreach ($aValues as $mValue) {

      if (is_null($mValue)) continue;

      if (!$mValue instanceof common\instruction) {

        if (is_array($mValue)) {

          $aResult[] = $this->toInstruction($mValue);
        }
        else {

          $aResult[] = $this->getWindow()->createInstruction($mValue);
        }
      }
      else {

        $aResult[] = $mValue;
      }
    }

    return $aResult;
  }

  protected function getValues() {

    return $this->aValues;
  }

  public function asArgument() {

    return $this->getControler()->createArgument(array(
      'group' => array(
        $this->getValues(),
      )));
  }
}