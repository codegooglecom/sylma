<?php

namespace sylma\core\module;
use sylma\core;

require_once('Controled.php');

abstract class Argumented extends Controled {

  const FACTORY_CONTROLER = 'factory';

  /**
   * Class manager
   */
  private $reflector;
  private $aClasses = array();

  protected static $sArgumentClass = '\sylma\core\argument\Iterator';
  protected static $sArgumentFile = 'core/argument/Iterator.php';

  /**
   * Argument object linked to this module, contains various parameters for the module
   * @var core\argument
   */
  protected $arguments = null;

  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    $factory = $this->getControler(self::FACTORY_CONTROLER);

    if (array_key_exists($sName, $this->aClasses)) {

      $class = $this->aClasses[$sName];
    }
    else {

      if (!$this->getArguments()) {

        $this->throwException(sprintf('Cannot build object @class %s. No settings defined', $sName));
      }

      $factory->setSettings($this->getArguments());
      $class = $factory->findClass($sName, $aArguments, $sDirectory);

      $this->aClasses[$sName] = $class;
    }

    $result = $factory->createObject($class, $aArguments);

    return $result;
  }

  /**
   *
   * @param array $mArguments
   * @param string $sNamespace
   * @return core\argument
   */
  protected function createArgument($mArguments, $sNamespace = '') {

    require_once(static::$sArgumentFile);

    if ($sNamespace) $aNS = array($sNamespace);
    else if ($this->getNamespace()) $aNS = array($this->getNamespace());
    else $aNS = array();

    return new static::$sArgumentClass($mArguments, $aNS);
  }

  protected function setArguments($mArguments = null, $bMerge = true) {

    if ($mArguments !== null) {

      if (is_array($mArguments)) {

        if ($this->getArguments() && $bMerge) $this->getArguments()->mergeArray($mArguments);
        else $this->arguments = $this->createArgument($mArguments, $this->getNamespace());
      }
      else if (is_object($mArguments)) {

        if (!$mArguments instanceof core\argument) {

          $this->throwException('Illegal argument sent');
        }

        if ($this->getArguments() && $bMerge) {

          $this->getArguments()->merge($mArguments);
        }
        else $this->arguments = $mArguments;
      }
    }
    else {

      $this->arguments = null;
    }

    return $this->getArguments();
  }

  /**
   *
   * @return core\argument
   */
  protected function getArguments() {

    return $this->arguments;
  }

  protected function getArgument($sPath, $mDefault = null, $bDebug = false) {

    $mResult = $mDefault;

    if (!$this->getArguments()) $this->throwException('No arguments has been defined');

    $mResult = $this->getArguments()->get($sPath, $bDebug);
    if ($mResult === null && $mDefault !== null) $mResult = $mDefault;

    return $mResult;
  }

  protected function readArgument($sPath, $mDefault = null, $bDebug = false) {

    $mResult = $mDefault;

    if (!$this->getArguments()) $this->throwException('No arguments has been defined');

    $mResult = $this->getArguments()->read($sPath, $bDebug);
    if ($mResult === null && $mDefault !== null) $mResult = $mDefault;

    return $mResult;
  }

  protected function setArgument($sPath, $mValue) {

    if (!$this->getArguments()) {

      $this->setArguments(array());
    }

    return $this->getArguments()->set($sPath, $mValue);
  }

  protected function dsp() {

    $mArgument = func_get_args();
    if (count($mArgument) == 1) $mArgument = current ($mArgument);

    echo \Sylma::show($mArgument, false);
  }

  protected function show($mVar, $bToken = true) {

    return \Sylma::show($mVar, $bToken);
  }

  /**
   * Log a message
   * @param mixed|DOMNode|string|array $mMessage The message to send, will be parsed or stringed
   * @param string $sStatut The statut of the message : see @file /system/allowed-messages.xml for more infos
   */
  protected function log($mMessage, $sStatut = \Sylma::LOG_STATUT_DEFAULT) {

    return \Sylma::log($this->getNamespace(), $mMessage, $sStatut);
  }
}


