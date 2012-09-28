<?php

namespace sylma\parser\context;
use sylma\core, sylma\parser, sylma\dom;

require_once('core/module/Argumented.php');
require_once(dirname(__dir__) . '/context.php');
//require_once('core/argumentable.php');

class Basic extends core\module\Domed implements parser\context {

  protected $parent;

  public function __construct() {

    $this->setArguments(array());
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

    $aArguments = $this->getArguments()->query();

    if (count($aArguments) > 1) {

      $this->throwException('Multiple values when object expected');
    }

    $result = reset($aArguments);
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

    if (!$result instanceof dom\node) {

      $result = $this->getControler('dom')->createDocument($result);
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
