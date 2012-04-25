<?php

namespace sylma\core;

require_once('module/Filed.php');

class Initializer extends module\Filed {

  const NS = 'http://www.sylma.org/core/initializer';

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

    $iStartTime = microtime(true);

    $user = \Sylma::getControler('user');
    $user->load();

    // Check for maintenance mode
    if ($sMaintenance = $this->loadMaintenance()) return $sMaintenance;

    $fs = \Sylma::getControler('fs');
    $fs->getDirectory()->getSettings()->loadDocument();

    $this->setDirectory($fs->getDirectory());

    $path = $this->create('path', array($this->loadGET()));

    // The extension specify the window type

    $sExtension = $path->parseExtension(true);

    // Load Redirect session var, if present means it has been redirected - $_SESSION['redirect'], $_POST in 'document'
    //$this->loadRedirect();


    // Parse of the request_uri, creation of the window - $_GET
    $window = $this->loadWindow($sExtension);

    // Reload last alternatives mime-type results - $_SESSION['results']
    //self::loadResults();

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

  protected function loadMaintenance() {

    $sResult = '';

    if ($this->getArgument('maintenance/enable')) $sResult = 'site en maintenance';

    return $sResult;
  }

  protected function setErrorReporting() {

    if (\Sylma::read('debug/enable')) {

      error_reporting(E_ALL);

	    if (!ini_get('display_errors')) {

	      \Sylma::log(self::NS, txt('php.ini : display_errors is Off. Fatal error will not be shown.'));
	    }
    }
    else {

      error_reporting(0);
    }

    libxml_use_internal_errors(false);
  }

  protected function loadWindow($sExtension) {

    $settings = $this->getArgument('window/' . strtolower($sExtension), false);

    if (!$settings) {

      $settings = $this->getArgument('window/html');
    }

    $sPath = $settings->read('path');

    // Creation of the window
    return $this->create('action', array($this->getFile($sPath)));
  }

  protected function loadGET() {

    $sResult = '';

    if (array_key_exists('q', $_GET) && $_GET['q']) {

      $sResult = $_GET['q'];
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