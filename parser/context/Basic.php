<?php

namespace sylma\parser\context;
use sylma\core, sylma\parser\context, sylma\dom;

class Basic extends core\module\Domed implements context {

  protected static $sArgumentClass = 'sylma\core\argument\Setable';
  protected $parent;

  public function __construct() {

    $this->setArguments(array());
  }

  public function shift($mValue) {

    $this->getArguments()->shift($mValue);
  }

  public function add($mValue) {

    if (is_array($mValue)) {

      foreach ($mValue as $mItem) {

        $this->getArguments()->add($mItem);
      }
    }
    else {

      $this->getArguments()->add($mValue);
    }
  }

  public function set($sPath, $mValue) {

    return $this->setArgument($sPath, $mValue);
  }

  public function asObject() {

    $result = null;
    $aArguments = $this->getArguments()->query();

    if (count($aArguments) > 1) {

      $this->throwException('Multiple values when object expected');
    }
    else if ($aArguments) {

      $result = reset($aArguments);
    }

    /*
    if (!is_object($result)) {

      $this->throwException('Result should be an object');
    }
    */
    return $result;
  }

  public function asArray() {

    $aResult = array();
    $aAction = $this->getArguments()->query();

    if (count($aAction) == 1 && is_array(current($aAction))) {

      $aResult = current($aAction);
    }
    else {

      $aResult = $aAction;
    }

    return $aResult;
  }

  public function asDOM() {

    $result = $this->asObject();

    if ($result && !$result instanceof dom\handler) {

      $result = $this->getManager('dom')->createDocument($result);
      //echo $this->show($result)->asString();
      //$this->throwException('Result should be a DOM object');
    }

    return $result;
  }

  public function asString() {

    $aResult = $this->getArguments()->asArray();

    return (string) implode('', $aResult);
  }
}
