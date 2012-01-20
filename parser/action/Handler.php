<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\parser, \sylma\storage\fs;

require_once('parser\action.php');
require_once('core\module\Domed.php');

/**
 * "Controller free" class.
 */
class Handler extends core\module\Domed implements parser\action {

  const CONTROLER_ALIAS = 'action';
  const DEBUG_UPDATE = true;

  const FS_CONTROLER = 'fs/editable';

  protected $file;
  protected $controler;

  protected $aArguments = array();
  protected $baseDirectory = null;

  public function __construct(fs\file $file, array $aArguments = array(), fs\directory $base = null) {

    $this->aArguments = $aArguments;

    $this->setControler(\Sylma::getControler(self::CONTROLER_ALIAS));
    $this->setDirectory(__file__);
    $this->loadDefaultArguments();

    $this->setNamespace($this->getControler()->getNamespace());

    $this->setFile($file);

    if ($base) $this->setBaseDirectory($base);
    else $this->setBaseDirectory($file->getParent());
  }

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
  protected function getFile($sPath = '', $bDebug = true) {

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

  protected function getClassName(fs\file $file) {

    $sNamespace = str_replace('/', '\\', (string) $file->getParent());
    $sClass = 'sylma' . ucfirst(strtolower($file->getSimpleName()));

    return array(
      'namespace' => $this->cleanPath($sNamespace),
      'class' => $this->cleanPath($sClass),
    );
  }

  protected function runCache(fs\file $file) {

    require_once($file->getRealPath());

    $result = null;

    $aClass = $this->getClassName($this->getFile());
    $sClass = $aClass['namespace'] . '\\' . $aClass['class'];

    $action = new $sClass($this->getBaseDirectory(), $this, $this->createArgument($this->aArguments));
    $result = $action->asDOM();

    return $result;
  }

  protected function loadDOM() {

    $parser = $this->getControler();
    $doc = $this->getFile()->getDocument();

    $action = $parser->create('dom', array($parser, $doc, $this->getBaseDirectory()));

    $result = $action->asDOM();

    $aClass = $this->getClassName($this->getFile());

    $result->getRoot()->setAttributes(array(
      'namespace' => substr($aClass['namespace'], 1),
      'class' => $aClass['class'],
    ));

    return $result;
  }

  protected function parseDOM() {

    $file =  $this->getFile((string) $this->getFile());
    $fs = $file->getControler();

    $sClass = $file->getName() . '.php';
    $sTemplate = $file->getName() . '.tpl.php';

    $dir = $file->getParent();
    $tmpDir = $dir->addDirectory(parser\action::EXPORT_DIRECTORY);

    $method = $this->loadDOM();

    //dspm((string) $method);
    $class = $tmpDir->getFile($sClass, fs\basic\Resource::DEBUG_EXIST);
    $template = $this->getTemplate('php/class.xsl');

    $sResult = $template->parseDocument($method, false);
    $class->saveText($sResult);

    $tpl = $tmpDir->getFile($sTemplate, fs\basic\Resource::DEBUG_EXIST);
    $template = $this->getTemplate('php/template.xsl');

    if ($sResult = $template->parseDocument($method, false)) {

      $tpl->saveText(substr($sResult, 22));
    }

    return $class;
  }

  public function asDOM() {

    $result = null;
    $file = $this->getFile();
    $sName = $file->getName() . '.php';

    $tmpDir = $this->getDirectory((string) $file->getParent())->addDirectory(parser\action::EXPORT_DIRECTORY);

    if ($tmpDir) {

      $tmpFile = $tmpDir->getFile($sName, 0);
    }

    if (!$tmpDir || !$tmpFile || $tmpFile->getLastChange() < $file->getLastChange() || self::DEBUG_UPDATE) {

      $tmpFile = $this->parseDOM();
    }

    $result = $this->runCache($tmpFile);
    //dspm((string) $result);
    return $result;
  }
}