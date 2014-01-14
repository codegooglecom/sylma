<?php

namespace sylma\parser\languages\common\basic;
use sylma\core, sylma\parser\languages\common;

class Concat extends common\basic\Controled implements common\addable, common\argumentable {

  protected $aValues = array();

  public function __construct(common\_window $controler, array $aContent) {

    $this->setControler($controler);
    $this->setValues($this->getWindow()->flattenArray($aContent));

    if (!$this->getValues()) {

      $this->getControler()->throwException('No value defined for string');
    }
  }

  protected function fusionStrings(array $aContent) {

    $aResult = array();
    $bString = false;

    foreach ($aContent as $mContent) {

      if (is_string($mContent)) {

        if ($bString) {

          $aResult[count($aResult) - 1] .= $mContent;
        }
        else {

          $aResult[] = $mContent;
        }

        $bString = true;
      }
      else if ($mContent instanceof common\stringable) {

        if ($bString) {

          $aResult[count($aResult) - 1] .= $mContent->asString();
        }
        else {

          $aResult[] = $mContent->asString();
        }

        $bString = true;
      }
      else {

        $aResult[] = $mContent;
        $bString = false;
      }
    }

    return $aResult;
  }

  protected function toString($mValue) {

    if ($mValue instanceof common\argumentable) {

      $result = $mValue->asArgument();
    }
    else {

      $result = $this->getControler()->toString($mValue);
      //$result = $mValue;
    }

    return $result;
  }

  protected function convertContent(array $aContent) {

    $aContent = $this->fusionStrings($aContent);

    $aResult = array();

    foreach ($aContent as $mContent) {

      $aResult[] = is_string($mContent) ? $this->getWindow()->createString($mContent, null, true) : $mContent;
    }

    return $aResult ? $aResult : null;
  }

  protected function setValues($aValues) {

    $this->aValues = $aValues;
  }

  protected function getValues() {

    return $this->aValues;
  }

  public function onAdd() {

    $this->getControler()->loadContent($this->aValues);
  }

  public function asArgument() {

    $aValues = $this->convertContent($this->aValues);

    if (count($aValues) === 1) {

      $aResult = array(current($aValues));
    }
    else {

      $aResult = array('concat' => $aValues);
    }

    return $this->getControler()->createArgument($aResult);
  }
}

