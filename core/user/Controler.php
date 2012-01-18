<?php

namespace sylma\core\user;
use sylma\core;

require_once('core/module/Domed.php');

class Controler extends core\module\Domed {
  
  protected $user;
  
  public function __construct() {
    
    $this->setDirectory(__FILE__);
    
    $this->setArguments(\Sylma::get('modules/users'));
    $this->getArguments()->merge(\Sylma::get('users'));
    
    $user = $this->create('user', array($this));
    $user->load();
    
    $this->user = $user;
  }
  
  public function getUser() {
    
    return $this->user;
  }
  
  public function getDocument($sPath, $iMode = \Sylma::MODE_READ) {
    
    return parent::getDocument($sPath, $iMode);
  }
}
