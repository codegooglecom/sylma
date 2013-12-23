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

  protected function toInstruction($mValue) {

    if (is_array($mValue)) {

      $aResult = array();

      foreach ($mValue as $mSub) {

        if (is_null($mSub)) continue;
        $aResult[] = $this->toInstruction($mSub);
      }

      $mValue = $aResult;
    }
    else {

      if (!$mValue instanceof common\instruction) {

        if ($mValue instanceof common\structure) {

          $aContents = array();

          foreach ($mValue->getContents() as $sKey => $mContent) {

            $aContents[$sKey] = $this->toInstruction($this->getWindow()->parse($mContent));
          }

          $mValue->setContents($aContents);
        }
        else {

          $mValue = $this->getWindow()->createInstruction($mValue);
        }
      }
    }

    return $mValue;
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