<?php

namespace sylma\parser\action;
use \sylma\core, \sylma\parser, \sylma\storage\fs;

require_once('parser\action.php');
require_once('core\module\Domed.php');

/**
 * "Controller free" class.
 */
class Handler extends core\module\Domed implements parser\action {
  
  const CONTROLER_ALIAS = 'parser/action';
  const DEBUG_UPDATE = true;
  
  protected $file;
  protected $controler;
  
  protected $aArguments = array();
  
  public function __construct(fs\file $file, array $aArguments = array()) {
    
    $this->setFile($file);
    $this->aArguments = $aArguments;
    
    $this->setControler(\Sylma::getControler(self::CONTROLER_ALIAS));
    $this->setDirectory(__file__);
    $this->loadDefaultArguments();
    
    $this->setNamespace($this->getControler()->getNamespace());
  }
  
  public function setControler(core\factory $controler) {
    
    $this->controler = $controler;
  }
  
  public function getControler() {
    
    return $this->controler;
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
  
  protected function runCache(fs\file $file) {
    
    require_once((string) $file);
    
    $result = null;
    $action = new ActionTest($this->getFile()->getParent(), $this);
    
    $result = $action->asDOM();
    
    return $result;
  }
  
  protected function loadDOM() {
    
    $parser = $this->getControler();
    $doc = $this->getFile()->getDocument();
    
    $action = $parser->create('dom', array($parser, $doc, $this->getFile()->getParent()));
    
    return $action->asDOM();
  }
  
  protected function parseDOM() {
    
    $file = $this->getFile();
    $fs = $file->getControler();
    
    $sClass = $file->getName() . '.php';
    $sTemplate = $file->getName() . '.tpl.php';
    
    $fs->setMode('editable');
    
    $dir = $fs->getDirectory((string) $file->getParent());
    $tmpDir = $dir->addDirectory('#tmp');
    
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
    
    $fs->setMode('');
    
    return $class;
  }
  
  public function asDOM() {
    
    $result = null;
    $file = $this->getFile();
    $sName = $file->getName() . '.php';
    
    $tmpDir = $file->getParent()->getDirectory('#tmp');
    
    if ($tmpDir) {
      
      $tmpFile = $tmpDir->getFile($sName, 0);
    }
    
    if (!$tmpDir || !$tmpFile || $tmpFile->getLastChange() < $file->getLastChange() || self::DEBUG_UPDATE) {
      
      $tmpFile = $this->parseDOM();
    }
    
    $result = $this->runCache($tmpFile);
    dspm((string) $result);
    return $result;
  }
}