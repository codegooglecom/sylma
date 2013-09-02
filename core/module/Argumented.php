<?php

namespace sylma\core\module;
use sylma\core;

abstract class Argumented extends Managed {

  const FACTORY_MANAGER = 'factory';

  /**
   * Class manager
   */
  protected $factory;

  protected static $sArgumentClass = '\sylma\core\argument\Readable';
  protected static $sFactoryClass = '\sylma\core\factory\Cached';

  /**
   * Argument object linked to this module, contains various parameters for the module
   * @var core\argument
   */
  protected $arguments = null;
  protected $settings;

  public function create($sName, array $aArguments = array(), $sDirectory = '') {

    return $this->getFactory()->create($sName, $aArguments, $sDirectory);
  }

  /**
   *
   * @return \sylma\core\factory
   */
  protected function getFactory($bCreate = true) {

    if (!$this->factory && $bCreate) {

      //$args = $this->getSettings(false) ? $this->getSettings() : $this->getArguments();
      if (!$args = $this->getSettings(false)) {

        $args = $this->getArguments();
      }

      $this->factory = $this->createFactory($args);
    }

    return $this->factory;
  }

  protected function createFactory(core\argument $arg = null) {

    //\Sylma::load(static::$sFactoryFile);
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

    //\Sylma::load(static::$sArgumentFile);

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

      if ($this->getArguments() && $bMerge) {

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

    if ($factory = $this->getFactory(false)) {

      $factory->setArguments($this->getArguments());
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

  protected function getArgument($sPath, $bDebug = true, $mDefault = null) {

    $mResult = $mDefault;

    if (!$this->getArguments()) $this->throwException('No arguments has been defined');

    $mResult = $this->getArguments()->get($sPath, $bDebug);
    if ($mResult === null && $mDefault !== null) $mResult = $mDefault;

    return $mResult;
  }

  protected function readArgument($sPath, $bDebug = true, $mDefault = null) {

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

  protected function setSettings($arg) {

    if (is_null($arg)) {

      $this->arguments = null;
    }
    else {

      if (!is_object($arg)) {

        $arg = $this->createArgument($arg);
      }

      if ($this->settings) {

        $this->settings->merge($arg);
      }
      else {

        $this->settings = $arg;
      }

      if ($factory = $this->getFactory(false)) {

        $factory->setArguments($this->getSettings());
      }
    }
  }

  protected function translate() {

    $aArguments = func_get_args();
    $sValue = array_shift($aArguments);

    // translate !

    array_unshift($aArguments, $sValue);

    return call_user_func_array('sprintf', $aArguments);
  }

  /**
   * @return core\argument
   */
  protected function getSettings($bDebug = true) {

    if (!$this->settings && $bDebug) {

      $this->launchException('No settings defined');
    }

    return $this->settings;
  }

  protected function get($sPath, $bDebug = true) {

    return $this->getSettings()->get($sPath, $bDebug);
  }

  protected function read($sPath, $bDebug = true) {

    return $this->getSettings()->read($sPath, $bDebug);
  }

  protected function query($sPath, $bDebug = true) {

    return $this->getSettings()->query($sPath, $bDebug);
  }

  protected function set($sPath, $mValue = null) {

    return $this->getSettings()->set($sPath, $mValue);
  }

  protected function dsp() {

    $mArgument = func_get_args();
    if (count($mArgument) == 1) $mArgument = current ($mArgument);

    \Sylma::dsp($mArgument);
  }

  protected function show($mVar, $bToken = true) {

    return \Sylma::show($mVar, $bToken);
  }
}


