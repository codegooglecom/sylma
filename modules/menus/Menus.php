<?php

class TreeMenus extends Module {
  
  const PREFIX = 'menu';
  
  private $document;
  private $action;
  private $breadcrumb;
  
  public function __construct(DOMDocument $document, $mAction = null) {
    
    $this->action = $mAction;
    $this->setDirectory(__file__);
    
    $this->setArguments('settings.yml');
    $this->setNamespace($this->getArgument('namespace'), self::PREFIX);
    $this->setNamespace(Sylma::get('actions/namespace'), 'le', false);
    
    $this->document = $document;
    
    if ($mAction && is_object($mAction)) {
      
      $this->breadcrumb = $this->buildBreadcrumb($mAction);
    }
  }
  
  protected function buildBreadcrumb(XML_Action $action) {
    
    $sPath = $action->getPath()->getActionPath();
    
    $breadcrumb = $this->create('document');
    $breadcrumb->addNode('breadcrumb', null, array(), $this->getNamespace());
    
    if ($current = $this->document->get("//*[@absolute-path=\"$sPath\"]")) {
      
      do {
        
        $current->addClass('active');
        
        $category = $breadcrumb->shift($current);
        $category->cleanChildren();
        
      } while (($current = $current->getParent()) && $current->getName() == 'category');
      
    } else {
      
      $breadcrumb->addNode('category', null, array('title' => $this->getTitle()));
    }
    
    if ($breadcrumb->countChildren() == 1) {
      
      $breadcrumb->insertNode('category', null, array(
        'file' => 'file',
        'absolute-path' => '/',
        'title' => 'Accueil',
      ), $breadcrumb->getFirst());
    }
  }
  
  public function getBreadcrumb() {
    
    return $this->breadcrumb;
  }
  
  public function getAction() {
    
    return $this->action;
  }
  
  public function isRoot() {
    
    if ((string) $this->getAction()->getPath() == '/index.eml') return true;
    else return false;
  }
  
  public function getGroup($sName) {
    
    $sPrefix = $this->getPrefix();
    if (!$group = $this->document->get("//$sPrefix:group[@name='$sName']", $this->getNS())) {
      
      $this->log(txt('Groupe %s introuvable', $sName));
    }
    
    return $group;
  }
  
  public function buildGroup($sName, XSL_Document $template, $sExtension = '') {
    
    $oResult = null;
    
    if ($group = $this->getGroup($sName)) {
      
      if ($sExtension) $template->setParameter('extension', $sExtension);
      $oResult = $template->parseDocument($this->create('document', array($group)));
    }
    return $oResult;
  }
  
  public function getTitle() {
    
    $sTitle = '';
    if ($this->getAction() && !$this->getAction()->isEmpty()) $sTitle = ucfirst($this->getAction()->read('/*/le:settings/le:name', $this->getNS()));
    
    return $sTitle;
  }
}
