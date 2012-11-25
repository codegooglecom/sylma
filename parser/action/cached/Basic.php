<?php

namespace sylma\parser\action\cached;
use sylma\core, sylma\parser, sylma\storage\fs;

abstract class Basic extends core\module\Domed implements parser\action\cached, core\stringable {

  //protected $bTemplate = false;
  protected $aActionArguments = array();

  protected $aResults = array();
  protected $bRunned = false;

  protected $handler;

  /**
   *
   * @param \sylma\storage\fs\file $file The php file containing instructions
   * @param \sylma\storage\fs\directory $dir
   * @param \sylma\parser\action $handler
   * @param array $aContexts
   * @param \sylma\core\argument $arguments
   */
  public function __construct(fs\file $file, fs\directory $dir, parser\action $handler, array $aContexts, array $aArguments = array(), array $aControlers = array()) {

    foreach ($aControlers as $sName => $controler) {

      $this->setControler($controler, $sName);
    }

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
    $handler = $this->getHandler();

    include($file->getRealPath());

    return $aArguments;
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

  protected function loadStringable(core\stringable $val, $iMode = 0) {

    return $val->asString($iMode);
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
