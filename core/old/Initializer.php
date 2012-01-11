<?php

use sylma\core;

class Initializer {
	
  const NS = 'http://www.sylma.org/core/initializer';
  
  private static $sArgument = 'XArguments';
  
  /**
   * 1. Load primary libraries
   */
  public function __construct() {
    
    require_once('core/functions/Text.php');
    require_once('core/functions/Array.php');
    //require_once('functions/Path.php');
    require_once('core/functions/old.php');
    require_once('core/module/old/Namespaced.php');
    require_once('core/settings/SettingsInterface.php');
    require_once('core/settings/Arguments.php');
    require_once('core/settings/Spyc.php');
    require_once('core/settings/XArguments.php');
  }
  
  /**
   * 2. Load global settings
   * 
   * @param type $sServer
   * @param type $sSylma
   * @return XArguments 
   */
  public function loadSettings($sServer, $sSylma) {
		
    $settings = new self::$sArgument(substr(SYLMA_RELATIVE_PATH, 1) . $sSylma, Sylma::NS);
    if ($sServer) $settings->mergeFile($sServer);
    
    return $settings;
  }
  
  /**
   * 3. Load secondary libraries
   * @param type $sCore 
   */
  public function load() {
    
    // set error report mode
    if (Sylma::get('debug/enable')) {
      
      error_reporting(E_ALL);
        
	    if (!ini_get('display_errors')) {
	      
	      Sylma::log(self::NS, txt('php.ini : display_errors is Off. Fatal error will not be shown.'));
	    }
    }
    else {
      
      error_reporting(0);
    }
    
    require_once('core/old/Controler.php');
    
    libxml_use_internal_errors(false);
    
    //set_exception_handler("self::sendException");
    
    core\exception\Basic::throwError(false);
    $this->loadLibs();
    core\exception\Basic::throwError(true);
    
    require_once('core/module/old/XDB.php');
    
    require_once('modules/logger/LoggerInterface.php');
    require_once('modules/logger/Logger.php');
    
    \Sylma::getControler('formater');
    
    //ini_set('session.save_path', 'c:/temp/php');
    //ini_set('session.cookie_lifetime', SESSION_MAX_LIFETIME);
    ini_set('session.gc_maxlifetime', Sylma::get('modules/users/session/lifetime'));
    
    session_start();
    
    if (Sylma::get('db/enable')) $this->loadXDB();
  }
  
  /**
   * Old load, will be progressively erased
   */
  protected function loadLibs() {
    
    require_once('core/functions/Global.php');
    
    require_once('core/module/old/Base.php');
    require_once('core/module/old/Module.php');
    require_once('core/module/old/Extension.php');
    
    require_once('core/old/XML_Processor.php');
    
    require_once('dom/Controler.php');
    require_once('dom/NodeInterface.php');
    require_once('dom/XML.php');
    require_once('dom/Attribute.php');
    require_once('dom/CData.php');
    require_once('dom/Fragment.php');
    require_once('dom/Comment.php');
    require_once('dom/Text.php');
    require_once('dom/Nodelist.php');
    require_once('dom/ElementInterface.php');
    
    require_once('dom/Document.php');
    require_once('dom/Element.php');
    require_once('core/old/HTML.php');
    
    require_once('core/settings/Options.php');
    
    require_once('core/old/Redirect.php');
    require_once('core/old/Messages.php');
    
    require_once('schemas/XML_Schema.php');
    
    require_once('action/Controler.php');
    require_once('action/Path.php');
    require_once('action/Array.php');
    require_once('action/Action.php');
    
    require_once('modules/xquery/XQuery.php');
    require_once('core/old/XSL_Document.php');
    
    require_once('user/User.php');
    require_once('user/Cookie.php');
    
    require_once('storage/filesys/Resource.php');
    require_once('storage/filesys/Directory.php');
    require_once('storage/filesys/File.php');
    require_once('storage/filesys/SFile.php');
    
    require_once('window/WindowInterface.php');
    require_once('window/Redirection.php');
    require_once('window/Action.php');
    require_once('window/HTML.php');
    require_once('window/XML.php');
    require_once('window/TXT.php');
    require_once('window/Img.php');
  }
  
  public function loadXDB() {
  	
    require_once('modules/exist/XML_Database.php');
    require_once('modules/dbx/DBX.php');
  }
  
}