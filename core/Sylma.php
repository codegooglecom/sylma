<?php

class Sylma {
  
  const PATH_LIB = 'core';
  const PATH_OPTIONS = '/system/sylma.yml';
  const MODE_EXECUTE = 1;
  const MODE_READ = 1;
  const MODE_WRITE = 1;
  const LOG_STATUT_DEFAULT = 'notice';
  
  private static $settings = null;
  private static $logger = null;
  
  /**
   * Handle final result for @method render()
   */
  private static $result = null;
  
  public static function init($sServer = '') {
    
    require_once('Functions.php');
    require_once('module/Namespaced.php');
    require_once('ArgumentsInterface.php');
    require_once('Arguments.php');
    require_once('Spyc.php');
    require_once('XArguments.php');
    
    $sSylma = SYLMA_PATH . self::PATH_OPTIONS;
    
    self::$settings = new XArguments($sSylma, 'sylma');
    if ($sServer)  self::getSettings()->mergeFile($sServer);
    
    // set error report mode
    if (Sylma::get('debug/enable')) error_reporting(E_ALL);
    else error_reporting(0);
    
    require_once('Controler.php');
    
    libxml_use_internal_errors(true);
    
    require_once('Error.php');
    $sError = set_error_handler("userErrorHandler");
    
    self::loadLibs();
    
    // DB
    if (self::get('db/enable')) require_once('modules/exist/XML_Database.php');
    
    // others
    require_once('modules/logger/LoggerInterface.php');
    require_once('modules/logger/Logger.php');
    require_once('modules/dbx/DBX.php');
    
    //ini_set('session.save_path', 'c:/temp/php');
    //ini_set('session.cookie_lifetime', SESSION_MAX_LIFETIME);
    ini_set('session.gc_maxlifetime', Sylma::get('users/session/lifetime'));
    
    session_start();
    
    self::$result = Controler::trickMe();
    
    //session_write_close();
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
  public static function log($sNamespace, $mMessage, $sStatut = self::LOG_STATUT_DEFAULT) {
    
    if (class_exists('Controler') && Controler::isAdmin() && Controler::useMessages()) {
      
      if (self::get('messages/print/all')) {
        
        echo $sNamespace . ' >> ' . $mMessage;
      }
      
      Controler::addMessage(array($sNamespace, ' >> ', $mMessage), $sStatut); // temp
    }
    else if (self::get('messages/print/hidden')) {
      
      echo $sNamespace . ' >> ' . $mMessage . "<br/>\n";
    }
    
    if (class_exists('Logger')) {
      
      // database is open log into
      
      
    }
    else if (self::get('messages/log/enable', false)) {
      
      // no database instance, use a file
      
      if ($sPath = self::get('messages/log/file', false)) {
        
        $fp = fopen(MAIN_DIRECTORY.$sPath, 'a+');
        fwrite($fp, "----\n" . $mMessage . ' -- ' . $sStatut . "\n"); //.Controler::getBacktrace()
        fclose($fp);
      }
    }
  }
  
  protected static function loadLibs() {
    
    set_include_path(get_include_path() . SYLMA_PATH_SEPARATOR . SYLMA_PATH .'/' . self::PATH_LIB);
    
    require_once('Global.php');
    
    require_once('module/Base.php');
    require_once('module/Module.php');
    require_once('module/Extension.php');
    require_once('module/XDB.php');
    require_once('XML_Processor.php');
    
    require_once('dom/Controler.php');
    require_once('dom/Document.php');
    require_once('dom/XML.php');
    require_once('dom/Element.php');
    require_once('HTML.php');
    
    require_once('core/Options.php');
    
    require_once('Redirect.php');
    require_once('Messages.php');
    
    require_once('schemas/XML_Schema.php');
    
    require_once('action/Controler.php');
    require_once('action/Path.php');
    require_once('action/Array.php');
    require_once('action/Action.php');
    
    require_once('modules/xquery/XQuery.php');
    require_once('XSL_Document.php');
    
    require_once('user/User.php');
    require_once('user/Cookie.php');
    
    require_once('storage/filesys/Resource.php');
    require_once('storage/filesys/Directory.php');
    require_once('storage/filesys/File.php');
    require_once('storage/filesys/SFile.php');
    
    require_once('window/WindowInterface.php');
    require_once('window/Action.php');
    require_once('window/HTML.php');
    require_once('window/XML.php');
    require_once('window/TXT.php');
    require_once('window/Img.php');
  }
  
  public static function render() {
    
    return self::$result;
  }
}