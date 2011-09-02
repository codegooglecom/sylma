<?php

namespace sylma\core\module;
use \sylma\core;

require_once('core/argument/Basic.php');
require_once('core/Reflector.php');
require_once('Namespaced.php');

abstract class Argumented extends Namespaced {
  
  const NS = 'http://www.sylma.org/core/module/Argumented';
  
  /**
   * Class manager
   */
  private $reflector;
  protected static $argumentClass = 'sylma\core\argument\Basic';
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
  
  public function createArgument($aArguments) {
    
    return new self::$argumentClass($aArguments, $this->getNamespace());
  }
  
  protected function setArguments($mArguments = null, $bMerge = true) {
    
    if ($mArguments !== null) {
      
      if (is_array($mArguments)) {
        
        if ($this->getArguments() && $bMerge) $this->getArguments()->mergeArray($mArguments);
        else $this->arguments = new core\argument\Basic($mArguments, $this->getNamespace());
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
    if ($mResult === null && $mDefault !== 'null') $mResult = $mDefault;
    
    return $mResult;
  }
  
  /**
   * Throw a customized exception to the main controler
   * 
   * @param string $sMessage The message describing the exception
   * @param array|string $mSender A list of keys or a single key describing the previous classes throwing this exception
   * @param integer $iOffset The number of calls before final sent to main controler. This will be used to localize the call in backtrace
   */
  protected function throwException($sMessage, $mSender = array(), $iOffset = 2) {
    
    $sNamespace = $this->getNamespace() ? $this->getNamespace() : self::NS;
    
    $mSender = (array) $mSender;
    $mSender[] = '@namespace ' . $sNamespace;
    
    \Sylma::throwException($sMessage, $mSender, $iOffset);
  }
  
  /**
   * Escape a string for secured queries to module's related storage system
   * <code>
   * list($spUser, $spPassword) = $this->escape(array($sUser, sha1($sPassword)));
   * </code>
   * 
   * @param string|array A single or a list of values to escape
   * @return string|array An escaped string or array of strings
   */
  protected function escape() {
    
    if (func_num_args() == 1) return addQuote(func_get_arg(0));
    else return addQuote(func_get_args());
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


