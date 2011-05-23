<?php

interface WindowInterface {
  
  public function loadAction($oAction);
}

interface WindowActionInterface extends WindowInterface {
  
  public function addJS($sHref, $mContent = null);
  public function addCSS($sHref = '');
  public function addOnLoad($sContent);
}
