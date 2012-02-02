<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs;

require_once('Domed.php');

abstract class Argumented extends Domed {

  protected $aActionArguments = array();

  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  protected function reflectArgument(dom\element $el) {

    $aResult = array();
    $window = $this->getWindow();

    $sName = $el->getAttribute('name');
    $sFormat = $el->getAttribute('format');

    $val = $window->stringToInstance($sFormat);

    $call = $window->createCall($window->getSelf(), 'getArgument', $val, array($sName));
    $bool = $window->stringToInstance('php-boolean');

    if ($val instanceof php\_scalar) {

      if ($val instanceof php\basic\instance\_String) {

        $aResult[] = $window->createCall($window->getSelf(), 'validateString', $bool, array($call));
      }
      else if ($val instanceof php\basic\instance\_Numeric) {

        $aResult[] = $window->createCall($window->getSelf(), 'validateNumeric', $bool, array($call));
      }
      else if ($val instanceof php\basic\instance\_Array) {

        $aResult[] = $window->create('function', array($window, 'is_array', $bool, array($call)));
      }
    }
    else if ($val instanceof php\_object) {

      $interface = $val->getInterface();
      $aResult[] = $window->createCall($window->getSelf(), 'validateObject', $bool, array($call, $interface->getName()));
    }

    if ($el->hasChildren()) {

      if ($validate = $el->get('self:validate')) {


      }
    }

    return $aResult;
  }

  protected function getActionArgument($sName) {

    if (!array_key_exists($sName, $this->aActionArguments)) {

      $this->throwException(txt('Argument %s does not exists', $sName));
    }

    //return $this->
  }

  protected function reflectGetArgument(dom\element $el) {

    $window = $this->getWindow();
    $sName = $el->getAttribute('name');

    if (!$mVal = $this->getActionArgument($sName)) {

      $this->throwException(txt('Unknown argument : %s', $sName));
    }

    return $window->createCall($window->getSelf(), 'getActionArgument', $window->loadInstance($mVal), array($sName));
  }

}