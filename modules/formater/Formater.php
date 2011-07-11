<?php

class Formater extends Module {
  
  public function getBacktrace() {
  
    foreach (debug_backtrace() as $aLine) {
      
      echo $aLine['file'].' - '.$aLine['class'].'::'.$aLine['function'] . ' - ' . $aLine['line'] . '<br/>';
    }
  }
}