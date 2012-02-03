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

    $val = $window->stringToInstance($sFormat);

    $call = $window->createCall($window->getSelf(), self::ARGUMENT_METHOD, $val, array($sName, $bRequired));

    $call = $this->validateArgumentFormat($sName, $val);
    $var = $call->getVar();

    // argument is available, ie. for validation, direclty after format has been validated
    
    $this->setActionArgument($sName, $var);
    //$aResult[] = $call;

    if ($el->hasChildren()) {

      if ($validate = $el->getx('self:validate')) {

        if (!$bRequired) {

          $if = $window->create('condition', array($window, $var));

          $window->add($if);
          $window->setScope($if);

          $aResult[] = $this->reflectValidate($validate, $sName, $var);

          $window->stopScope();
        }
        else {

          $aResult[] = $this->reflectValidate($validate, $sName, $var);
        }
      }
    }



    return $aResult;
  }

  protected function validateArgumentFormat($sName, php\_instance $val) {

    $window = $this->getWindow();

    $call = $window->createCall($window->getSelf(), self::ARGUMENT_METHOD, $val, array($sName));
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

    return $this->getActionArgument($sName);
  }

  protected function reflectValidate(dom\element $el, $sArgument, php\_var $var) {

    $window = $this->getWindow();

    $bRequired = $el->testAttribute('required', true);
    $bReturn = $el->testAttribute('return', false);

    if ($el->countChildren() != 1) {

      $this->throwException(txt('Children expected in %s', $el->asToken()));
    }

    $result = $this->parseNode($el->getFirst());
    $validation = $window->createVar($result);

    $call = $window->createCall($window->getSelf(), 'validateArgument', 'php-boolean', array($sArgument, $var, $validation, $bRequired, $bReturn));
    return $window->create('assign', array($this->getWindow(), $var, $call));
  }
}