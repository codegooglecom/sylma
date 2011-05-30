<?php

/**
 * Allow a transparent use of any @interface SettingsInterface implemented class
 * @author Rodolphe Gerber
 */
class Settings extends Namespaced implements SettingsInterface {
  
  /**
   * Child settings object, will be used for all calls
   * @var SettingsInterface
   */
  protected $settings;
  
  public function __construct(SettingsInterface $settings) {
    
    $this->settings = $settings;
  }
  
  public function get($sPath = '', $bDebug = true) {
    
    return $this->settings->get($sPath, $bDebug);
  }
  
  public function read($sPath = '', $bDebug = true) {
    
    return (string) $this->get($sPath, $bDebug);
  }
  
  public function set($sPath = '', $mValue = null) {
    
    return (string) $this->settings->set($sPath, $bDebug);
  }
}