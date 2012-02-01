<?php

namespace sylma\parser\action;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom;

require_once('core/module/Domed.php');
require_once('parser/action.php');
require_once('core/stringable.php');

/**
 * "Controller free" class.
 */
class Handler extends core\module\Domed implements parser\action, core\stringable {

  const CONTROLER_ALIAS = 'action';

  const FS_CONTROLER = 'fs/editable';

  const DEBUG_UPDATE = true; // default : false
  const DEBUG_RUN = true; // default : true
  const DEBUG_SHOW = false; // default : false

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

    $result = new $sClass($this->getBaseDirectory(), $this, $this->createArgument($this->aArguments));

    return $result;
  }

  protected function reflectAction() {

    $parser = $this->getControler();
    $doc = $this->getFile()->getDocument();

    $action = $parser->create('dom', array($parser, $doc, $this->getBaseDirectory()));

    $result = $action->asDOM();

    return $result;
  }

  protected function buildAction() {

    $file =  $this->getFile((string) $this->getFile());
    $fs = $file->getControler();

    $sPath = $file->getName() . '.php';
    $sTemplate = $file->getName() . '.tpl.php';

    $dir = $file->getParent();
    $tmpDir = $dir->addDirectory(parser\action::EXPORT_DIRECTORY);
    $tpl = $tmpDir->getFile($sTemplate, fs\basic\Resource::DEBUG_EXIST);

    $method = $this->reflectAction();

    if (self::DEBUG_SHOW) {
      $tmp = $this->create('document', array($method));
      dspm($this->getFile()->asToken());
      dspm(new \HTML_Tag('pre', $tmp->asString(true)));
    }

    // set new class and file

    $class = $tmpDir->getFile($sPath, fs\basic\Resource::DEBUG_EXIST);

    $template = $this->getTemplate('php/class.xsl');
    $aClass = $this->getClassName($this->getFile());

    $template->setParameters(array(
      'namespace' => substr($aClass['namespace'], 1),
      'class' => $aClass['class'],
      'template' => $tpl->getRealPath(),
    ));

    $sResult = $template->parseDocument($method, false);
    $class->saveText($sResult);

    if ($method->getRoot()->testAttribute('use-template')) {

      $template = $this->getTemplate('php/template.xsl');

      if ($sResult = $template->parseDocument($method, false)) {

        $tpl->saveText(substr($sResult, 22));
      }
    }

    return $class;
  }

  protected function runAction() {

    $result = null;
    $file = $this->getFile();
    $sName = $file->getName() . '.php';

    $tmpDir = $this->getDirectory((string) $file->getParent())->addDirectory(parser\action::EXPORT_DIRECTORY);

    if ($tmpDir) {

      $tmpFile = $tmpDir->getFile($sName, 0);
    }

    if (!$tmpDir || !$tmpFile || $tmpFile->getLastChange() < $file->getLastChange() || self::DEBUG_UPDATE) {

      $tmpFile = $this->buildAction();
    }

    if (self::DEBUG_RUN) $result = $this->runCache($tmpFile);
    else {

      $this->throwException(t('No result, DEBUG_RUN set to TRUE'));
    }

    return $result;
  }

  protected function parseDOM(dom\domable $val) {

    return $val->asDOM();
  }

  protected function parseString(core\stringable $mVal) {

    return $mVal->asString();
  }

  protected function parseObject(parser\action\cached $mVal) {

    return $mVal->asObject();
  }

  public function asObject() {

    $action = $this->runAction();
    return $this->parseObject($action);
  }

  public function asArray() {

    $action = $this->runAction();
    return $action->asArray();
  }

  public function asString($iMode = 0) {

    $action = $this->runAction();
    return $this->parseString($action);
  }

  public function asDOM() {

    $action = $this->runAction();
    return $this->parseDOM($action);
  }
}