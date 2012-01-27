<?php

namespace sylma\parser\action\cached;
use sylma\core, sylma\parser, sylma\storage\fs;

require_once('core/module/Domed.php');

require_once(dirname(__dir__) . '/cached.php');
require_once('core/stringable.php');

abstract class Basic extends core\module\Domed implements parser\action\cached, core\stringable {

  //protected $bTemplate = false;

  public function __construct(fs\directory $dir, core\factory $controler, core\argument $args) {

    require_once('parser/action.php');

    $this->setControler($controler);
    $this->setDirectory($dir);
    $this->setNamespace(parser\action::NS);
    $this->setArguments($args);
  }

  /**
   *
   * @return array
   */
  abstract protected function runAction();

  protected function useTemplate() {

    return $this->bTemplate;
  }

  /**
   *
   * @return array|mixed
   */
  protected function parseAction() {

    $mResult = null;
    $aArguments = $this->runAction();

    if ($this->useTemplate()) {

      $mResult = $this->loadTemplate($aArguments);
    }
    else {

      $mResult = $aArguments;
    }

    return $mResult;
  }

  protected function loadArgumentable(core\argumentable $val = null) {

    if (!$val) return null;

    $arg = $val->asArgument();

    return $this->loadDomable($arg);
  }

  protected function validateString($sVal) {

    if (!is_string($sVal)) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Invalid argument type : string expected, %s given', $formater->asToken($sVal)));
    }
  }

  protected function validateObject($val, $sInterface) {

    if ($val instanceof $sInterface) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Invalid argument type : object expected, %s given', $formater->asToken($val)));
    }
  }

  public function asObject() {

    return $this->parseAction();
  }

  public function asString() {

    $mResult = $this->parseAction();

    /*
    if (is_object($mVal)) {

      $sResult = (string) $mVal;
    }
    else if (is_string($mVal)) {

      $sResult = $mVal;
    }
    else if (is_array($mVal)) {

      $this->throwException(t('Cannot stringed array result'));
    }

    return $sResult;*/

    return (string) implode('', $mResult);
  }
}
