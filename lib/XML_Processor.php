<?php

interface XML_ProcessorInterface {
  
  public function loadElement($oElement, XML_Action $oAction = null);
  public function startAction(XML_Action $oAction);
  public function stopAction();
}

class XML_Processor implements XML_ProcessorInterface {
  
  private $aActions = array();
  //private $bInterfaced = false;
  private $aElements = array();
  
  public function __construct() {
    
    $this->onLoad();
  }
  /*
  public function useInterface($bInterfaced = null) {
    
    if ($bInterfaced !== null) $this->bInterfaced = (bool) $bInterfaced;
    return $this->bInterfaced;
  }
  */
  public function startAction(XML_Action $oAction) {
    
    $this->aActions[] = $oAction;
    
    if (Controler::useStatut('action/report')) {
      
      dspm(new HTML_Span('Start processor : ', array('style' => 'color: red')).($this->getAction() ? $this->getAction()->getPath() : 'unknown'), 'action/report');
    }
  }
  
  public function stopAction() {
    
    if (Controler::useStatut('action/report')) {
      
      dspm(new HTML_Span('Stop processor : ', array('style' => 'color: red')).($this->getAction() ? $this->getAction()->getPath() : 'unknown'), 'action/report');
    }
    
    array_pop($this->aActions);
  }
  
  public function isFirst() {
    
    return (bool) (count($this->aElements) == 1);  
  }
  
  public function runChildren($mBase) {
    
    return $this->getAction()->runInterfaceList($mBase, $this->getElement());
  }
  
  public function buildChildren($oElement) {
    
    if ($this->getAction() && $oElement && $oElement->hasChildren()) {
      
      $aResults = array();
      foreach ($oElement->getChildren() as $oChild) $aResults[] = $this->getAction()->buildArgument($oChild);
      
      // return new XML_NodeList($aResults);
      return $aResults;
      
    } else return null;
  }
  
  public function getAction() {
    
    $oAction = null;
    
    if ($this->aActions) $oAction = array_last($this->aActions);
    if (!$oAction) dspm(xt('Aucune action liée au processeur'), 'warning');
    
    return $oAction;
  }
  
  public function getElement() {
    
    return array_last($this->aElements);
  }
  
  public function loadElement($oElement, XML_Action $oAction = null) {
    
    $this->aElements[] = $oElement;
    
    $mResult = $this->onElement($oElement, $oAction);
    
    if (Controler::useStatut('action/report')) dspm(xt('%s [onElement] : %s &gt; %s',
      new HTML_Tag('span', 'Processor', array('style' => 'color: red;')), view($oElement), view($mResult)), 'action/report');
    
    array_pop($this->aElements);
    
    return $mResult;
  }
  
  public function onLoad() { }
  public function onElement($oElement, XML_Action $oAction) { }
  public function parse() { }
}
