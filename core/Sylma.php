<?php

use \sylma\core, \sylma\modules, \sylma\dom, \sylma\storage;

class Sylma {
  
  const NS = 'http://www.sylma.org';
  const ROOT = SYLMA_ROOT;
  const PATH_LIB = 'core';
  const PATH_OPTIONS = '/system/sylma.yml';
  const MODE_EXECUTE = 1;
  const MODE_WRITE = 2;
  const MODE_READ = 4;
  const LOG_STATUT_DEFAULT = 'notice';
  
  /**
   * @var SettingsInterface
   */
  private static $settings = null;
  private static $logger = null;
  protected static $aControlers;
  
  public static $exception = '\sylma\core\exception\Basic';
  
  /**
   * Handle final result for @method render()
   * @var mixed
   */
  private static $result = null;
  
  public static function init($sServer = '') {
    
    require_once('core/exception/Basic.php');
    //xdebug_disable();
    set_error_handler(self::$exception . "::loadError");
    
    require_once('Initializer.php');
    
    $init = self::$aControlers['init'] = new Initializer();
    
    self::$settings = $init->loadSettings($sServer, self::PATH_OPTIONS);
    $init->load(self::PATH_LIB);
    
    try {
      
      self::$result = Controler::trickMe();
    }
    catch (core\exception $e) {
      
      //if (self::get('debug/enable')) echo $e;
    }
    
    //session_write_close();
  }
  
  public static function setControler($sName, $controler) {
    
    self::$aControlers[$sName] = $controler;
    return $controler;
  }
  
  public static function getControler($sName, $bLoad = true, $bDebug = true) {
    
    $controler = array_val($sName, self::$aControlers);
    if ($bLoad && !$controler) $controler = self::loadControler($sName);
    
    if ($bLoad && $bDebug && !$controler) self::throwException(txt('Controler %s is not defined', $sName));
    
    return $controler;
  }
  
  protected static function loadControler($sName) {
    
    $controler = null;
    
    switch ($sName) {
      
      case 'fs' :
        
        require_once('storage/fs/Controler.php');
        $controler = new storage\fs\Controler;
        
      break;
      
      case 'dom' :
        
        require_once('dom2/Controler.php');
        $controler = new dom\Controler;
        
      break;
      
      case 'user' :
        
        $controler = \Controler::getUser();
      
      break;
      
      case 'formater' :
        
        require_once('modules/formater/Controler.php');
        $controler = new modules\formater\Controler;
      
      break;
      
      case 'factory' :
        
        require_once('core/Reflector.php');
        $controler = new core\Reflector;
        
      break;
    }
    
    if ($controler) self::setControler($sName, $controler);
    
    return $controler;
  }
  
  protected static function getSettings($sPath = '') {
    
    if ($sPath) return self::getSettings()->get($sPath);
    else return self::$settings;
  }
  
  public static function get($sPath = '', $bDebug = true) {
    
    if (self::getSettings()) return self::getSettings()->get($sPath, $bDebug);
    else return $bDebug;
  }
  
  /**
   * Log system messages either in database or in a file defined in @settings /messages/log/file if db is not yet ready
   * Arguments can be see as questions : Who, What, Where
   */
  public static function log($mPath, $mMessage, $sStatut = self::LOG_STATUT_DEFAULT) {
    
    $aPath = (array) $mPath;
    $aPath[] = '@time ' . date('Y-m-d H:m:s');
    
    $sPath = implode(' ', array_reverse($aPath));
    
    $aMessage = array($sPath, ' @message ', $mMessage);
    $sMessage = implode('', $aMessage);
    //print_r(debug_backtrace());
    if (class_exists('Controler') && Controler::isAdmin() && Controler::useMessages()) {
      
      if (self::get('messages/print/visible')) echo $sMessage."<br/>\n";
      Controler::addMessage($aMessage, $sStatut); // temp
    }
    else if (self::get('messages/print/hidden')) {
      
      echo $sMessage . "<br/>\n";
    }
    
    if (class_exists('Logger')) {
      
      // database is open log into
      
      
    }
    else if (self::get('messages/log/enable', false)) {
      
      // no database instance, use a file
      
      if ($sFile = self::get('messages/log/file', false)) {
        
        $fp = fopen(MAIN_DIRECTORY.$sFile, 'a+');
        fwrite($fp, "----\n" . $sMessage . ' -- ' . $sStatut . "\n"); //.Controler::getBacktrace()
        fclose($fp);
      }
    }
  }
  
  public static function loadException(Exception $e) {
    
    $newException = new Sylma::$exception;
    $newException->loadException($e);
    
    return $newException;
  }
  
  public static function throwException($sMessage, array $aPath = array(), $iOffset = 1) {
    
    $e = new Sylma::$exception($sMessage);
    
    $e->setPath($aPath);
    $e->load($iOffset);
    
    throw $e;
  }
  
  public static function isWindows() {
    
    return PHP_OS == 'WINNT';
  }
  
  public static function render() {
    
    return self::$result;
  }
}