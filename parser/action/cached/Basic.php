<?php

namespace sylma\parser\action\cached;
use sylma\core, sylma\parser, sylma\storage\fs;

require_once('core/module/Domed.php');
require_once('core/stringable.php');

require_once(dirname(__dir__) . '/cached.php');

abstract class Basic extends core\module\Domed implements parser\action\cached, core\stringable {

  //protected $bTemplate = false;
  protected $aActionArguments = array();

  protected $aResults = array();
  protected $bRunned = false;

  public function __construct(fs\directory $dir, parser\action $controler, array $aContexts, array $aArguments = array()) {

    require_once('parser/action.php');

    if (!array_key_exists(self::CONTEXT_DEFAULT, $aContexts)) {

      $aContexts[self::CONTEXT_DEFAULT] = $controler->getControler()->createContext();
    }

    $this->setContexts($aContexts);
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
  public function loadAction() {

    if (!$this->bRunned) {

      $aResult = $this->parseAction();
      $this->aResults[self::CONTEXT_DEFAULT]->set('', $aResult);
      $this->bRunned = true;
      
      //echo $this->show($this->aResults['default']->getArguments());
      //echo $this->show($aResult);
    }
    
    //return $this->aResults;
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
      $this->throwException(sprintf('Invalid argument type : string expected, %s given', $formater->asToken($sVal)));
    }

    return $sVal;
  }

  protected function getActionArgument($sName, $bRequired = true) {

    $mResult = null;

    if (!array_key_exists($sName, $this->aActionArguments)) {

      if ($bRequired) $this->throwException(sprintf('Unknow argument : %s', $sName));
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
      else $this->throwException(sprintf('Validation failed for argument %s', $sName));
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
      $this->throwException(sprintf('Invalid argument type : numeric expected, %s given', $formater->asToken($iVal)));
    }

    return $iVal + 0;
  }

  protected function validateArray($aVal) {

    if (!is_array($aVal)) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(sprintf('Invalid argument type : array expected, %s given', $formater->asToken($aVal)));
    }

    return $aVal;
  }

  protected function getContexts() {

    return $this->aResults;
  }

  public function setContexts(array $aContexts) {

    $this->aResults = $aContexts;
  }

  /**
   *
   * @param type $sContext
   * @return array|parser\context
   */
  public function getContext($sContext = self::CONTEXT_DEFAULT) {

    $mResult = null;

    if (array_key_exists($sContext, $this->aResults)) {

      $mResult = $this->aResults[$sContext];
    }

    return $mResult;
  }

  protected function validateObject($val, $sInterface) {

    if (!$val instanceof $sInterface) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(sprintf('Invalid argument type : object %s expected, %s given', $sInterface, $formater->asToken($val)));
    }

    return $val;
  }

  protected function getActionFile($sPath, array $aArguments) {

    $action = $this->create('action', array($this->getFile($sPath), $aArguments));
    $action->setContexts($this->getContexts());

    return $action;
  }

  public function asObject() {

    return $this->getContext()->asObject();
  }

  public function asArray() {
    
    return $this->getContext()->asArray();
  }

  public function asString($iMode = 0) {

    return $this->getContext()->asString();
  }

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $file = $this->getControler()->getFile();

    $mSender[] = $file->asToken();

    return parent::throwException($sMessage, $mSender, $iOffset);
  }
}
