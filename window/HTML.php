<?php

class WindowHTML extends XML_Action {
  
  private $oHead = null;
  private $sOnLoad = '';
  
  public function addOnLoad($sContent) {
    
    $this->sOnLoad .= "\n".$sContent;
  }
  
  public function addJS($sHref, $mContent = null) {
    
    if ($oHead = $this->getHead()) {
      
      if ($mContent) $oHead->add(new HTML_Script('', (string) $mContent));
      else if (!$oHead->get("ns:script[@src='$sHref']")) $oHead->add(new HTML_Script($sHref));
      
    }// else dspm(xt('Impossible d\'ajouter le fichier script %s', new HTML_Strong($sHref)), 'warning');
  }
  
  public function addCSS($sHref = '') {
    
    if (($oHead = $this->getHead()) && !$oHead->get("ns:link[@href='$sHref']")) {
      
      $oHead->add(new HTML_Style($sHref));
      
    }// else dspm(xt('Impossible d\'ajouter la feuille de style %s', new HTML_Strong($sHref)), 'warning');
  }
  
  public function getHead() {
    
    if (!$this->oHead) $this->oHead = new XML_Element('head', null, null, SYLMA_NS_XHTML);
    
    return $this->oHead;
  }
  
  public function printXML() {
    
    $sDocType = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
    
    // Action parsing
    
    $oView = new XML_Document($this);
    
    if ($oView->isEmpty()) {
      
      throw new Exception('Frontend ne retourne aucun résultat.');
      
    } else {
      
      // Add js onload
      
      if ($this->sOnLoad) $this->addJS(null, "window.addEvent('domready', function() {\n".$this->sOnLoad."\n});");
      
      if ($oHead = $oView->get('//ns:head')) $oHead->add($this->getHead()->getChildren());
      else dspm(xt('Impossible de trouver l\'en tête de la fenêtre dans %s', view($oView)), 'action/error');
      
      // Put messages and infos
      
      $sBody = '//ns:body';
      
      // infos
      
      if (Controler::isAdmin()) {
        
        $oInfos = new XML_Element('div', Controler::getInfos(), array('id' => 'msg-admin'));
        
        if ($oContainer = $oView->get($sBody)) $oContainer->add($oInfos);
        else $oView->add($oInfos);
      }
      
      // messages
      
      if (!$sMessage = Controler::getWindowSettings()->read('messages')) $sMessage = $sBody;
      
      if (Sylma::get('dom/debug/show-queries')) {
        
        $args = new XArguments(XML_Controler::$aQueries);
        dspm(XArguments::renderTree($args->parseTree()));
      }

      if ($oContainer = $oView->get($sMessage)) $oContainer->shift(Controler::getMessages());
      else {
        
        dspm(xt('Containeur %s introuvable', new HTML_Strong($sMessage)), 'action/warning');
        $oView->add(Controler::getMessages());
      }

      Controler::useMessages(false);
      
      // Fill empty html tags
      // TODO check not to heavy (metal)
      if ($oElements = $oView->query(SYLMA_HTML_TAGS, 'html', SYLMA_NS_XHTML)) {
        
        foreach ($oElements as $oElement) {
          
          if (!$oElement->hasChildren()) $oElement->set(' ');
        }
      }
      
      // Remove security elements
      
      if ($oElements = $oView->query('//@ls:owner | //@ls:mode | //@ls:group', 'ls', SYLMA_NS_SECURITY)) $oElements->remove();
      
      if ($oView->isEmpty()) {
        
        return (string) xt('Problème lors du chargement du site. Nous nous excusons pour ce désagrément. %s pour revenir à la page d\'accueil', new HTML_Br.new HTML_A('/', t('Cliquez-ici')));
        
      } else {
        
        $oView->formatOutput();
        return $sDocType."\n".$oView->display(false, true);
      }
    }
  }
  
  public function __toString() {
    
    try {
      
      $sResult = $this->printXML();
      
    } catch(Exception $e) {
      
      Sylma::sendException($e);
      
      $sResult = (string) xt('Problème lors du chargement du site. Nous nous excusons pour ce désagrément. %s pour revenir à la page d\'accueil', new HTML_Br.new HTML_A('/', t('Cliquez-ici')));
    }
    
    return $sResult;
  }
}

