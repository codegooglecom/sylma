<?php

namespace sylma\core;
use sylma\parser, sylma\core, sylma\storage\fs;

require_once('module/Filed.php');

class Initializer extends module\Filed {

  const NS = 'http://www.sylma.org/core/initializer';
  const EXTENSION_DEFAULT = 'html';

  /**
   * 2. Load global settings
   *
   * @param type $sServer
   * @param type $sSylma
   * @return XArguments
   */
  public function loadSettings($sServer, $sSylma) {

    //$settings = $this->createArgument($sSylma, \Sylma::NS);
    require_once(self::$sArgumentFile);
    $settings = new self::$sArgumentClass($sSylma, \Sylma::NS);

    if ($sServer) $settings->mergeFile($sServer);

    return $settings;
  }

  public function run($settings) {

    $this->setArguments($settings);
    $this->setErrorReporting();

    //set_exception_handler("self::sendException");
    ini_set('session.gc_maxlifetime', \Sylma::read('session/lifetime'));

    session_start();

    // if (\Sylma::read('db/enable')) $this->loadXDB();

    //$iStartTime = microtime(true);

    $user = \Sylma::getControler('user');
    $user->load();

    // Check for maintenance mode
    if ($sMaintenance = $this->loadMaintenance()) return $sMaintenance;

    $fs = \Sylma::getControler('fs');

    $this->setDirectory($fs->getDirectory());
    $this->getDirectory()->getSettings()->loadDocument();

    $path = $this->create('path', array($this->loadGET(), null, array(), false));

    // The extension specify the window type

    // Load Redirect session var, if present means it has been redirected - $_SESSION['redirect'], $_POST in 'document'
    //$this->loadRedirect();


    // Parse of the request_uri, creation of the window - $_GET


    // Reload last alternatives mime-type results - $_SESSION['results']
    //self::loadResults();

    if ($file = $this->getFile((string) $path, false)) {

      // A file
      $sResult = $this->loadFile($file);
    }
    else if (!$path->getExtension()) {

      $sExtension = $path->parseExtension(true);

      $window = $this->loadWindow($sExtension);
      $sResult = $this->loadAction($path, $window);
    }
    else {

      $this->throwException('No valid window defined');
    }

    return $sResult;
  }

  protected function loadAction(parser\action\path $path, parser\action $window) {

    $path->parsePath();

    $action = $this->create('action', array($path->getFile(), $path->getArguments()->asArray()));
    //echo get_class($window); exit;

    $sPath = (string) $path;
    $sPath = strlen($sPath) > 1 ? substr($sPath, 1) : $sPath;

    $window->setArgument('content', $action);
    $window->setArgument('current', $sPath);

    //if ($action->doRedirect()) self::doHTTPRedirect($oResult);

    return $window->asString();
  }

  protected function loadFile(fs\file $file) {

    switch ($file->getExtension()) {

      case 'php' :

        $this->throwException('Cannot read php files');

      case 'jpg' :
      case 'jpeg' :
      case 'png' :
      case 'gif' :
      default :

        $window = $this->create('window', array($this, $file));

      break;
    }

    return $window->asString();
  }

  protected function getMime($sExtension) {

    switch (strtolower($sExtension)) {

      case 'jpg' : $sExtension = 'jpeg';
      case 'jpeg' :
      case 'png' :
      case 'gif' : return 'image/'.$sExtension;

      case 'js' : return 'application/javascript';
      case 'css' : return 'text/css';
      case 'xml' :
      case 'xsl' : return 'text/xml';

      case 'html' : return 'text/html';
      case 'xhtml' : return 'application/xhtml+xml';

      default : return 'plain/text';
    }
  }

  public function setContentType($sExtension) {

    header('Content-type: '.$this->getMime($sExtension));
  }

  protected function loadMaintenance() {

    $sResult = '';

    if ($this->getArgument('maintenance/enable')) $sResult = 'site en maintenance';

    return $sResult;
  }

  protected function setErrorReporting() {

    if (\Sylma::read('debug/enable')) {

      error_reporting(E_ALL);

	    if (!ini_get('display_errors')) {

	      \Sylma::log(self::NS, sprintf('php.ini : display_errors is Off. Fatal error will not be shown.'));
	    }
    }
    else {

      error_reporting(0);
    }

    libxml_use_internal_errors(false);
  }

  protected function loadWindow($sExtension) {

    $sExtension = strtolower($sExtension);
    if (!$sExtension) $sExtension = self::EXTENSION_DEFAULT;

    $settings = $this->getArgument('window/' . $sExtension, false);

    if (!$settings) {

      $this->throwException(sprintf('No window associated with extension "%s"', $sExtension));
    }

    $sAlias = $sExtension;
    $sPath = $settings->read('action');

    $window = $this->create($sAlias, array($this->getFile($sPath)));

    // Creation of the window
    return $window;
  }

  protected function loadGET() {

    $sResult = '';

    if (array_key_exists('q', $_GET) && $_GET['q']) {

      $sResult = '/' . $_GET['q'];
      //unset($aGET['q']);

    } else $sResult = '/';

    return $sResult;
  }

  protected function loadPOST() {


  }

  protected function loadRedirect() {

    $redirect = $this->create('redirect');

    // Une redirection a été effectuée

    if (array_key_exists('redirect', $_SESSION)) {

      $session = unserialize($_SESSION['redirect']);
      unset($_SESSION['redirect']);

      // Récupération des messages du Redirect et suppression

      if ($session instanceof self::$sRedirect) {

        // get messages
        $redirect = $session;
      }
      else {

        \Sylma::log(t('Session Redirect perdu !'), 'warning');
      }

    } else {

      $redirect->setArgument('post', $this->loadPost());
    }

    self::$oRedirect = $oRedirect;

    return $oRedirect;
  }

  protected function loadResults() {

    if (!array_key_exists('results', $_SESSION)) $_SESSION['results'] = array();
    self::$aResults = $_SESSION['results'];
  }

}