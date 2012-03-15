<?php

namespace sylma\parser\action\compiler;
use \sylma\core, \sylma\dom, \sylma\parser, \sylma\storage\fs, \sylma\parser\action\php;

require_once('Domed.php');

abstract class Argumented extends Domed {

  const ARGUMENT_METHOD = 'getActionArgument';

  protected $aActionArguments = array();

  protected function setActionArgument($sName, php\_var $var) {

    $this->aActionArguments[$sName] = $var;
  }

  /**
   *
   * @param type $sName
   * @return php\_var
   */
  protected function getActionArgument($sName) {

    if (!array_key_exists($sName, $this->aActionArguments)) {

      $this->throwException(txt('Argument %s does not exists', $sName));
    }

    $var = $this->aActionArguments[$sName];

    return $var;
  }

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
    $bRequired = $el->testAttribute('required', true);
    $validate = $default = null;

    if ($el->hasChildren()) {

      $default = $el->getx('self:default', array(), false);
      $validate = $el->getx('self:validate', array(), false);
    }

    $val = $window->stringToInstance($sFormat);

    $callArgument = $window->createCall($window->getSelf(), self::ARGUMENT_METHOD, $val, array($sName, $bRequired));
    $var = $callArgument->getVar();

    $callFormat = $this->validateArgumentFormat($val, $callArgument);

    if (!$bRequired) {

      $if = $window->create('condition', array($window, $callArgument));

      $window->add($if);
      $window->setScope($if);
    }

    $assign = $window->create('assign', array($window, $var, $callFormat, $callFormat->getReturn()));
    $window->add($assign);
    //$var = $callFormat->getVar();
    // argument is available direclty after format has been checked, ie. for validation

    $this->setActionArgument($sName, $var);

    if ($validate) {

      $callValidate = $this->reflectValidate($validate, $sName, $var, (bool) $default);
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

    if ($val instanceof php\_scalar) {

      if ($val instanceof php\basic\instance\_String) {

        $call = $window->createCall($window->getSelf(), 'validateString', $bool, array($call));
      }
      else if ($val instanceof php\basic\instance\_Numeric) {

        $call = $window->createCall($window->getSelf(), 'validateNumeric', $bool, array($call));
      }
      else if ($val instanceof php\basic\instance\_Array) {

        $call = $window->createCall($window->getSelf(), 'validateArray', $bool, array($call));
      }
    }
    else if ($val instanceof php\_object) {

      $interface = $val->getInterface();
      $call = $window->createCall($window->getSelf(), 'validateObject', $bool, array($call, $interface->getName()));
    }

    return $call;
  }

  protected function reflectGetArgument(dom\element $el) {

    $sName = $el->getAttribute('name');

    $arg = $this->getActionArgument($sName);
    $instance = $arg->getInstance();

    $aResult = array();
    $children = $el->getChildren();

    $aResult = array_merge($aResult, $this->runConditions($arg, $children));
    if ($instance instanceof php\_object) $aResult = array_merge($aResult, $this->runVar($arg, $children));

    if (!$aResult) $aResult[] = $arg;

    return count($aResult) == 1 ? reset($aResult) : $aResult;
  }

  protected function reflectDefault(dom\element $el, php\_var $var) {

    $window = $this->getWindow();

    if ($el->countChildren() != 1) {

      $this->throwException(txt('One child expected in %s', $el->asToken()));
    }

    $bReturn = $el->testAttribute('return', true);

    $isnull = $window->create('function', array($window, '\is_null', $window->stringToInstance('php-boolean'), array($var)));
    $if = $window->create('condition', array($window, $isnull));

    $window->add($if);
    $window->setScope($if);

    $varDefault = $window->addVar($this->parseNode($el->getFirst()));

    if ($bReturn) {

      $assign = $window->create('assign', array($this->getWindow(), $var, $varDefault));
      $window->add($assign);
    }

    $window->stopScope();
  }

  protected function reflectValidate(dom\element $el, $sArgument, php\_var $var, $bDefault = false) {

    $window = $this->getWindow();

    $bRequired = $el->testAttribute('required', true);
    $bReturn = $el->testAttribute('return', false);

    if ($el->countChildren() != 1) {

      $this->throwException(txt('One child expected in %s', $el->asToken()));
    }

    $result = $this->parseNode($el->getFirst());
    $validation = $window->addVar($result);

    $call = $window->createCall($window->getSelf(), 'validateArgument', 'php-boolean', array($sArgument, $var, $validation, $bRequired, $bReturn, $bDefault));
    return $window->create('assign', array($this->getWindow(), $var, $call));
  }
}