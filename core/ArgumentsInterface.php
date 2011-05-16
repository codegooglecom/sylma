<?php

interface ArgumentsInterface {
  
  public function getNamespace();
  public function get($sPath, $bDebug = true);
  // public function read($sPath, $bDebug = true);
  public function set($sPath, $mValue = null);
  // public function merge();
  // public function parse();
  // public function __toString();
  
}