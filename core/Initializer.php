<?php

class Initializer {
	
	const NS = 'http://www.sylma.org/core/initializer';
	
	public function __construct() {
		
    require_once('functions/Text.php');
    require_once('functions/Arrays.php');
    require_once('functions/Paths.php');
    require_once('module/old/Namespaced.php');
    require_once('settings/SettingsInterface.php');
    require_once('settings/Arguments.php');
    require_once('settings/Spyc.php');
    require_once('settings/XArguments.php');
	}
	
	public function loadSettings($sServer, $sSylma) {
		
    $settings = new XArguments(substr(SYLMA_RELATIVE_PATH, 1) . $sSylma, Sylma::NS);
    if ($sServer) $settings->mergeFile($sServer);
    
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
    
    libxml_use_internal_errors(false);
    
    //set_exception_handler("self::sendException");
    
    $this->loadLibs($sCore);
    
    require_once('module/old/XDB.php');
            
    require_once('modules/logger/LoggerInterface.php');
    require_once('modules/logger/Logger.php');
    
    //ini_set('session.save_path', 'c:/temp/php');
    //ini_set('session.cookie_lifetime', SESSION_MAX_LIFETIME);
    ini_set('session.gc_maxlifetime', Sylma::get('modules/users/session/lifetime'));
    
    session_start();
    
    if (Sylma::get('db/enable')) $this->loadXDB();
	}
  
  protected function loadLibs($sCore) {
    
    set_include_path(get_include_path() . SYLMA_PATH_SEPARATOR . MAIN_DIRECTORY); // SYLMA_PATH_SEPARATOR . SYLMA_PATH .'/' . $sCore . 
    
    require_once('functions/Global.php');
    
    require_once('core/module/old/Base.php');
    require_once('core/module/old/Module.php');
    require_once('core/module/old/Extension.php');
    
    require_once('core/XML_Processor.php');
    
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
    require_once('core/HTML.php');
    
    require_once('settings/Options.php');
    
    require_once('core/Redirect.php');
    require_once('core/Messages.php');
    
    require_once('schemas/XML_Schema.php');
    
    require_once('action/Controler.php');
    require_once('action/Path.php');
    require_once('action/Array.php');
    require_once('action/Action.php');
    
    require_once('modules/xquery/XQuery.php');
    require_once('core/XSL_Document.php');
    
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