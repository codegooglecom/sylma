<?php

require_once('Namespaced.php');

class ModuleManager extends Namespaced {

  const DIRECTORY_TOKEN = '@sylma-directory';
  const CLASSBASE_TOKEN = '@sylma-classbase';
  
  /**
   * Classes to use within @method create() loaded in @settings /classes
   */
  private $aClasses = array();
  
  /**
   * Argument object linked to this module, contains various parameters for module
   */
  protected $arguments = null;
  
  /**
   * Build an object defined in @settings classes
   * 
   * @param string $sName The short name of the class
   * @param array $aArguments The arguments sent to the object on contstruction
   *
   * @return mixed The object builded
   */
  public function create($sName, array $aArguments = array()) {
    
    $result = null;
    
    if (array_key_exists($sName, $this->aClasses)) {
      
      $class = $this->aClasses[$sName];
    }
    else {
      
      if (!$this->getArguments()) {
        
        $this->throwException(txt('Cannot build object @class %s. No settings defined', $sName));
      }
      
      $class = $this->loadClass($sName, $this->getArguments());
      
      if ($sClassBase = $this->getArguments()->getToken(self::CLASSBASE_TOKEN)) {
        
        $class->set('name', path_absolute($class->read('name'), $sClassBase, '\\'));
      }
      
      $sInlineDirectory = $this->getArguments()->getLastDirectory();
      $sDirectory = $sInlineDirectory ? $sInlineDirectory : (string) $this->getDirectory();
      
      if ($sFile = $class->read('file', false)) {
        
        $class->set('file', path_absolute($sFile, $sDirectory));
      }
      
      $this->aClasses[$sName] = $class;
    }
    
    return Controler::createObject($class, $aArguments);
  }
  
  protected function loadClass($sName, SettingsInterface $args) {
    
    $aPath = explode('/', $sName);
    array_unshift($aPath, null);
    
    $sPath = implode('/classes/', $aPath);
    
    $args->registerToken(self::DIRECTORY_TOKEN);
    $args->registerToken(self::CLASSBASE_TOKEN);
    
    if (!$class = $args->get($sPath)) {
      
      $this->throwException(txt('Cannot build object @class %s. No settings defined for these class', $sName));
    }
    
    $args->unRegisterToken(self::DIRECTORY_TOKEN);
    $args->unRegisterToken(self::CLASSBASE_TOKEN);
    
    return $class;
  }
  
  protected function setArguments($mArguments = null, $bMerge = true) {
    
    if ($mArguments !== null) {
      
      if (is_array($mArguments)) {
        
        if ($this->getArguments() && $bMerge) $this->getArguments()->mergeArray($mArguments);
        else $this->arguments = new XArguments($mArguments, $this->getNamespace());
      }
      else if (is_string($mArguments)) {
        
        $this->arguments = new XArguments((string) $this->getFile($mArguments));
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
    
    $mSender = (array) $mSender;
    $mSender[] = '@namespace ' . $this->getNamespace();
    
    Sylma::throwException($sMessage, $mSender, $iOffset);
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
  protected function log($mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {
    
    return Sylma::log($this->getNamespace(), $mMessage, $sStatut);
  }
}


