<?php

interface LoggerInterface {
  
  public function addLog($sNamespace, $mMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT);
}