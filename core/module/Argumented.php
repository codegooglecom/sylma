<?php

namespace sylma\core\module;
use \sylma\core;

require_once('core/argument/Domed.php');
require_once('Exceptionable.php');
require_once('core/factory.php');

require_once('core/Reflector.php');

abstract class Argumented extends Exceptionable implements core\factory {
  
  /**
   * Class manager
   */
  private $reflector;
  protected static $argumentClass = 'sylma\core\argument\Domed';
  /**
   * Argument object linked to this module, contains various parameters for the module
   */
  protected $arguments = null;
  
  public function create($sName, array $aArguments = array(), $sDirectory = '') {
    
    if (!$this->getArguments()) {
      
      $this->throwException(txt('Cannot build object @class %s. No settings defined', $sName));
    }
    
    if (!$this->reflector) $this->reflector = new core\Reflector($this->getArguments());
    $result = $this->reflector->create($sName, $aArguments, $sDirectory);
    
    return $result;
  }
  
  protected function createArgument(array $aArguments, $sNamespace = '') {
    
    if (!$sNamespace) $sNamespace = $this->getNamespace();
    
    return new static::$argumentClass($aArguments, $sNamespace);
  }
  
  protected function setArguments($mArguments = null, $bMerge = true) {
    
    if ($mArguments !== null) {
      
      if (is_array($mArguments)) {
        
        if ($this->getArguments() && $bMerge) $this->getArguments()->mergeArray($mArguments);
        else $this->arguments = $this->createArgument($mArguments, $this->getNamespace());
      }
      else if (is_object($mArguments)) {
        
        if ($this->getArguments() && $bMerge) $this->getArguments()->merge($mArguments);
        else $this->arguments = $mArguments;
      }
    }
    else {
      
      $this->arguments = null;
    }
    
    return $this->getArguments();
  }
  
  protected function getArguments() {
    
    return $this->arguments;
  }
  protected function getArgument($sPath, $mDefault = null, $bDebug = false) {
    
    $mResult = $mDefault;
    
    if (!$this->getArguments()) $this->throwException(t('No arguments has been defined'));
    
    $mResult = $this->getArguments()->get($sPath, $bDebug);
    if ($mResult === null && $mDefault !== null) $mResult = $mDefault;
    
    return $mResult;
  }
  
  protected function readArgument($sPath, $mDefault = null, $bDebug = false) {
    
    $mResult = $mDefault;
    
    if (!$this->getArguments()) $this->throwException(t('No arguments has been defined'));
    
    $mResult = $this->getArguments()->read($sPath, $bDebug);
    if ($mResult === null && $mDefault !== null) $mResult = $mDefault;
    
    return $mResult;
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


