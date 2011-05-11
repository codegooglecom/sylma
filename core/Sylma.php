<?php

class Sylma {
  
  const PATH_LIB = 'core';
  private static $oSettings = null;
  
  public static function init(array $aSettings) {
    
    global $sylma;
    
    require_once(self::PATH_LIB . '/Arguments.php');
    self::$oSettings = new Arguments($sylma, 'sylma');
    self::getSettings()->merge($aSettings);
    
    if (Sylma::get('debug/enable')) error_reporting(E_ALL);
    else error_reporting(0);
    
    libxml_use_internal_errors(true);
    
    require_once('Error.php');
    $sError = set_error_handler("userErrorHandler");
    
    self::loadLibs();
    
    // DB
    // if (self::get('database/enable')) require_once('modules/exist/XML_Database.php');
    require_once('modules/exist/XML_Database.php');
    
    // others
    require_once('modules/dbx/DBX.php');
    
    //ini_set('session.save_path', 'c:/temp/php');
    //ini_set('session.cookie_lifetime', SESSION_MAX_LIFETIME);
    ini_set('session.gc_maxlifetime', Sylma::get('users/session/lifetime'));
    
    session_start();
    
    return Controler::trickMe();
    
    //session_write_close();
  }
  
  public static function getSettings($sPath = '') {
    
    if ($sPath) return self::getSettings()->get($sPath);
    else return self::$oSettings;
  }
  
  public static function get($sPath) {
    
    return self::getSettings()->get($sPath);
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
    
    require_once('dom/Options.php');
    
    require_once('Controler.php');
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
    
    require_once('Window.php');
  }
  
}