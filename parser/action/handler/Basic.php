<?php

namespace sylma\parser\action\handler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom;

require_once('core/module/Domed.php');
require_once('parser/action.php');

/**
 * "Controller free" class.
 */
abstract class Basic extends core\module\Domed implements parser\action {

  const CONTROLER_ALIAS = 'action';

  const FS_CONTROLER = 'fs/editable';

  const DEBUG_RUN = true; // default : true
  const DEBUG_SHOW = false; // default : false

  protected $file;
  protected $controler;

  protected $baseDirectory = null;

  protected function getBaseDirectory() {

    return $this->baseDirectory;
  }

  protected function setBaseDirectory(fs\directory $baseDirectory) {

    $this->baseDirectory = $baseDirectory;
  }

  protected function setFile(fs\file $file) {

    $this->file = $file;
  }

  /**
   * Allow get of object's file or object's directory's files
   *
   * @param string $sPath
   * @param boolean $bDebug
   * @return fs\file|null
   */
  public function getFile($sPath = '', $bDebug = true) {

    if ($sPath) {

      $result = parent::getFile($sPath, $bDebug);
    }
    else {

      $result = $this->file;
    }

    return $result;
  }

  protected function cleanPath($sPath) {

    return str_replace(array('-', '_', '.'), array(), $sPath);
  }

  protected function reflectAction() {

    $parser = $this->getControler();
    $doc = $this->getFile()->getDocument();

    $action = $parser->create('dom', array($parser, $doc, $this->getBaseDirectory()));

    $result = $action->asDOM();

    return $result;
  }

  protected function runAction() {

    $result = null;
    $file = $this->getFile();
    $sName = $file->getName() . '.php';

    //$sDirectory = (string) $file->getParent();
    //$sDirectory = $sDirectory ? $sDirectory : '/';

    $fs = $this->getControler('fs/cache');
    $tmpDir = $fs->getDirectory()->addDirectory((string) $file->getParent());

    if ($tmpDir) {

      $tmpFile = $tmpDir->getFile($sName, 0);
    }

    if (!$tmpDir || !$tmpFile || $tmpFile->getLastChange() < $file->getLastChange() || \Sylma::read('action/update')) {

      $tmpFile = $this->buildAction();
    }

    if (self::DEBUG_RUN) $result = $this->runCache($tmpFile);
    else {

      $this->throwException('No result, DEBUG_RUN set to TRUE');
    }

    return $result;
  }

  protected function parseDOM(dom\domable $val) {

    return $val->asDOM();
  }

  public function asDOM() {

    $action = $this->runAction();
    return $this->parseDOM($action);
  }
}