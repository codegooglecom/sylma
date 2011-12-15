<?php

class Messages extends XML_Document {
  
  private $aAllowedMessages = array();
  private $aStatuts = array();
  private $oLast = null;
  private $aNS = array('lm' => SYLMA_NS_MESSAGES);
  
  public function __construct(XML_Document $oSchema = null, $mMessages = array()) {
    
    parent::__construct('messages');
    
    if ($oSchema) $this->add($oSchema->getChildren());
    
    $this->addMessages($mMessages);
  }
  
  public function addMessage(XML_Element $oMessage) {
    
    $oResult = null;
    
    if ($oMessage->useNamespace(SYLMA_NS_MESSAGES)) {
      
      // TODO: foreach ($oMessage->aArguments as $oArgument) $this->setArgument('fields'][ += $oMessage[]
      
      $sPath = $oMessage->readByName('path');
      
      if ($oCategory = $this->get($sPath)) {
        
        $oResult = $oCategory->add($oMessage);
        $this->oLast = $oMessage;
      }
      
    }
    
    return $oResult;
  }
  
  /*
   * Add a message from a String
   * 
   * @param $mMessage
   *   The message format String
   * @param $sStatut
   *   The stat of the message format String
   * @param $aArguments
   *   The arguments of the message format Array
   * @return
   *   A pointer to the node added
   **/
  public function addStringMessage($mMessage, $sPath = 'user', $aArguments = array()) {
    
    return $this->addMessage(new Message($mMessage, $sPath, $aArguments));
  }
  
  /*
   * Ajoute une liste de messages dans la pile
   * 
   * @param XML_Nodelist|array $aMessages Un tableau contenant les messages à ajouter
   **/
  public function addMessages($aMessages) {
    
    $aResult = array();
    foreach ($aMessages as $oMessage) $aResult[] = $this->addMessage($oMessage);
    
    return $aResult;
  }
  
  /*
   * Récupère les messages sous forme de liste
   * 
   * @param $sPath
   *   Si donné, seul les messages du statut correspondant seront récupérés
   **/
  public function getMessages($sPath = '') {
    
    $oResult = $this->query("$sPath//lm:message", 'lm', SYLMA_NS_MESSAGES);
    if (!$oResult->length) $oResult = array();
    
    return $oResult;
  }
  
  public function useStatut($sPath) {
    
    if (!array_key_exists($sPath, $this->aStatuts)) $this->aStatuts[$sPath] = ($this->get($sPath));
    
    return $this->aStatuts[$sPath];
  }
  
  public function hasMessages($sPath = '') {
    
    return ($this->getMessages($sPath));
  }
  
  public function unserialize($sDocument) {
    
    return parent::__construct('<?xml version="1.0" encoding="utf-8"?>'."\n".$sDocument);
  }
  
  public function parse() {
    
    if ($this->get('//lm:message', $this->aNS)) {
      /*
      if (SYLMA_MESSAGES_TIME) {
        
        $oMessages = $this->query('//lm:message[@time]', $this->aNS);
        
        $aTimes = $aElapsed = array();
        
        foreach ($oMessages as $oMessage) $aTimes[] = floatval($oMessage->getAttribute('time'));
        
        sort($aTimes);
        $iPrev = reset($aTimes);
        
        foreach ($aTimes as $iKey => $fValue) {
          
          $aElapsed[$iKey] =  $fValue - $iPrev;
          $iPrev = $fValue;
        }
        
        sort($aElapsed);
        
        $iMin = reset($aElapsed);
        $iDiff = end($aElapsed) - $iMin;
        
        $hMin = 0x0A;
        $hMax = 0xC8;
        
        $hDiff = $hMax - $hMin;
        
        foreach ($aElapsed as $iKey => $fValue) {
          
          $sValue = sprintf('%02X', $hMax - (($hDiff / $iDiff)  * ($fValue - $iMin)));
          $sColor = '#'.$sValue.$sValue.'FF';
          
          $oMessages->item($iKey)->setAttributes(array(
            'time' => number_format($fValue, 2),
            'time-color' => $sColor));
        }
      }
      */
      return $this->parseXSL(new XML_Document(Controler::getSettings('messages/template/@path'), MODE_EXECUTION));
      
    } else return null;
  }
  
}

class Message extends XML_Element {
  
  public function __construct($mMessage, $sPath = '', array $aArgs = array()) {
    
    parent::__construct('lm:message', null, null, SYLMA_NS_MESSAGES);
    
    if (!$sPath) $sPath = '/';
    
    $this->addNode('content', $mMessage, null, '');
    $this->addNode('path', $sPath, null, '');
    $this->addNode('arguments', null, null, '')->addArray($aArgs);
  }
}

