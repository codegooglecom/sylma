<?php

interface SettingsInterface {
  
  public function get($sPath = '', $bDebug = true);
  public function read($sPath = '', $bDebug = true);
  //public function query($sPath = '', $bDebug = true);
  public function set($sPath = '', $mValue = null);
  //public function add($sPath = '', $mValue = null);
  
  // public function merge();
  // public function parse();
  // public function __toString();
  
}