<?php

namespace sylma\core;
use sylma\core, sylma\storage\fs;

class Initializer extends module\Domed {

  const NS = 'http://www.sylma.org/core/initializer';
  const AUTOLOAD_MANAGER = 'autoload';

  protected $iStartTime = 0;
  protected static $aStats = array();

  protected static $sArgumentClass = 'sylma\core\argument\Readable';

  /**
   * 2. Load global settings
   *
   * @param type $sServer
   * @param type $sSylma
   * @return XArguments
   */
  public function loadSettings($sServer, $sSylma) {

    $result = new \sylma\core\argument\Filed($sSylma, array(\Sylma::NS));

    if ($sServer) {

      $server = new \sylma\core\argument\Filed($sServer, array(\Sylma::NS));

      foreach ($server->query('imports', false) as $sFile) {

        $result->mergeFile($sFile);
      }

      $result->merge($server);
    }

    return $result;
  }


  public function createArgument($mArguments, $sNamespace = '') {

    return parent::createArgument($mArguments, $sNamespace);
  }

  protected function getStartTime() {

    return $this->iStartTime;
  }

  protected function setStartTime($iTime) {

    $this->iStartTime = $iTime;
  }

  public function getElapsedTime() {

    return microtime(true)- $this->getStartTime();
  }

  public function run($settings) {
//print_r($settings->query('imports', false));

    \Sylma::getManager(self::AUTOLOAD_MANAGER)->loadNamespaces($settings->query('autoload'));

    $this->setArguments($this->createArgument($settings->query()));
    $this->setSettings($this->getArguments());

    //$this->setArguments($settings);
    $this->setErrorReporting();

    //set_exception_handler("self::sendException");
    $iLifetime = $this->readArgument('session/lifetime');
    ini_set('session.gc_maxlifetime', $iLifetime);
    session_set_cookie_params($iLifetime);

    session_cache_expire($this->readArgument('session/cache'));

    $this->startSession();

    // if (\Sylma::read('db/enable')) $this->loadXDB();

    $this->setStartTime(microtime(true));

    require_once('storage/fs/Controler.php');

    // load directory without security
    $fs = new fs\Controler(\Sylma::ROOT, false, false, false);
    $fs->loadDirectory();
    \Sylma::setManager('fs', $fs);
    \Sylma::setManager('fs/free', $fs);

    // load user
    $user = \Sylma::getManager('user');
    $user->load();

    if (\Sylma::isAdmin()) {

      require_once('debug/Functions.php');
    }

    // load directory with security
    $fs = new fs\Controler(\Sylma::ROOT, false, true, true);
    $fs->loadDirectory();
    \Sylma::setManager('fs', $fs);

    $this->setDirectory($fs->getDirectory());

    // Check for maintenance mode
    if ($sMaintenance = $this->loadMaintenance()) {

      return $sMaintenance;
    }
    //$this->getDirectory()->getSettings()->loadDocument();

    $path = $this->loadPath();
    \Sylma::setManager('path', $path);

    // The extension specify the window type

    // Parse of the request_uri, creation of the window - $_GET


    // Reload last alternatives mime-type results - $_SESSION['results']
    //self::loadResults();

    if ($sFile = $path->asFile()) {

      $sResult = $this->createWindowBuilder()->createWindow($sFile, $this->get('images'));
    }
    else {

      $sResult = $this->runScript($path);
    }

    return $sResult;
  }

  protected function createWindowBuilder() {

    $args = $this->getFactory()->findClass('builder');
    return $this->create('builder', array($args));
  }

  protected function startSession() {

    session_start();
  }

  protected function loadPath() {

    $aGET = $this->loadGET();
    return $this->create('path', array($aGET['path'], null, $aGET['arguments'], false));
  }

  protected function runScript(core\request $path) {

    $sResult = '';
    $bProfile = $this->readArgument('debug/profile');

    if ($bProfile) {

      $profiler = $this->create('profiler');
      $profiler->start();

      //xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
    }

    $sExtension = $path->parseExtension(true);

    if ($sExtension == $this->readArgument('redirect/extension')) {

      $path->parse();
      $redirect = $this->prepareScript($path->asFile(), $path->getArguments());

      if (!$redirect instanceof core\redirect) {

        $this->throwException('Cannot redirect at that adress');
      }

      $this->runRedirect($redirect);
    }
    else if (in_array($sExtension, $this->query('executables'))) {

      $sResult = $this->runExecutable($path);
    }
    else if (!$path->getExtension()) {

      $builder = $this->createWindowBuilder();
      $sResult = $builder->buildWindow($path, $this->get('window'), $this->read('debug/update', false), $this->read('debug/run'));
    }
    else {

      $this->throwException('No valid window defined');
    }

    if ($bProfile) {

      $profiler->stop();
      $profiler->save();
/*
      $data = xdebug_get_code_coverage();
      xdebug_stop_code_coverage();

      //print_r($data);
 */
    }

    return $sResult;
  }

