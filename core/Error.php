<?php
/*
 * Classe de gestion des erreurs
 * Created on 23 oct. 2008
 */

function userErrorHandler($errno, $errstr, $errfile, $errline) {
  
  if (Controler::isAdmin()) {
    
    if (Sylma::get('messages/format/enable')) {
      
      $oMessage = new HTML_Div;
      $oMessage->add(
        new HTML_Strong("ERREUR [$errno] "),
        $errstr,
        new HTML_Tag('i', " - $errfile [ligne $errline]"));
        
    } else $oMessage = "ERREUR [$errno] $errstr - [$errline] - $errfile : ";//.Controler::getBacktrace();
    
    if (Controler::useMessages()) Controler::addMessage($oMessage, 'error');
    else if (Sylma::get('debug/enable')) echo $oMessage;
    
    return true;
  }
  
  return true;
}
