<?php

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
      
      $class = $this->create(self::CLASS_CLASS, array($sClass, $this, false));
      $doc = $class->parse();
      // dspf($doc);
      if ($doc && !$doc->isEmpty()) $sResult = $doc->parseXSL($this->getTemplate('class-string.xsl'), false);
      
      $result = new HTML_Tag('pre', $sResult);
    }
    catch (SylmaExceptionInterface $e) {
      
      
    }
    
    return $result;
  }
  
  public function getClass($sClass) {
    
    try {
      
      $class = $this->create(self::CLASS_CLASS, array($sClass, $this));
      
      // dspf($class->parse());
      return $class->parse();
    }
    catch (SylmaExceptionInterface $e) {
      
      
    }
    catch (Exception $e) {
      
      Sylma::loadException($e);
      //$e->loadException();
    }
    
    return null;
  }
}


