<?php

class Initializer {
	
	const NS = 'http://www.sylma.org/core/initializer';
	
	public function __construct() {
		
    require_once('functions/Text.php');
    require_once('functions/Arrays.php');
    require_once('module/Namespaced.php');
    require_once('ArgumentsInterface.php');
    require_once('Arguments.php');
    require_once('Spyc.php');
    require_once('XArguments.php');
	}
	
	public function loadSettings($sServer, $sSylma) {
		
    $sSylma = SYLMA_PATH . $sSylma;
    
    $settings = new XArguments($sSylma, 'sylma');
    if ($sServer)  $settings->mergeFile($sServer);
    
    return $settings;
	}
	
	public function load($sCore) {
		
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
    
    require_once('Controler.php');
    
    libxml_use_internal_errors(true);
    
    require_once('SylmaException.php');
    require_once('Error.php');
    set_error_handler("Sylma::sendError");
    //set_error_handler("sylmaErrorHandler");
    //set_exception_handler("self::sendException");
    
    $this->loadLibs($sCore);
    
    require_once('module/XDB.php');
            
    require_once('modules/logger/LoggerInterface.php');
    require_once('modules/logger/Logger.php');
    
    //ini_set('session.save_path', 'c:/temp/php');
    //ini_set('session.cookie_lifetime', SESSION_MAX_LIFETIME);
    ini_set('session.gc_maxlifetime', Sylma::get('users/session/lifetime'));
    
    session_start();
    
    if (Sylma::get('db/enable')) $this->loadXDB();
	}
  
  protected function loadLibs() {
    
    set_include_path(get_include_path() . SYLMA_PATH_SEPARATOR . SYLMA_PATH .'/' . $sPathLib);
    
    require_once('functions/Global.php');
    
    require_once('module/Base.php');
    require_once('module/Module.php');
    require_once('module/Extension.php');
    
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