<?php

namespace sylma\parser\action;
use \sylma\core, sylma\parser, sylma\dom, sylma\storage\fs;

class Controler extends core\module\Domed implements core\factory {

  const FS_EDITABLE = 'fs/editable';

  /**
   * Format action builded with @method buildAction(), must be set to FALSE in production
   */
  const FORMAT_ACTION = true;

  public function __construct() {

    //$this->loadDefaultArguments();

    $this->setDirectory(__file__);
    $this->setNamespace(parser\action::NS);
    $this->setArguments('controler.yml');
  }

  public function runAction($sPath, array $aArguments = array()) {

    $action = $this->getAction($sPath, $aArguments);
    return $action->asDOM();
  }

  public function getAction($sPath, array $aArguments = array(), fs\directory $dir = null) {

    require_once('core/functions/Path.php');

    $path = $this->create('path', array(core\functions\path\toAbsolute($sPath, $dir)));
    $fs = \Sylma::getControler('fs');
    //$file = $fs->getFile($sPath, true, );

    return $this->loadAction($path->getFile());
  }

  public function buildAction(dom\handler $doc, array $aArguments = array(), fs\editable\directory $dir = null, fs\directory $base = null, $sName = '') {

    if (!$dir) {

      $fs = $this->getControler(self::FS_EDITABLE);

      $user = $this->getControler('user');
      $tmp = $fs->getDirectory((string) $user->getDirectory('#tmp'));

      $dir = $tmp->createDirectory();
    }

    if ($sName) $file = $dir->createFile($sName . '.eml');
    else $file = $dir->createFile('eml', true);

    $doc->saveFile($file, self::FORMAT_ACTION);

    return $this->loadAction($file, $aArguments, $base);
  }

  protected function loadAction(fs\file $file, array $aArguments = array(), $base = null) {

    $result = $this->create('action', array($file, $aArguments, $base));

    if ($parent = $this->getControler('parser')->getContext('action/current')) {

      $result->setParentParser($parent);
      $result->setContexts($parent->getContexts());
    }

    return $result;
  }

  public function getArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::getArgument($sPath, $mDefault, $bDebug);
  }

  public function getDirectory($sPath = '', $bDebug = true) {

    return parent::getDirectory($sPath, $bDebug);
  }

  public function readArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::readArgument($sPath, $mDefault, $bDebug);
  }

  public function createAction(fs\file $file, array $aArguments = array(), array $aContexts = array(), $dir = null) {

    $dir = $dir ? $file->getParent() : $dir;

    $result = $this->create('action', array($file, $aArguments, $dir));
    $result->setContexts($aContexts);

    return $result;
  }

  public function createContext() {

    return $this->create('context');
  }

}