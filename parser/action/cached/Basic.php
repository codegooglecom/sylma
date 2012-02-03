<?php

namespace sylma\parser\action\cached;
use sylma\core, sylma\parser, sylma\storage\fs;

require_once('core/module/Domed.php');

require_once(dirname(__dir__) . '/cached.php');
require_once('core/stringable.php');

abstract class Basic extends core\module\Domed implements parser\action\cached, core\stringable {

  //protected $bTemplate = false;
  protected $aActionArguments = array();

  public function __construct(fs\directory $dir, parser\action $controler, array $aArguments) {

    require_once('parser/action.php');

    $this->setControler($controler);
    $this->setDirectory($dir);
    $this->setNamespace(parser\action::NS);

    $this->loadDefaultArguments();
    $this->aActionArguments = $aArguments;
  }

  /**
   *
   * @return array
   */
  abstract protected function runAction();

  /**
   *
   * @return array|mixed
   */
  protected function parseAction() {

    return $this->runAction();
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

  protected function validateArgument($sName, $mVar, $mVal, $bRequired = true, $bReturn = false) {

    if ($bRequired && (is_null($mVal) || $mVal === false)) {

      $this->throwException(txt('Validation failed for argument %s', $sName));
    }

    if ($bReturn) $mResult = $mVal;
    else $mResult = $mVar;

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

  protected function validateObject($val, $sInterface) {

    if ($val instanceof $sInterface) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Invalid argument type : object expected, %s given', $formater->asToken($val)));
    }

    return $val;
  }

  protected function getActionFile($sPath, array $aArguments) {

    return $this->create('action', array($this->getFile($sPath), $aArguments));
  }

  public function asObject() {

    $aResult = $this->parseAction();

    if (!$aResult) {

      $this->throwException(txt('No valid object result'));
    }

    return array_pop($aResult);
  }

  public function asArray() {

    $aResult = array_values($this->parseAction());

    if (count($aResult) == 1) $aResult = array_pop($aResult);

    if (!is_array($aResult)) {

      $formater = $this->getControler('formater');
      $this->throwException(txt('Invalid %s, array expected', $formater->asToken($aResult)));
    }

    return $aResult;
  }

  public function asString($iMode = 0) {

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

  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {

    $file = $this->getControler()->getFile();

    $mSender[] = $file->asToken();

    return parent::throwException($sMessage, $mSender, $iOffset);
  }
}
