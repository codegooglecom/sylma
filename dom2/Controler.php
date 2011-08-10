<?php

namespace sylma\dom;

class Controler extends \ModuleManager {
  
  const NS = 'http://www.sylma.org/dom/controler';
  const SETTINGS = 'settings.yml';
  const CLASSBASE_TOKEN = '@sylma-classbase';
  
  protected $aDefaultClasses = array();
  protected $aClasses = array(
    'document' => 'DOMDocument',
    'element' => 'DOMElement',
  );
  
  protected $directory;
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    $this->setArguments(self::SETTINGS);
    $this->setNamespace(self::NS);
  }
  
  public function registerDocument(dom\document $doc, core\argument $settings = null) {
    
    $aClasses = array();
    
    if (!$this->aClasses || $settings) {
      
      $this->getArguments()->registerToken(self::CLASSBASE_TOKEN);
      
      $classes = $this->getArguments()->get('classes')->merge($settings->get('classes'));
      
      foreach ($this->aClasses as $sKey => $sClass) {
        
        if ($class = $classes->get($sKey)) {
          
          if ($sClassBase = $classes->getToken(self::CLASSBASE_TOKEN)) {
            
            $class->set('name', path_absolute($class->read('name'), $sClassBase, '\\'));
          }
          
          $aClasses[$sClass] = $class->get('name');
        }
      }
      
      $this->getArguments()->unRegisterToken(self::CLASSBASE_TOKEN);
      
      if (!$settings) $this->aDefaultClasses = $aClasses;
    }
    else {
      
      $aClasses = $this->aDefaultClasses;
    }
    
    return $aClasses;
  }
}
