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
    $action = new ActionTest;
    $action->setControler($this);
    
    $arg = $action->asArgument();
    
    if ($arg) $result = $arg->asDOM();
    
    return $result;
  }
  
  protected function loadDOM() {
    
    $parser = $this->getControler();
    $doc = $this->getFile()->getDocument();
    
    $action = $parser->create('dom', array($parser, $doc));
    
    $method = $action->asDOM();
    
    $template = $this->getTemplate('php/source.xsl');
    
    return $template->parseDocument($method, false);
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
      
      $sResult = $this->loadDOM();
      
      $fs = $file->getControler();
      
      $fs->setMode('editable');
      
      $dir = $fs->getDirectory((string) $file->getParent());
      $tmpDir = $dir->addDirectory('#tmp');
      $tmpFile = $tmpDir->getFile($sName, fs\basic\Resource::DEBUG_EXIST);
      
      $tmpFile->saveText($sResult);
      
      $fs->setMode('');
    }
    
    $result = $this->runCache($tmpFile);
    
    return $result;
  }
}