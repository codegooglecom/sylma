<?php

require_once('core/module/Module.php');

class Inspector extends Module {
  
  const ELEMENT_CLASS = 'element';
  const CLASS_CLASS = 'class'; // :(
  
  const MESSAGES_STATUT = 'warning';
  const NS = 'http://www.sylma.org/modules/inspector';
  
  public function __construct() {
    
    $this->setArguments(Sylma::get('modules/inspector'));
    
    $this->setDirectory(__file__);
    $this->setNamespace(self::NS);
  }
  
  public function getDeclared() {
    
    try {
      
      $system = new XArguments((string) $this->getFile('system-classes.yml'));
      $system->merge(new XArguments((string) $this->getFile('sylma-classes.yml')));
      $aAll = get_declared_classes();
      
      $root = $this->create(self::ELEMENT_CLASS, array('classes', null, null, self::NS));
      foreach (array_diff($aAll, $system->query()) as $sClass) $root->addNode('class', $sClass);
      
      return $root->getDocument();
      
    } catch (SylmaExceptionInterface $e) {
      
      return null;
    }
  }
  
  public function stringClass($sClass) {
    
    $result = null;
    
    try {
      
      $class = $this->create(self::CLASS_CLASS, array($sClass, $this, array('parent' => false)));
      $doc = $class->parse();
      
      if ($doc && !$doc->isEmpty()) $sResult = $doc->parseXSL($this->getTemplate('class-string.xsl'), false);
      
      $result = new HTML_Tag('pre', $sResult);
    }
    catch (SylmaExceptionInterface $e) {
      
      
    }
    
    return $result;
  }
  
  /**
   * Read the module @settings /classes
   */
  public function getModule($sSettings) {
    
    $args = new XArguments($sSettings, $this->getNamespace());
    
    return Arguments::buildDocument(array('classes' => array('#class' => $this->extractClasses($args))), $this->getNamespace());
  }
  
  private function extractClasses(SettingsInterface $class) {
    
    $aResult = array();
    
    if ($classes = $class->get('classes', false)) {
      
      foreach ($classes as $sKey => $subClass) $aResult[] = $this->createClass($sKey, $subClass);
    }
    
    return $aResult;
  }
  
  private function createClass($sName, SettingsInterface $class) {
    
    return array(
      '@key' => $sName,
      '@name' => $class->get('name'),
      'file' => $class->get('file', false),
      '#class' => $this->extractClasses($class),
    );
  }
  
  /**
   * Load full class and sub-classes
   */
  public function getClassSettings($sKey, $sPath) {
    
    $args = new XArguments($sPath, $this->getNamespace());
    $class = $this->loadClass($sKey, $args);
    
    if ($sFile = $class->read('file', false)) $class->set('file', path_absolute($sFile, $args->getFile()->getParent()));
    if (!$class->read('name')) $this->throwException(txt('No name defined for class %s', $sKey));
    
    return $this->getClass($class->read('name'), $class->read('file', false));
  }
  
  /**
   * Load full class and sub-classes
   */
  public function getClass($sClass, $sFile = '') {
    
    $result = null;
    dspf(get_include_path());
    try {
      
      if ($sFile) Controler::loadClass($sClass, $sFile);
      
      $class = $this->create(self::CLASS_CLASS, array($sClass, $this));
      
      $result = $class->parse();
      if ($sFile) $result->setAttribute('file', $sFile);
    }
    catch (SylmaExceptionInterface $e) {
      
      
    }
    catch (Exception $e) {
      
      Sylma::loadException($e);
    }
    
    return $result;
  }
}


