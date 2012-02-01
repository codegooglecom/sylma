<?php

namespace sylma\parser\action\cached;
use sylma\core, sylma\parser, sylma\storage\fs;

require_once('core/module/Domed.php');

require_once(dirname(__dir__) . '/cached.php');
require_once('core/stringable.php');

abstract class Basic extends core\module\Domed implements parser\action\cached, core\stringable {

  //protected $bTemplate = false;

  public function __construct(fs\directory $dir, parser\action $controler, core\argument $args) {

    require_once('parser/action.php');

    $this->setControler($controler);
    $this->setDirectory($dir);
    $this->setNamespace(parser\action::NS);

    $this->loadDefaultArguments();
    $this->setArguments(array('arguments' => $args));
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
  }

  protected function validateObject($val, $sInterface) {

    if ($val instanceof $sInterface) {

      $formater = \Sylma::getControler('formater');
      $this->throwException(txt('Invalid argument type : object expected, %s given', $formater->asToken($val)));
    }
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

      $this->throwException(t('No valid array result'));
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
