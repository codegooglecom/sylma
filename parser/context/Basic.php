<?php

namespace sylma\parser\context;
use sylma\core, sylma\parser\context, sylma\dom;

class Basic extends core\argument\Readable implements context {

  protected $parent;

  public function add($mValue, $bRef = false) {

    if (is_array($mValue)) {

      foreach ($mValue as $mItem) {

        parent::add($mItem);
      }
    }
    else {

     parent::add($mValue);
    }
  }

  public function asObject() {

    $result = null;
    $aArguments = $this->loadArray();

    if (count($aArguments) > 1) {

      $this->throwException('Multiple values when object expected');
    }
    else if ($aArguments) {

      $result = reset($aArguments);
    }

    return $result;
  }

  public function loadArray() {

    $aResult = array();
    $aAction = $this->query();

    if (count($aAction) == 1 && is_array(current($aAction))) {

      $aResult = current($aAction);
    }
    else {

      $aResult = $aAction;
    }

    return $aResult;
  }

  protected function createDocument($sElement = '') {

    return \Sylma::getManager('dom')->createDocument($sElement);
  }

  public function asDOM() {

    $result = $this->asObject();

    if ($result && !$result instanceof dom\handler) {

      $result = $this->createDocument($result);
    }

    return $result;
  }

  public function asString() {

    //$this->normalize();
    $aResult = $this->loadArray();

    return (string) implode('', $aResult);
  }
}
