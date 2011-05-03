<?php

class XUser extends User {
  
  public function __construct() {
    
    // $this->setSchema($this->getDocument('user.xsd'));
    
    
    parent::__construct();
  }
  
  public function logout() {
    
    $_SESSION = array();
    // if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
    // session_destroy();
    
    Controler::addMessage(t('Session détruite !'), 'report');
  }
  
  // public function authenticate($sUser, $sPassword) {
  //protected function loadGroups() {
  //protected function loadProfile() {
  // public function isMember($mValue) {
  
}

