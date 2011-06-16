<?php

require_once('NodeInterface.php');

interface ElementInterface extends NodeInterface {
  
  public function setAttribute($sName, $sValue = '', $sUri = null);
  public function add();
  public function get($sQuery, $mNS = '', $sUri = '');
  
}