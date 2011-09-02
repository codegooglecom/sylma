<?php

namespace sylma\core;

class Reflector {
  
  /**
   * Used in @class argument for keeping file path for relative path import
   */
  const DIRECTORY_TOKEN = '@sylma-directory';
  
  /**
   * Used in @class argument for keeping trace of last defined class namespace
   */
  const CLASSBASE_TOKEN = '@sylma-classbase';
  
  /**
   * Classes to use within @method create() loaded in @settings /classes
   */
  private $aClasses = array();
  private $settings;
  
  public function __construct(argument $settings) {
    
    $this->settings = $settings;
  }
  
  protected function getSettings() {
    
    return $this->settings;
  }
  
  /**
   * Build an object defined in @settings classes
   * 
   * @param string $sName The short name of the class
   * @param array $aArguments The arguments sent to the object on contstruction
   *
   * @return mixed The object builded
   */
  public function create($sName, array $aArguments = array(), $sDirectory = '') {
    
    $result = null;
    
    if (array_key_exists($sName, $this->aClasses)) {
      
      $class = $this->aClasses[$sName];
    }
    else {
      
      if (!$this->getSettings()) {
        
        $this->throwException(txt('Cannot build object @class %s. No settings defined', $sName));
      }
      
      // set class name
      $class = $this->loadClass($sName, $this->getSettings());
      
      if ($sClassBase = $this->getSettings()->getToken(self::CLASSBASE_TOKEN)) {
        
        $class->set('name', path_absolute($class->read('name'), $sClassBase, '\\'));
      }
      
      // set file name
      if ($sInlineDirectory = $this->getSettings()->getLastDirectory()) $sDirectory = $sInlineDirectory;
      
      if ($sFile = $class->read('file', false)) {
        
        $class->set('file', path_absolute($sFile, $sDirectory));
      }
      
      $this->aClasses[$sName] = $class;
    }
    
    return $this->createObject($class, $aArguments);
  }
  
  protected function loadClass($sName, argument $args) {
    
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
  
  /**
   * Create an object from a module array using @method buildClass()
   * 
   * @param argument $class The argument object containing the classes infos
   *  Ex : array(classes' => array('keyname' => array('name' => 'classname', 'file' => 'filename')))
   * @param* array $aArguments The list of arguments to send to __construct
   * 
   * @return mixed The object created
   */
  public function createObject(argument $class, array $aArguments = array()) {
    
    $result = null;
    
    if (!$sClass = $class->get('name')) { // has name ?
      
      \Sylma::throwException(txt('Cannot build object. No "name" defined in class'));
    }
    
    if (self::includeClass($sClass, $class->get('file', false))) {
      
      $result = $this->buildClass($sClass, $aArguments);
    }
    
    return $result;
  }
  
  /**
   * Build an object from the class name with Reflection
   * @param string $sClass the class name
   * @param string $sFile the file where the class is declared to include
   * @param array $aArgument the arguments to use at the __construct call
   * @return null|object the created object
   */
  public static function includeClass($sClass, $sFile = '') {
    
    $sMain = \Sylma::ROOT;
    
    if (!class_exists($sClass)) {
      
      if (!$sFile) {
        
        $sFile = str_replace('\\', '/', $sClass . '.php');
      }
      
      // include the file
      
      $sFile = $sMain . $sFile;
      
      if (file_exists($sFile)) require_once($sFile);
      else {
        
        \Sylma::throwException(txt('Cannot build object of @class %s. @file %s not found !', $sClass, $sFile));
      }
    }
    
    if (!class_exists($sClass)) {
      
      \Sylma::throwException(txt('Cannot build object. @class %s doesn\'t exists !', $sClass));
    }
    
    return true;
  }
  
  public function buildClass($sClass, array $aArguments = array()) {
    
    $result = null;
    
    // creation of object
    
    // caching classes improve performances
    if (array_key_exists($sClass, $this->aClasses)) $reflected = $this->aClasses[$sClass];
    else $reflected = $this->aClasses[$sClass] = new \ReflectionClass($sClass);
    
    if ($aArguments) $result = $reflected->newInstanceArgs($aArguments);
    else $result = $reflected->newInstance();
    
    // These 2 following functions doesn't work, keep here for futur brainstorming
    //
    // $result = new $sClass(list($aArguments));
    // $result = call_user_func_array(array($sClass, '__construct'), $aArguments);
    
    return $result;
  }
  
  public function throwException($sMessage) {
    
    \Sylma::throwException($sMessage);
  }
}