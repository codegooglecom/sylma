<?php

namespace sylma\parser\caller;
use sylma\core, sylma\parser\action\php, sylma\parser;

require_once('core/module/Argumented.php');

class Method extends core\module\Argumented {

  protected $sName;
  protected $sReturn;
  protected $aArguments = array();

  public function __construct(Domed $controler, core\argument $method) {

    $this->setControler($controler);
    $this->parseArgument($method);
  }

  protected function parseArgument(core\argument $method) {

    $aArguments = array();

    foreach ($method as $sArgument => $arg) {

      if ($sArgument != 'argument' || $arg->getNamespace() != $this->getControler()->getNamespace()) {

        $this->throwException(sprintf('Invalid %s, argument expected', $arg->asToken()));
      }

      $sName = $arg->read('@name');

      $aArguments[$sName] = array(
        'format' => $arg->read('@format'),
        'required' => $arg->read('@required', false),
      );
    }

    $this->sName = $method->read('@name');
    $this->sReturn = $method->read('@return');

    $this->setArguments($aArguments);
  }

  public function getReturn() {

    $sResult = '';
    $sReturn = $this->sReturn;

    if (substr($sReturn, 0, 4) == 'php-') {

      $sResult = $sReturn;
    }
    else {

      require_once('core/functions/path.php');

      $sResult = core\functions\path\toAbsolute($sReturn, $this->getControler()->getNamespace('php'), '\\');
    }

    return $sResult;
  }

  public function getName() {

    return $this->sName;
  }

  protected function validateArgument(core\argument $arg, $obj) {

    $bResult = false;
    $sFormat = $arg->read('format');

    if ($obj instanceof php\basic\Called) {

      $obj = $obj->getReturn();
    }
    else if ($obj instanceof php\basic\_Var) {

      $obj = $obj->getInstance();
    }

    if (substr($sFormat, 0, 4) == 'php-') {

      if ($obj instanceof php\_scalar) {

        $bResult = $obj->useFormat($sFormat);
      }
    }
    else {

      $interface = $obj->getInterface();
      $bResult = $interface->instanceOf($sFormat);
    }

    if (!$bResult) {

      $this->throwException(sprintf('Argument %s has bad format, %s expected', get_class($obj), $sFormat));
    }
  }

  protected function validateArguments(array $aArguments) {

    $iKey = 0;

    foreach ($this->getArguments() as $sKey => $arg) {

      if (array_key_exists($sKey, $aArguments)) {

        $aResult[$sKey] = $this->validateArgument($arg, $aArguments[$sKey]);
      }
      else {

        if (array_key_exists($iKey, $aArguments)) {

          $aResult[] = $this->validateArgument($arg, $aArguments[$iKey]);
        }
      }

      $iKey++;
      return false;
    }

    return true;
  }

  public function reflectCall(php\_window $window, php\basic\_ObjectVar $var, array $aArguments = array()) {

    $this->validateArguments($aArguments);

    $result = $window->createCall($var, $this->getName(), $window->stringToInstance($this->getReturn()), $aArguments);

    return $result;
  }
}