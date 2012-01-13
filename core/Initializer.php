<?php

class Initializer {
	
  const NS = 'http://www.sylma.org/core/initializer';
  
  private static $sArgument = 'XArguments';
  
  private static $sRedirect = 'core\Redirect';
  private static $sRedirectFile = 'core\Redirect.php';
  
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
    if (Sylma::read('debug/enable')) {
      
      error_reporting(E_ALL);
        
	    if (!ini_get('display_errors')) {
	      
	      Sylma::log(self::NS, txt('php.ini : display_errors is Off. Fatal error will not be shown.'));
	    }
    }
    else {
      
      error_reporting(0);
    }
    
    libxml_use_internal_errors(false);
    
    //set_exception_handler("self::sendException");
    
    ini_set('session.gc_maxlifetime', Sylma::read('modules/users/session/lifetime'));
    
    session_start();
    
    // if (Sylma::read('db/enable')) $this->loadXDB();
    
    $iStartTime = microtime(true);
    $sSystemPath = $_SERVER['DOCUMENT_ROOT'];
    
    $user = \Sylma::getControler('user');
    $user->load();
    
    // Check for maintenance mode
    if ($aMaintenance = self::loadMaintenance()) return $aMaintenance[0];
    
    \Sylma::getControler('fs');
    
    if ($user->needProfile()) $user->loadProfile();
    
    // init xml database
    //if (\Sylma::read('db/enable')) self::setDatabase(new XML_Database());
    
    $path = $this->create('path');
    $path->loadGET();
    
    // The extension specify the window type
    
    if (!$sExtension = $path->parseExtension(true)) $sExtension = $this->readArgument('window/default');
    
    $sExtension = strtolower($sExtension);
    
    $settings = $this->getArgument('settings/' . $sExtension, false);
    if (!$settings) $settings = $this->getArgument('settings/default');
    
    // Creation of the window
    
    $factory = \Sylma::getControler('factory');
    $window = $factory->createObject($settings);
    
    $result = $this->loadResults();
    
    if ($file = $this->loadFile($path, $sExtension)) {
      
      // A file
      $window->loadAction($file);
    }
    else if (in_array($path->getExtension(), $aExecutableExtensions)) {
      
      // An action
      $path->parsePath();
      
      if ($result) {
        
        // Pre-recorded result
        $window->loadAction($result); // TODO : make XML_Action
      }
      else {
        
        // Get then send the action
        $action = $this->create('action', array($path));
        $action->run();
        
        if ($action->doRedirect()) self::doHTTPRedirect($oResult);
        
        if ($settings->read('action')) {
          
          $window->setArgument('window-action', $action);
          
        } else {
          
          $window->loadAction($action);
        }
      }
    }
    
    return $window;
  }
  
  protected function loadFile($path, $sExtension) {
    
    if (!$sExtension) return null;
    
    $file = self::getFile($path . '.' . $sExtension);
    
    return $file && $oFile->checkRights(\Sylma::MODE_READ);
  }
  
  protected function loadXDB() {
  	
    require_once('modules/exist/XML_Database.php');
  }
  
  protected function loadRedirect() {
    
    require_once(self::$sRedirectFile);
    $redirect = new self::$sRedirect;
    
    // Une redirection a été effectuée
    
    if (array_key_exists('redirect', $_SESSION)) {
      
      $session = unserialize($_SESSION['redirect']);
      unset($_SESSION['redirect']);
      
      // Récupération des messages du Redirect et suppression
      
      if ($session instanceof 'core\Redirect') {
        
        // get messages
        $redirect = $session;
      }
      else {
        
        self::addMessage(t('Session Redirect perdu !'), 'warning');
      }
      
    } else {
      
      if ($_POST) {
        
        //$oValues = new XML_Document(new XML_Element('post', null, null, SYLMA_NS_XHTML));
        $oValues = new XML_Document('post');
        
        self::loadPost($_POST, $oValues->getRoot());
        
        $oRedirect->setDocument('post', $oValues);
      }
    }
    
    self::$oRedirect = $oRedirect;
    
    return $oRedirect;
  }
  
  protected function loadResults() {
    
    if (!array_key_exists('results', $_SESSION)) $_SESSION['results'] = array();
    self::$aResults = $_SESSION['results'];
  }

}