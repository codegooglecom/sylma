<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs, \sylma\parser\languages\common, sylma\parser\languages\php;

require_once('Domed.php');

abstract class Argumented extends Domed {

  const ARGUMENT_METHOD = 'getActionArgument';

  protected $aActionArguments = array();
  protected $iArgument = 0;

  protected function setActionArgument($mKey, common\_var $var) {

    $this->aActionArguments[$mKey] = $var;
  }

  /**
   *
   * @param string|integer $mKey
   * @return common\_var
   */
  protected function getActionArgument($mKey) {

    if (!array_key_exists($mKey, $this->aActionArguments)) {

      $this->throwException(sprintf('Argument %s does not exists', $mKey));
    }

    $var = $this->aActionArguments[$mKey];

    return $var;
  }

  protected function getArgumentIndex() {

    return $this->iArgument++;
  }
  /**
   *
   * @param dom\element $el
   * @return core\argumentable|array|null
   */
  protected function reflectArgument(dom\element $el) {

    $aResult = array();
    $window = $this->getWindow();

    $mKey = $el->readAttribute('name', null, false);

    if (!$mKey) $mKey = $this->getArgumentIndex();

    $sFormat = $el->readAttribute('format');
    $bRequired = $el->testAttribute('required', true);
    $validate = $default = null;

    if ($el->hasChildren()) {

      $default = $el->getx('self:default', array(), false);
      $validate = $el->getx('self:validate', array(), false);
    }

    $val = $window->stringToInstance($sFormat);

    $callArgument = $window->createCall($window->getSelf(), self::ARGUMENT_METHOD, $val, array($mKey, $bRequired));
    $var = $callArgument->getVar();

    $callFormat = $this->validateArgumentFormat($val, $callArgument);

    if (!$bRequired) {

      $if = $window->createCondition($callArgument);

      $window->add($if);
      $window->setScope($if);
    }

    $assign = $window->create('assign', array($window, $var, $callFormat));
    $window->add($assign);
    //$var = $callFormat->getVar();
    
    // argument is available direclty after format has been checked, ie. for validation

    $this->setActionArgument($mKey, $var);

    if ($validate) {

      $callValidate = $this->reflectValidate($validate, $mKey, $var, (bool) $default);
      $window->add($callValidate);
    }

    if (!$bRequired) {

      $window->stopScope();
    }

    if ($default) {

      $this->reflectDefault($default, $var);
    }

    return $aResult;
  }

  protected function validateArgumentFormat($val, php\basic\CallMethod $call) {

    $window = $this->getWindow();

    $bool = $window->stringToInstance('php-boolean');

    if ($val instanceof common\_scalar) {

      if ($val instanceof php\basic\instance\_String) {

        $call = $window->createCall($window->getSelf(), 'validateString', $window->stringToInstance('php-string'), array($call));
      }
      else if ($val instanceof php\basic\instance\_Numeric) {

        $call = $window->createCall($window->getSelf(), 'validateNumeric', $window->stringToInstance('php-numeric'), array($call));
      }
      else if ($val instanceof php\basic\instance\_Array) {

        $call = $window->createCall($window->getSelf(), 'validateArray', $window->stringToInstance('php-array'), array($call));
      }
    }
    else if ($val instanceof common\_object) {

      $interface = $val->getInterface();
      $call = $window->createCall($window->getSelf(), 'validateObject', $val, array($call, $interface->getName()));
    }

    return $call;
  }

  protected function reflectGetArgument(dom\element $el) {

    if (!$mKey = $el->readAttribute('name', null, false)) {

      $mKey = (integer) $el->readAttribute('index');
    }

    $arg = $this->getActionArgument($mKey);
    $instance = $arg->getInstance();

    $aResult = array();
    $children = $el->getChildren();

    $aResult = array_merge($aResult, $this->runConditions($arg, $children));

    if ($instance instanceof common\_object) {

      $aResult = array_merge($aResult, $this->runVar($arg, $children));
    }

    if (!$aResult) $aResult[] = $arg;

    return count($aResult) == 1 ? reset($aResult) : $aResult;
  }

  protected function reflectDefault(dom\element $el, common\_var $var) {

    $window = $this->getWindow();

    if ($el->countChildren() != 1) {

      $this->throwException(sprintf('One child expected in %s', $el->asToken()));
    }

    $bReturn = $el->testAttribute('return', true);

    $isnull = $window->createFunction('\is_null', $window->stringToInstance('php-boolean'), array($var));
    $if = $window->createCondition($isnull);

    $window->add($if);
    $window->setScope($if);

    $mResult = $this->parseNode($el->getFirst());
    $varDefault = $window->addVar($mResult);

    if ($bReturn) {

      $assign = $window->create('assign', array($this->getWindow(), $var, $varDefault));
      $window->add($assign);
    }

    $window->stopScope();
  }

  protected function reflectValidate(dom\element $el, $sArgument, common\_var $var, $bDefault = false) {

    $window = $this->getWindow();

    $bRequired = $el->testAttribute('required', true);
    $bReturn = $el->testAttribute('return', false);

    if ($el->countChildren() != 1) {

      $this->throwException(sprintf('One child expected in %s', $el->asToken()));
    }

    $result = $this->parseNode($el->getFirst());
    $validation = $window->addVar($result);

    if ($bReturn) {

      $this->setActionArgument($sArgument, $result);
    }

    $call = $window->createCall($window->getSelf(), 'validateArgument', 'php-boolean', array($sArgument, $var, $validation, $bRequired, $bReturn, $bDefault));
    return $window->create('assign', array($this->getWindow(), $var, $call));
  }
}

