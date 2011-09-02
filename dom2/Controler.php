<?php

namespace sylma\dom;

class Controler extends \Module {
  
  const NS = 'http://www.sylma.org/dom/controler';
  const SETTINGS = 'settings.yml';
  
  protected $aDefaultClasses = array();
  protected $aClasses = array(
    'document' => 'DOMDocument',
    'element' => 'DOMElement',
  );
  
  protected $directory;
  
  protected $aStats = array();
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    $this->setArguments(self::SETTINGS);
    $this->setNamespace(self::NS);
  }
  
  public function getClasses(core\argument $settings = null) {
    
    $aClasses = array();
    
    if (!$this->aDefaultClasses || $settings) {
      
      $this->getArguments()->registerToken(self::CLASSBASE_TOKEN);
      $this->getArguments()->registerToken(self::DIRECTORY_TOKEN);
      
      $classes = $this->getArguments()->get('classes');
      if ($settings) $classes->merge($settings);
      
      foreach ($this->aClasses as $sKey => $sClass) {
        
        if ($class = $classes->get($sKey)) {
          
          if ($sClassBase = $classes->getToken(self::CLASSBASE_TOKEN)) {
            
            $class->set('name', path_absolute($class->read('name'), $sClassBase, '\\'));
          }
          
          if (!class_exists($class->read('name'))) {
            
            if ($sFile = $class->read('file', false)) $class->set('file', path_absolute($sFile, $class->getLastDirectory()));
            \Controler::loadClass($class->read('name'), $class->read('file', false));
          }
          
          $aClasses[$sClass] = $class->read('name');
        }
      }
      
      $this->getArguments()->unRegisterToken(self::CLASSBASE_TOKEN);
      $this->getArguments()->unRegisterToken(self::DIRECTORY_TOKEN);
      
      if (!$settings) $this->aDefaultClasses = $aClasses;
    }
    else {
      
      $aClasses = $this->aDefaultClasses;
    }
    
    return $aClasses;
  }
  
  public function addStat($sName, array $aArguments) {
    
    if ($this->getArgument('stats/enable')) $this->aStats[$sName][] = $aArguments;
  }
}
