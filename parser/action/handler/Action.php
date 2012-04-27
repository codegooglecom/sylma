<?php

namespace sylma\parser\action\handler;
use sylma\core, sylma\parser, sylma\storage\fs, sylma\dom;

require_once('Basic.php');
require_once('core/stringable.php');

class Action extends Basic implements core\stringable {

  protected $file;
  protected $controler;

  protected $aArguments = array();
  protected $aContexts = array();

  protected $action = null;
  protected $bRunned = false;

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

  public function setArgument($sKey, $mValue) {

    $this->aArguments[$sKey] = $mValue;
  }

  protected function getContexts() {

    return $this->aContexts;
  }

  protected function setContexts(array $aContexts) {

    $this->aContexts = $aContexts;
  }

  protected function getClassName(fs\file $file) {

    $sNamespace = '\sylma' . str_replace('/', '\\', (string) $file->getParent());
    $sClass = 'sylma' . ucfirst(strtolower($file->getSimpleName()));

    return array(
      'namespace' => $this->cleanPath($sNamespace),
      'class' => $this->cleanPath($sClass),
    );
  }

  protected function createAction($sClass) {

    $result = new $sClass($this->getBaseDirectory(), $this, $this->getContexts(), $this->aArguments);

    foreach ($this->getControlers() as $sName => $controler) {

      $result->setControler($controler, $sName);
    }

    return $result;
  }

  /**
   * Allow management of multiple calls on same action
   * @return parser\action\cached
   */
  protected function runAction() {

    if (!$this->isRunned()) {

      $this->setAction(parent::runAction());
      $this->isRunned(true);
    }

    return $this->getAction();
  }

  protected function isRunned($mValue = null) {

    if (!is_null($mValue)) $this->bRunned = $mValue;

    return $this->bRunned;
  }

  protected function getAction() {

    return $this->action;
  }

  protected function setAction(parser\action\cached $action) {

    $this->action = $action;
  }

  protected function runCache(fs\file $file) {

    require_once($file->getRealPath());

    $aClass = $this->getClassName($this->getFile());
    $sClass = $aClass['namespace'] . '\\' . $aClass['class'];

    return $this->createAction($sClass);
  }

  protected function reflectAction() {

    $parser = $this->getControler();
    $doc = $this->getFile()->getDocument();

    try {

      $action = $parser->create('dom', array($parser, $doc, $this->getBaseDirectory()));
      $result = $action->asDOM();
    }
    catch (core\exception $e) {

      $e->addPath($this->getFile()->asToken());
      throw $e;
    }

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
      //dspm($this->getFile()->asToken());
      echo '<pre>' . $tmp->asString(true) . '</pre>';
    }

    // set new class and file

    $classFile = $tmpDir->getFile($sPath, fs\basic\Resource::DEBUG_EXIST);

    $template = $this->getTemplate('../php/class.xsl');
    $aClass = $this->getClassName($this->getFile());

    $template->setParameters(array(
      'namespace' => substr($aClass['namespace'], 1),
      'class' => $aClass['class'],
      'template' => $tpl->getRealPath(),
    ));

    $sResult = $template->parseDocument($method, false);
    $classFile->saveText($sResult);

    if ($method->getRoot()->testAttribute('use-template')) {

      $template = $this->getTemplate('../php/template.xsl');

      if ($sResult = $template->parseDocument($method, false)) {

        $tpl->saveText(substr($sResult, 22));
      }
    }

    return $classFile;
  }

  protected function parseString(core\stringable $mVal) {

    return $mVal->asString();
  }

  protected function parseObject(parser\action\cached $mVal) {

    return $mVal->asObject();
  }

  public function getContext($sContext) {

    $action = $this->runAction();
    return $action->getContext($sContext);
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
}