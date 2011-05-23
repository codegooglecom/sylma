<?php

class WindowAction extends XML_Document implements WindowActionInterface {
  
  private $sOnLoad = '';
  
  public function addOnLoad($sValue) {
    
    return null;
  }
  
  public function addJS($sHref, $mContent = null) {
    
    return null; // TODO
  }
  
  public function addCSS($sHref = '') {
    
    return null; // TODO
  }
  
  public function loadAction($oAction) {
    
    Controler::setContentType('xml');
    
    // $oRoot = $this->set(new XML_Element('action', null, null, 'salut'));
    // $oContent = $oRoot->addNode('content', null, null, 'salut');
    
    $oRoot = $this->set(new XML_Element('action', null, null, SYLMA_NS_XHTML));
    $oContent = $oRoot->addNode('content', null, null, SYLMA_NS_XHTML);
    
    if ($oAction instanceof XML_Action) { // action
      
      $oResult = $oAction->parse();
      $oContent->add($oResult);
      
    } else if ($oAction instanceof XML_File) { // file
      
      $oContent->add(new XML_Document((string) $oAction));
      
    } else {
      
      $oContent->add($oAction);
    }
    
    $oRoot->addNode('messages', Controler::getMessages());
    
    $aKeys = array_reverse(array_keys(Controler::getResults()));
    
    if (Controler::countResults() >= 1) $oContent->setAttribute('recall', $aKeys[1]); // TODO
    if (Controler::countResults() == 2) $oContent->setAttribute('methods', $aKeys[0]); // TODO
    
    $oInfos = $oRoot->addNode('infos', Controler::getInfos());
    $oInfos->getFirst()->addClass('msg-infos-sub');
  }
  
  public function __toString() {
    
    $oView = new XML_Document($this);
    $oView->formatOutput();
    
    return $oView->display();
  }
}

