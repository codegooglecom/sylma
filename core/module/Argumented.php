<?php

namespace sylma\core\module;
use sylma\core;

abstract class Argumented extends Managed {

  const FACTORY_MANAGER = 'factory';

  /**
   * Class manager
   */
  protected $factory;

  protected static $sArgumentClass = '\sylma\core\argument\Iterator';
  protected static $sArgumentFile = '/core/argument/Iterator.php';

  protected static $sFactoryFile = '/core/factory/Reflector.php';
  protected static $sFactoryClass = '\sylma\core\factory\Reflector';


  /**
   * Argument object linked to this module, contains various parameters for the module
   * @var core\argument
   */
  protected $arguments = null;

  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    return $this->getFactory()->create($sName, $aArguments, $sDirectory);
  }

  /**
   *
   * @return \sylma\core\factory
   */
  protected function getFactory() {

    if (!$this->factory) {

      $this->factory = $this->createFactory($this->getArguments());
    }

    return $this->factory;
  }

  protected function createFactory(core\argument $arg = null) {

    \Sylma::load(static::$sFactoryFile);
    $result = new static::$sFactoryClass($arg);

    return $result;
  }

  /**
   *
   * @param array $mArguments
   * @param string $sNamespace
   * @return \sylma\core\argument
   */
  protected function createArgument($mArguments, $sNamespace = '') {

    \Sylma::load(static::$sArgumentFile);

    if ($sNamespace) $aNS = array($sNamespace);
    else if ($this->getNamespace()) $aNS = array($this->getNamespace());
    else $aNS = array();

    return new static::$sArgumentClass($mArguments, $aNS);
  }

  protected function setArguments($mArguments = null, $bMerge = true) {

    if (is_null($mArguments)) {

      $this->arguments = null;
    }
    else {

      if ($this->getArguments()) {

        $this->getArguments()->merge($mArguments);
      }
      else {

        if (is_array($mArguments)) {

          $this->arguments = $this->createArgument($mArguments, $this->getNamespace());
        }
        else if ($mArguments instanceof core\argument) {

          $this->arguments = $mArguments;
        }
        else {

          $this->throwException('Illegal argument sent');
        }
      }
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


