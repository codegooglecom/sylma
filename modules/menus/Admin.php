<?php

require_once('Menus.php');

class TreeMenusAdmin extends Module {
  
  protected $from;
  protected $sTo;
  
  public function __construct($sFrom, $sTo = '') {
    
    $this->setDirectory(__file__);
    $this->setArguments('settings.yml');
    
    $this->setNamespace($this->readArgument('namespace'));
    $this->setNamespace(Sylma::read('actions/namespace'), 'le', false);
    
    $this->from = $this->getDocument($sFrom, Sylma::MODE_EXECUTE);
    
    if (!$sTo && $this->from && ($file = $this->from->getFile())) {
      
      $this->sTo = $file->getParent() . '/' . $file->getSimpleName() . '-cache.xml';
    }
    
    $this->update();
  }
  
  public function update() {
    
    if (!$this->from) $this->throwException(txt('No source document defined'));
    if (!$this->sTo) $this->throwException(txt('No target document defined'));
    
    $sTarget = path_absolute($this->sTo, $this->getDirectory());
    $oElement = $this->from->getRoot();
    
    $sParentPath = $sPath = '';
    $sAbsolute = 'absolute-path';
    
    $bArguments = $bParentArguments = false;
    $bCheck = $bParentCheck = true;
    
    while ($oElement) {
      
      switch ($oElement->getName()) {
        
        case 'tree' :
        
        break;
        
        case 'group' :
          
          $bParentArguments = $oElement->testAttribute('parse-arguments', false);
          $bParentCheck = $oElement->testAttribute('check', true);
          
        break;
        
        case 'category' :
          
          if (!$sPath = $oElement->getAttribute('directory')) $sPath = $oElement->getAttribute('file');
          
          if (!$sPath) dspm(xt('Aucun chemin pour l\'élément %s', view($oElement)), 'action/warning');
          else {
            
            if ($sPath{0} != '/') $sPath = $sParentPath.'/'.$sPath;
            
            $sAbsolutePath = $sPath;
            
            $oNoDisplay = new XML_Attribute('no-display', 'true');
            $bArguments = $oElement->testAttribute('parse-arguments', $bParentArguments);
            $bCheck = $oElement->testAttribute('check', $bParentCheck);
            $sTitle = $oElement->getAttribute('title');
            
            if ($oElement->testAttribute('no-link', true) && $bCheck) {
              
              // action => load file
              
              $oPath = new XML_Path($sPath, array(), true, $bArguments);
              
              if (!$oPath->getPath()) {
                
                $oElement->add($oNoDisplay);
                
              } else {
                
                $sAbsolutePath = $oPath->getActionPath();
                $oDocument = new XML_Document((string) $oPath);
                
                if (!$sTitle) $sTitle = $oDocument->read('//le:settings/le:name', 'le', SYLMA_NS_EXECUTION);
              }
            }
            
            $oElement->setAttribute($sAbsolute, $sAbsolutePath);
            
            if (!$sTitle) $oElement->add($oNoDisplay);
            else $oElement->setAttribute('title', $sTitle);
          }
          
          dspm(xt('%s : title=%s, display=%s, arguments=%s, check=%s, path=%s',
            view($oElement), view($sTitle), view(!$oElement->testAttribute('no-display', false)), view($bArguments), view($bCheck), view($sPath)));
          
        break;
        
        default : $this->log(txt('@element %s inconnnu', $oElement->getName()));
      }
      
      // Browse tree
      
      if ($oElement->isComplex()) {
        
        // children
        
        $sParentPath = $sPath;
        $oElement = $oElement->getFirst();
        
      } else if ($oElement->getNext()) {
        
        // next
        
        $oElement = $oElement->getNext();
        
      } else {
        
        // parent
        
        while (!$oElement->isRoot() && !($oElement->getNext())) {
          
          $oElement = $oElement->getParent();
          $sParentPath = $oElement->getParent()->getAttribute($sAbsolute);
        }
        
        if ($oElement->isRoot()) $oElement = null;
        else $oElement = $oElement->getNext();
      }
    }
    
    if (!$this->from->save($sTarget)) dspm(xt('Impossible de créer le fichier %s', new HTML_Strong($sTarget)), 'file/error');
    else dspm(xt('Fichier %s crée', $sTarget), 'success');
    
    //return new Redirect('/index.sylma');
  }
}
