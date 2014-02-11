<?php

namespace sylma\core\window;
use sylma\core, sylma\storage\fs;

class Builder extends core\module\Domed {

  const EXTENSION_DEFAULT = 'html';

  public function __construct(core\argument $args) {

    $this->setSettings($args);

    $this->setDirectory(__FILE__);
  }

  public function createWindow(fs\file $file, core\argument $images) {

    if (in_array($file->getExtension(), $images->query('extensions'))) {

      $window = $this->create('images', array($this->getInitializer(), $images));
      $sResult = $this->loadWindowFile($file, $window);
    }
    else {

      $sResult = $this->createWindowFile($file);
    }

    return $sResult;
  }

  protected function getInitializer() {

    return $this->getManager('init');
  }

  protected function createWindowFile(fs\file $file) {

    $sResult = '';

    switch ($file->getExtension()) {

      case 'php' :

        $this->throwException('Cannot read php files');

      default :

        $window = $this->create('window', array($this->getInitializer()));
        $sResult = $this->loadWindowFile($file, $window);

      break;
    }

    return $sResult;
  }

  protected function loadWindowFile(fs\file $file, core\window\file $window) {

    $window->setFile($file);
    return $window->asString();
  }

  public function buildWindow(core\request $path, core\argument $exts, $bUpdate = null, $bRun = true) {

    $this->setSettings($exts);

    $sExtension = strtolower($path->getExtension());
    if (!$sExtension) $sExtension = self::EXTENSION_DEFAULT;

    $settings = $this->get($sExtension);
    $sCurrent = (string) $path;

    $path->parse();

    $aPaths = $this->buildWindowStack($settings, $sCurrent);
    $aPaths[] = (string) $path->asFile();

    $aPaths = array_reverse($aPaths);
    $sMain = array_pop($aPaths);

    $file = $this->getFile($sMain);

    if (!$file->checkRights(\Sylma::MODE_EXECUTE)) {

      $this->getInitializer()->send404();
      $file = $this->getErrorWindow();
      $aPaths = array($this->getErrorPath());
    }

    $args = $path->getArguments();
    $args->set('sylma-paths', $aPaths);

    $builder = $this->getManager(self::PARSER_MANAGER);

    return $builder->load($file, array(
      'arguments' => $args,
      //'post' => $this->loadPost(true),
    ), $bUpdate, $bRun);
  }

  protected function getErrorWindow() {

    return $this->getFile($this->read('error/window'));
  }

  protected function getErrorPath() {

    return $this->read('error/action');
  }

  protected function buildWindowStack(core\argument $arg, $sPath) {

    $aResult = array();

    do {

      if ($content = $this->lookupRoute($arg, $sPath)) {

        $aResult[] = $content->read('action');
        $arg = $content->get('sub', false);
      }
      else {

        $arg = null;
      }

    } while ($arg);

    return $aResult;
  }

  /**
   * @return \sylma\core\argument
   */
  protected function lookupRoute(core\argument $args, $sCurrent) {

    $result = null;

    foreach ($args as $alt) {

      if (is_object($alt)) {

        if ($this->testRoute($alt, $sCurrent)) {

          $result = $alt;
        }
      }
    }
/*
    if (!$result) {

      $this->launchException('No route found', get_defined_vars());
    }
*/

    return $result;
  }

  protected function testRoute(core\argument $alt, $sCurrent) {

    $sPattern = $alt->read('pattern', false);

    return !$sPattern || preg_match($sPattern, $sCurrent);
  }

  public function loadObject(core\request $path, $window) {

    $path->parse();
    $file = $path->getFile();

    switch ($file->getExtension()) {

      case 'eml' : $result = $this->loadObjectAction($path, $window); break;
      case 'vml' : $result = $this->loadObjectScript($path, $window); break;

      default :

        $this->launchException(sprintf('Unknown exectuable extension : %s', $file->getExtension()));
    }

    return $result;
  }

  protected function loadObjectAction(core\request $path, core\window\action $window) {

    $action = $this->loadAction($path);

    $window->setAction($action, $path->getExtension());

    return $window->asString();
  }

  protected function loadObjectScript(core\request $path, core\window\scripted $window) {

    $window->setScript($path, $this->getInitializer()->loadPOST(true));

    return $window->asString();
  }

  public function loadAction(core\request $path) {

    $path->parse();

    return $this->createAction($path->getFile(), $path->getArguments()->asArray());
  }

  protected function createAction(fs\file $file, array $aArguments = array()) {

    return $this->create('action', array($file, $aArguments));
  }
}
