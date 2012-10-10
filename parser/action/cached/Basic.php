<?php

namespace sylma\parser\action\cached;
use sylma\core, sylma\parser, sylma\storage\fs;

abstract class Basic extends core\module\Domed implements parser\action\cached, core\stringable {

  //protected $bTemplate = false;
  protected $aActionArguments = array();

  protected $aResults = array();
  protected $bRunned = false;

  protected $file;
  protected $handler;

  /**
   *
   * @param \sylma\storage\fs\file $file The php file containing instructions
   * @param \sylma\storage\fs\directory $dir
   * @param \sylma\parser\action $handler
   * @param array $aContexts
   * @param \sylma\core\argument $arguments
   */
  public function __construct(fs\file $file, fs\directory $dir, parser\action $handler, array $aContexts, array $aArguments = array()) {

    require_once('parser/action.php');

    if (!array_key_exists(self::CONTEXT_DEFAULT, $aContexts)) {

      $aContexts[self::CONTEXT_DEFAULT] = $handler->getControler()->createContext();
    }

    $this->setFile($file);

    $this->setContexts($aContexts);
    $this->setHandler($handler);

    $this->setDirectory($dir);
    $this->setNamespace(parser\action::NS);

    $this->loadDefaultArguments();
    $this->setActionArguments($aArguments);
  }

  public function getHandler() {

    return $this->handler;
  }

  public function setHandler(parser\action $handler) {

    $this->handler = $handler;
  }

  public function setActionArguments(array $aArguments) {

    $this->aActionArguments = $aArguments;
  }

  protected function getActionArgument($sName, $bRequired = true) {

    $mResult = null;

    if (!array_key_exists($sName, $this->aActionArguments)) {

      if ($bRequired) $this->throwException(sprintf('Missing argument : %s', $sName));
    }
    else {

      $mResult = $this->aActionArguments[$sName];
    }

    return $mResult;
  }

  /**
   *
   * @return array|mixed
   */
  protected function runAction(fs\file $file) {

    $aArguments = array();

    include($file->getRealPath());

    return $aArguments;
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  protected function getFile($sPath = '', $bDebug = true) {

    $result = $sPath ? parent::getFile($sPath, $bDebug) : $this->file;

    return $result;
  }

  /**
   * Allow management of multiple calls on same action
   * @return array|mixed
   */
  public function loadAction() {

    if (!$this->bRunned) {

      $mResult = $this->runAction($this->getFile());
      $this->aResults[self::CONTEXT_DEFAULT]->set('', $mResult);
      $this->bRunned = true;

      //echo $this->show($this->aResults['default']->getArguments());
      //echo $this->show($this->runAction());
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

  public function getParentParser($bRoot = false) {

    return $this->getHandler()->getParentParser($bRoot);
  }

  public function setContexts(array $aContexts) {

    $this->aResults = $aContexts;
  }

  /**
   *
   * @param type $sContext
   * @return parser\context
   */
  public function getContext($sContext = self::CONTEXT_DEFAULT, $bDebug = true) {

    $mResult = null;

    if (array_key_exists($sContext, $this->aResults)) {

      $mResult = $this->aResults[$sContext];
    }
    else if ($bDebug) {

      $this->throwException(sprintf('Context %s does not exists', $sContext));
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

  protected function getActionFile($sPath, array $aArguments = array()) {

    $action = $this->create('action', array($this->getFile($sPath), $aArguments));
    $action->setContexts($this->getContexts());
    $action->setParentParser($this->getHandler());

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

    $mSender[] = $this->getFile()->asToken();

    return parent::throwException($sMessage, $mSender, $iOffset);
  }
}
