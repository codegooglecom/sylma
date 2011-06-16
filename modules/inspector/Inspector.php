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
  
  public function getClass($sClass) {
    
    try {
      
      include('Method.php');
      
      $class = $this->create(self::CLASS_CLASS, array($sClass, $this));
      
      //dspf($class->parse());
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


