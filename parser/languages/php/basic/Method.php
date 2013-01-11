<?php

namespace sylma\parser\languages\php\basic;
use sylma\core, sylma\parser\languages\common, sylma\parser\languages\php, sylma\parser;

class Method extends core\module\Argumented {

  protected $sName;
  protected $return;
  protected $aArguments = array();
  protected $reflection;

  function __construct(_Interface $parent, $sName) {

    $this->setManager($parent);
    $this->setName($sName);

    try {

      $reflection = new \ReflectionMethod($parent->getName(), $sName);
      $this->setReflection($reflection);
    }
    catch (\ReflectionException $e) {

      $this->throwException($e->getMessage());
    }

    $this->loadArguments();
  }

  protected function getReflection() {

    return $this->reflection;
  }

  protected function setReflection(\ReflectionMethod $reflection) {

    $this->reflection = $reflection;
  }

  protected function loadArguments() {

    $aArguments = array();
    $method = $this->getReflection();

    foreach ($method->getParameters() as $parameter) {

      $sName = $parameter->getName();

      if ($parameter->isArray()) {

        $sFormat = 'php-array';
      }
      else if ($type = $parameter->getClass()) {

        $sFormat = $type->getName();
      }
      else {

        switch ($sName{0}) {

          case 's' : $sFormat = 'php-string'; break;
          case 'b' : $sFormat = 'php-boolean'; break;
          case 'a' : $sFormat = 'php-array'; break;
          case 'i' : $sFormat = 'php-integer'; break;
          case 'f' : $sFormat = 'php-float'; break;
        }

        if (!$sFormat) {

          $this->throwException('Unknown format for parameter');
        }
      }

      $sName = strtolower(substr($sName, 1));

      $aArguments[$sName] = array(
        'format' => $sFormat,
        'required' => !$parameter->isOptional(),
      );
    }

    $this->sName = $method->getName();
    $this->loadReturn();

    $this->setArguments($aArguments);
  }

  protected function loadReturn() {

    $sComment = $this->getReflection()->getDocComment();
    preg_match('/@return ([\w\\\\\|]*)/', $sComment, $aMatches);

    if (!isset($aMatches[1])) {

      $this->throwException('Cannot find return value');
    }

    $aParameters = explode('|', $aMatches[1]);

    if (false !== $iKey = array_search('null', $aParameters)) {

      unset($aParameters[$iKey]);
    }

    if (count($aParameters) > 1) {

      $this->throwException('Cannot handle more than one type in return');
    }

    $this->return = $this->loadInstance(current($aParameters));

    if (!$this->return) {

      $this->throwException(sprintf('Cannot find return value'));
    }
  }

  protected function loadInstance($sFormat) {

    switch ($sFormat) {

      case 'string' : $sToken = 'php-string'; break;
      case 'array' : $sToken = 'php-array'; break;
      case 'null' : $sToken = 'php-null'; break;
      case 'bool' :
      case 'boolean' : $sToken = 'php-boolean'; break;
      default : $sToken = $sFormat;
    }

    return $this->getManager()->getWindow()->tokenToInstance($sToken);
  }

  public function getReturn() {

    return $this->return;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  protected function validateArgument($sFormat, $obj) {

    $bResult = false;

    if ($obj instanceof php\basic\Called) {

      $obj = $obj->getReturn();
    }
    else if ($obj instanceof php\basic\_Var) {

      $obj = $obj->getInstance();
    }

    if (substr($sFormat, 0, 4) == 'php-') {

      if ($obj instanceof common\_scalar) {

        $bResult = $obj->useFormat($sFormat);
      }
    }
    else {

      if ($obj instanceof common\_object) {

        $interface = $obj->getInterface();
        $bResult = $interface->isInstance($sFormat);
      }
    }

    if (!$bResult) {

      $this->throwException(sprintf('Bad argument : %s, %s expected', $this->show($obj), $sFormat));
    }
  }

  protected function validateArguments(array $aArguments) {

    $iKey = 0;

    foreach ($this->getArguments() as $sKey => $aArgument) {

      if (array_key_exists($sKey, $aArguments)) {

        $aResult[$sKey] = $this->validateArgument($aArgument['format'], $aArguments[$sKey]);
      }
      else {

        if (array_key_exists($iKey, $aArguments)) {

          $aResult[] = $this->validateArgument($aArgument['format'], $aArguments[$iKey]);
        }
      }

      $iKey++;
      return false;
    }

    return true;
  }

  public function reflectCall(common\_window $window, php\basic\_ObjectVar $var, array $aArguments = array()) {

    $this->validateArguments($aArguments);

    $result = $window->createCall($var, $this->getName(), $this->getReturn(), $aArguments);

    return $result;
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $mSender[] = '@class ' . $this->getManager()->getName();
    $mSender[] = '@method ' . $this->getName();

    parent::throwException($sMessage, $mSender);
  }
}