  protected function runExecutable(core\request $path) {

    $sExtension = $path->getExtension();

    if ($this->getFactory()->findClass($sExtension, '', false)) {

      // with window

      $window = $this->create($sExtension, array($this));
      \Sylma::setManager('window', $window);

      $sResult = $this->createWindowBuilder()->loadObject($path, $window);
    }
    else {

     // no window

     $this->setHeaderContent($this->getMime($sExtension));

     $path->parse();
     $file = $path->asFile();

     if ($file->getExtension() !== 'vml') {

       $this->send404();
       $file = $this->getFile($this->getErrorPath());
       //$this->launchException('Can execute only view');
     }

     $sResult = (string) $this->prepareScript($file, $path->getArguments());
    }

    return $sResult;
  }

  protected function prepareScript(fs\file $file, core\argument $args = null, core\argument $contexts = null) {

    $builder = $this->getManager(self::PARSER_MANAGER);

    $result = $builder->load($file, array(
      'arguments' => $args,
      'contexts' => $contexts,
    ), $this->readArgument('debug/update', false), $this->readArgument('debug/run'), true);

    return $result;
  }

  public function getExtensions() {

    return $this->getArgument('extensions')->query();
  }

  public function send404() {

    header('HTTP/1.0 404 Not Found');
  }

  public function getMime($sExtension) {

    if (!$sResult = $this->readArgument("mime/$sExtension", false)) {

      $this->launchException('Unknown content type');
      //$sResult = $this->readArgument("mime/default");
    }

    return $sResult;
  }

  public function setHeaderCache($iTime, $bPublic = true) {

    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + $iTime));
    //if ($bPublic) header('Cache-Control: public');

    header_remove('Pragma');
  }

  public function setHeaderContent($sMime, $sCharset = 'utf-8') {

    $sType = "Content-Type: $sMime;";
    if ($sCharset) $sType .= "charset=$sCharset";

    header($sType);
    //header("Vary: Accept");
  }

  protected function loadMaintenance() {

    $sResult = '';

    if ($this->readArgument('maintenance/enable') && !\Sylma::isAdmin()) {

      $sResult = $this->getFile($this->readArgument('maintenance/file'))->execute();
    }

    return $sResult;
  }

  protected function setErrorReporting() {

    error_reporting(\Sylma::read('exception/level'));

    libxml_use_internal_errors(false);
  }

  protected function loadGET() {

    $aResult = array();

    if (array_key_exists('q', $_GET) && $_GET['q']) {

      $aResult['path'] = '/' . $_GET['q'];
      unset($_GET['q']);
    }
    else {

      $aResult['path'] = '/';
    }

    $aResult['arguments'] = $_GET;

    return $aResult;
  }

  public function loadPOST($bArgument = false) {

    if($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0)
    {
      $displayMaxSize = ini_get('post_max_size');

      switch(substr($displayMaxSize,-1))
      {
        case 'G':
          $displayMaxSize = $displayMaxSize * 1024;
        case 'M':
          $displayMaxSize = $displayMaxSize * 1024;
        case 'K':
           $displayMaxSize = $displayMaxSize * 1024;
      }

      $this->launchException('Posted data is too large. '. $_SERVER['CONTENT_LENGTH']. ' bytes exceeds the maximum size of '. $displayMaxSize.' bytes.');
    }

    return $bArgument ? $this->createArgument($_POST) : $_POST;
  }

  /**
   * Load Redirect session var, if present means it has been redirected - $_SESSION['redirect'], $_POST in 'post'
   * @return core\redirect
   */
  public function loadRedirect() {

    $redirect = $this->create('redirect');

    // Une redirection a été effectuée

    if (array_key_exists('redirect', $_SESSION)) {

      $redirect = unserialize($_SESSION['redirect']);
      unset($_SESSION['redirect']);

      // Récupération des messages du Redirect et suppression

      if (!$redirect instanceof core\redirect) {

        $this->throwException('Cannot get back the redirect');
      }

    } else {

      if ($aPost = $this->loadPost()) $redirect->setArgument('post', $aPost);
    }

    return $redirect;
  }

  protected function runRedirect(core\redirect $redirect) {

    $_SESSION['redirect'] = serialize($redirect);

    if (!$sPath = (string) $redirect) {

      $this->throwException('Bad redirection');
    }

    header("Location: $sPath");
  }

  protected function loadResults() {

    if (!array_key_exists('results', $_SESSION)) $_SESSION['results'] = array();
    self::$aResults = $_SESSION['results'];
  }

  public function addStat($sPath) {

    if (!isset(self::$aStats[$sPath])) self::$aStats[$sPath] = 0;
    self::$aStats[$sPath]++;
  }

  public function getStats() {

    $arg = new core\argument\advanced\Treed(self::$aStats);

    $aTree = $arg->parseTree();
    $aResult = $arg->renderTree($aTree);

    return $aResult[1];
  }

  public function getURL() {

    return
      (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'];
  }
}