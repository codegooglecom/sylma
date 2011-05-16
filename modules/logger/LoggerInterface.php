<?php

interface LoggerInterface {
  
  public function log($sNamespace, $mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT);
}