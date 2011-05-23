<?php

class WindowTXT implements WindowActionInterface {
  
  private $sContent = '';
  
  public function addJS($sHref, $mContent = null) {
    
  }
  
  public function addCSS($sHref = '') {
    
  }
  
  public function addOnLoad($sContent) {
    
  }
  
  public function loadAction($oAction) {
    
    if ($oAction) {
      
      if ($oAction instanceof XML_Action) {
        
        $this->sContent = (string) $oAction->parse();
        
      } else if ($oAction instanceof XML_File) {
        
        /*
        $oFinfo = new finfo(FILEINFO_MIME, ini_get('mime_magic.magicfile')); // Retourne le type mime
        
        if (!$oFinfo) {
          
          $this->sContent = "Échec de l'ouverture de la base de données fileinfo";
          
        } else {
          
          if($sMime = $oFinfo->file(MAIN_DIRECTORY.$sPath)) {
            
            $this->sContent = $sMime.'hello';
            $oFinfo->close();
            
          } else $this->sContent = 'Mime introuvable !';
          
        }
        */
        
        Controler::setContentType(Controler::getPath()->getExtension());
        
        $this->sContent = file_get_contents(MAIN_DIRECTORY.$oAction);
        
      } else $this->sContent = (string) $oAction;
      
    } else Controler::error404();
  }
  
  public function __toString() {
    
    return $this->sContent;
  }
}

