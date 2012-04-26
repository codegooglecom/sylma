<?php

class Redirection implements WindowActionInterface {
  
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
    
    $mResult = $oAction->parse();
    
    if (!is_object($mResult) || !$mResult instanceof Redirect) {
      
      $mResult = new Redirect('/');
      dspm(xt('Aucune redirection dans l\'action (%s), redirection par défaut effectuée', view($mResult)), 'action/warning');
    }
    
    return $mResult;
  }
  
  public function __toString() {
    
    return t('Erreur : Problème dans la redirection');
    //return xt('Erreur : Problème dans la redirection %s', new HTML_A('/hello', 'Cliquez ici pour revenir à la page d\'accueil'));
  }
}

