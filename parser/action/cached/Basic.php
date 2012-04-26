<?php

namespace sylma\parser\action\cached;
use sylma\core, sylma\parser, sylma\storage\fs;

require_once('core/module/Domed.php');
require_once('core/stringable.php');

require_once(dirname(__dir__) . '/cached.php');

abstract class Basic extends core\module\Domed implements parser\action\cached, core\stringable {

  //protected $bTemplate = false;
  protected $aActionArguments = array();

  protected $aResults = null;
  protected $bRunned = false;

  public function __construct(fs\directory $dir, parser\action $controler, array $aContexts, array $aArguments = array()) {

    require_once('parser/action.php');

    $this->aContexts = $aContexts;
    $this->setControler($controler);
    $this->setDirectory($dir);
    $this->setNamespace(parser\action::NS);

    $this->loadDefaultArguments();
    $this->setArgumentsArray($aArguments);
  }

  public function setArgumentsArray(array $aArguments) {

    $this->aActionArguments = $aArguments;
  }

  /**
   *
   * @return array
   */
  abstract protected function runAction();

  protected function parseAction() {

    return $this->runAction();
  }

  /**
   * Allow management of multiple calls on same action
   * @return array|mixed
   */
  protected function loadAction() {

    $aResults = null;

    if (!$this->bRunned) {

      $aResults = $this->aResults = $this->parseAction();
      $this->bRunned = true;
    }
    else {

      $aResults = $this->aResults;
    }

    return $aResults;
  }

  protected function loadArgumentable(core\argumentable $val = null) {

    if (!$val) return null;

    $arg = $val->asArgument();

    return $this->loadDomable($arg);
  }

  protected function loadStringable(core\stringable $val, $iMode = 0) {

    return $val->asString($iMode);
  }

  protected function validateString($sVal) {

    if (!is_string($sVal)) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Invalid argument type : string expected, %s given', $formater->asToken($sVal)));
    }

    return $sVal;
  }

  protected function getActionArgument($sName, $bRequired = true) {

    $mResult = null;

    if (!array_key_exists($sName, $this->aActionArguments)) {

      if ($bRequired) $this->throwException(txt('Unknow argument : %s', $sName));
    }
    else {

      $mResult = $this->aActionArguments[$sName];
    }

    return $mResult;
  }

  protected function validateArgument($sName, $mVar, $mVal, $bRequired = true, $bReturn = false, $bDefault = false) {

    $mResult = null;

    if ($bRequired && (is_null($mVal) || $mVal === false)) {

      if ($bDefault) $mResult = null;
      else $this->throwException(txt('Validation failed for argument %s', $sName));
    }

    if (!$bDefault) {

      if ($bReturn) $mResult = $mVal;
      else $mResult = $mVar;
    }

    return $mResult;
  }

  protected function validateNumeric($iVal) {

    if (!is_numeric($iVal)) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Invalid argument type : numeric expected, %s given', $formater->asToken($iVal)));
    }

    return $iVal + 0;
  }

  protected function validateArray($aVal) {

    if (!is_array($aVal)) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Invalid argument type : array expected, %s given', $formater->asToken($aVal)));
    }

    return $aVal;
  }

  protected function loadActionContexts(parser\action\cached $action) {

    foreach ($this->aContexts as $sContext) {

      $this->aContexts[$sContext] += $action->getContext($sContext);
    }
  }

  public function getContext($sContext = self::CONTEXT_DEFAULT) {

    $aResult = array();
    $aContexts = $this->loadAction();

    if (array_key_exists($sContext, $aContexts)) {

      $aResult = $aContexts[$sContext];
    }

    return $aResult;
  }

  protected function validateObject($val, $sInterface) {

    if (!$val instanceof $sInterface) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Invalid argument type : object %s expected, %s given', $sInterface, $formater->asToken($val)));
    }

    return $val;
  }

  protected function getActionFile($sPath, array $aArguments) {

    return $this->create('action', array($this->getFile($sPath), $aArguments));
  }

  public function asObject() {

    $aResult = $this->getContext();

    if (!$aResult) {

      $this->throwException(txt('No valid object result'));
    }

    return array_pop($aResult);
  }

  public function asArray() {

    $aAction = array_values($this->getContext());

    if (count($aAction) == 1 && is_array(current($aAction))) $aResult = current($aAction);
    else $aResult = $aAction;

    return $aResult;
  }

  public function asString($iMode = 0) {

    $mResult = $this->getContext();

    return (string) implode('', $mResult);
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $file = $this->getControler()->getFile();

    $mSender[] = $file->asToken();

    return parent::throwException($sMessage, $mSender, $iOffset);
  }
}
