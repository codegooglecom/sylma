<?php

class Sylma {
  
  const PATH_LIB = 'core';
  const PATH_OPTIONS = '/system/sylma.yml';
  const MODE_EXECUTE = 1;
  const MODE_WRITE = 2;
  const MODE_READ = 4;
  const LOG_STATUT_DEFAULT = 'notice';
  
  private static $settings = null;
  private static $logger = null;
  protected static $aControlers;
  
  public static $exception = 'SylmaException';
  
  /**
   * Handle final result for @method render()
   */
  private static $result = null;
  
  public static function init($sServer = '') {
    
  	require_once('Initializer.php');
  	
  	$init = self::$aControlers['init'] = new Initializer();
  	
  	self::$settings = $init->loadSettings($sServer, self::PATH_OPTIONS);
    $init->load(self::PATH_LIB);
    
    try {
      
      self::$result = Controler::trickMe();
    }
    catch (SylmaExceptionInterface $e) {
      
      if (self::get('debug/enable')) echo $e;
    }
    
    //session_write_close();
  }
  
  public static function setControler($sName, $controler) {
    
    self::$aControlers[$sName] = $controler;
  }
  
  public static function getControler($sName) {
    
    return array_val($sName, self::$aControlers);
  }
  
  protected static function getSettings($sPath = '') {
    
    if ($sPath) return self::getSettings()->get($sPath);
    else return self::$settings;
  }
  
  protected static function getLogger() {
    
    return $this->logger;
  }
  
  protected static function setLogger(LoggerInterface $logger) {
    
    $this->logger = $logger;
  }
  
  public static function get($sPath, $bDebug = true) {
    
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
    
    if (class_exists('Controler') && Controler::isAdmin() && Controler::useMessages()) {
      
      if (self::get('messages/print/all')) echo $sMessage;
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
  
  public static function throwException($sMessage, array $aPath = array(), $iOffset = 1) {
    
    $e = new Sylma::$exception($sMessage);
    
    $e->setPath($aPath);
    $e->loadException($iOffset);
    
    throw $e;
  }
  
  public static function sendError($iNo, $sMessage, $sFile, $iLine) {
  	
    $sylmaException = new self::$exception($sMessage);
    $sylmaException->loadError($iNo, $sMessage, $sFile, $iLine);

    //throw $sylmaException;
  }

  public static function render() {
    
    return self::$result;
  }
}