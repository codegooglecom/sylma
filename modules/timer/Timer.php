<?php

class Timer extends Module {
  
  const NS = 'http://www.sylma.org/modules/utils/timer';
  
  const ARGUMENTS_CLASS = 'xarguments';
  const DIRECTORY_TMP = '/tmp';
  const DIRECTORY_PREFIX = 'timer-';
  
  const FILE_PHP = 'Classes.php';
  const FILE_YAML = 'settings.yml';
  const FILE_TEMPLATE = 'class-time.xsl';
  
  const INSPECTOR_CLASS = 'inspector/controler';
  const INSPECTOR_PATH = 'modules/inspector';
  
  const CLASS_PREFIX = 'Sylma';
  
  protected $inspector;
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    $this->setArguments('settings.yml');
    $this->setNamespace(self::NS);
    
    Sylma::setControler('timer', $this->create('timer'));
    
    $this->getArguments()->set('classes/inspector', Sylma::get(self::INSPECTOR_PATH));
    $this->inspector = $this->create(self::INSPECTOR_CLASS);
  }
  
  public function loadSettings($sPath) {
    
    $sName = uniqid(self::DIRECTORY_PREFIX);
    
    if ((!$directory = Controler::getDirectory(self::DIRECTORY_TMP)) ||
        !$directory->checkRights(Sylma::MODE_WRITE) ||
        (!$directory = $directory->addDirectory($sName))) $this->throwException(txt('Temp directory not ready', $sTemp));
    
    if (!$this->inspector) $this->throwException(t('No inspector defined'));
    
    $arg = $this->create(self::ARGUMENTS_CLASS, array($sPath));
    
    if (!$classes = $arg->get('classes')) $this->throwException(txt('No classes found in @file %s', $sPath));
    
    $aClasses = $this->loadClasses($classes);
    
    $classes = Arguments::buildDocument(array('classes' => $aClasses), $this->inspector->getNamespace());
    
    $sContent = $classes->parseXSL($this->getTemplate(self::FILE_TEMPLATE), false);
    
    $file = $directory->getFile(self::FILE_PHP, FileInterface::DEBUG_EXIST);
    
    if ($file->saveText($sContent)) dspm(xt('File has been saved in %s', new HTML_A((string) $file, (string) $file)), 'success');
    else dspm(xt('Cannot save file at path %s', (string) $file), 'warning');
    // dspf(SYLMA_PATH);
    // dspf($arg->query('classes'));
    $sContent = $arg->dump();
    
    $file = $directory->getFile(self::FILE_YAML, FileInterface::DEBUG_EXIST);
    
    if ($file->saveText($sContent)) dspm(xt('File has been saved in %s', new HTML_A((string) $file, (string) $file)), 'success');
    else dspm(xt('Cannot save file at path %s', (string) $file), 'warning');
  }
  
  protected function loadClasses(SettingsInterface $classes) {
    
    $aResult = array();
    
    foreach ($classes as $class) {
      
      $sName = $class->read('name');
      
      if ($sFile = $class->read('file', false)) {
        
        $sPath = path_absolute($sFile, $classes->getLastDirectory());
      }
      else {
        
        $sPath = '';
      }
      
      $aResult[] = $this->inspector->getSimpleClass($sName, $sPath);
      
      $class->set('name', self::CLASS_PREFIX . $sName);
      $class->set('file', self::FILE_PHP);
      
      if ($subClasses = $class->get('classes', false)) $aResult = array_merge($aResult, $this->loadClasses($subClasses));
    }
    
    return $aResult;
  }
  
  // public function getClass($sClass) {
    
    
    // $doc = $inspector
    
    // if ($doc && !$doc->isEmpty()) {
      
      // $sResult = $doc;
      // $result = new HTML_Tag('pre', $sResult);
    // }
    
    // return $result;
  // }
  
  public function parse() {
    
    return Sylma::getControler('timer')->parse();
  }
}