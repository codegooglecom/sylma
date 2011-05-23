<?php

class WindowXML extends XML_Document implements WindowInterface {
  
  private $sMode = '';
  
  public function loadAction($oAction) {
    
    Controler::setContentType('xml');
    $this->sMode = Controler::getPath()->getAssoc('xml-mode');
    
    if ($oAction instanceof XML_Action) {
      
      $oResult = $oAction->parse();
      
      if (is_string($oResult)) $this->add('root', $oResult);
      else $this->set($oResult);
      
    } else if ($oAction instanceof XML_File) {
      
      $this->set(new XML_Document((string) $oAction));
      
    } else {
      
      $this->set(new XML_Element('root', (string) $oAction));
    }
  }
  
  public function __toString() {
    
    if ($this->sMode == 'html' || (Controler::getPath()->getExtension() == 'rss')) {
      
      $oView = new XML_Document($this);
      $oView->formatOutput();
      
      return $oView->display(true);
      
    } else if ($this->sMode == 'htm') {
      
      return $this->display(true, false);
      
    } else {
      
      return parent::__toString();
    }
  }
}

