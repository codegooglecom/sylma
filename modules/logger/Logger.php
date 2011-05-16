<?php

class Logger extends XDB_Module implements LoggerInterface {
  
  const OPTIONS_FILE = 'options.yml';
  
  public function __construct() {
    
    $this->setArguments(self::OPTIONS_FILE);
  }
  
  public function log($sNamespace, $mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {
    
    dspf($this->getArguments());
  }
}