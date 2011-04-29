<?php

class Users extends DBX_Module {
  
  public function __construct(XML_Directory $directory = null, XML_Document $dSchema = null, XML_Document $dOptions = null) {
    
    $this->setName('users');
    $this->setArguments(Sylma::get('modules/users'));
    $this->getArguments()->merge(Sylma::get('users'));
    
    if (!$directory) $directory = $this->setDirectory(__file__);
    if (!$dSchema) $dSchema = $this->getDocument('login.xsd');
    if (!$dOptions) $dOptions = $this->runAction('options');
    
    parent::__construct($directory, $dSchema, $dOptions);
  }
  
  public function connection(Redirect $redirect) {
    
    $dTemplate = $this->getTemplate('form/index.xsl');
    if (isset($_SERVER['HTTPS'])) $dTemplate->setParameter('https', $_SERVER['HTTPS']);
    
    return $this->add(
      $redirect,
      $this->setFormID(),
      $dTemplate,
      $this->readOption('add-do-path', false),
      $this->getTemplateExtension());
  }
  
  public function login(Redirect $redirect) {
    
    if (!$dPost = new XML_Options($redirect->getDocument('post'))) {
      
      $this->dspm('Aucune données d\'authentification !', 'warning');
    }
    else {
      
      $sUser = $dPost->get('name');
      $sPassword = $dPost->get('password');
      
      $bRemember = (bool) $dPost->get('remember');
      
      $user = $this->create('user');
      
      if ($user->authenticate($sUser, $sPassword, $bRemember)) {
        
        $user->load($bRemember);
        Controler::setUser($user);
        
        $sRedirect = $this->readOption('redirect', '/', false);
        $redirect->setPath($sRedirect);
        
        $redirect->addMessage(t('Bienvenue '.$user->getArgument('first-name')), 'success');
      }
    }
    
    return $redirect;
  }
}


