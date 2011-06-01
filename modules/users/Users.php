<?php

class Users extends DBX_Module {
  
  const MODULE_NAME = 'users';
  
  const LOGIN_SCHEMA = 'login.xsd';
  const LOGIN_TEMPLATE = 'form/index.xsl';
  
  const PROFIL_SCHEMA = 'profil.xsd';
  
  const OPTIONS_ACTION = 'options';
  const OPTION_USERS = 'users';
  const OPTION_MODULE = 'modules/users';
  
  public function __construct(XML_Directory $directory = null, XML_Document $dSchema = null, XML_Document $dOptions = null) {
    
    $this->setName(self::MODULE_NAME);
    
    $this->setArguments(Sylma::get(self::OPTION_MODULE));
    $this->getArguments()->merge(Sylma::get(self::OPTION_USERS));
    
    if (!$directory) $directory = $this->setDirectory(__file__);
    if (!$dOptions) $dOptions = $this->runAction(self::OPTIONS_ACTION);
    
    parent::__construct($directory, $dSchema, $dOptions);
  }
  
  public function editProfil(Redirect $redirect) {
    
    $this->setSchema(self::PROFIL_SCHEMA);
  }
  
  public function connection(Redirect $redirect) {
    
    $this->setSchema($this->getDocument(self::LOGIN_SCHEMA, Sylma::MODE_EXECUTE));
    $dTemplate = $this->getTemplate(self::LOGIN_TEMPLATE);
    
    if (isset($_SERVER['HTTPS'])) $dTemplate->setParameter('https', $_SERVER['HTTPS']);
    
    // dspf($this->getOptions()->getDocument());
    // dspf($this->getTemplateExtension());
    
    return $this->add(
      $redirect,
      $this->setFormID(),
      $dTemplate,
      $this->readOption('add-do-path', false),
      $this->getTemplateExtension());
  }
  
  public function login(Redirect $redirect) {
    
    if (!$post = new Options($redirect->getDocument('post'))) {
      
      $this->dspm('Aucune données d\'authentification !', 'warning');
    }
    else {
      
      $this->setSchema(self::LOGIN_SCHEMA);
      
      $sUser = $post->read('name');
      $sPassword = $post->read('password');
      
      $bRemember = (bool) $post->get('remember', false);
      
      $user = $this->create('user');
      
      if ($user->authenticate($sUser, $sPassword, $bRemember)) {
        
        Controler::setUser($user);
        $user->load($bRemember);
        
        $sRedirect = $this->readOption('redirect', '/', false);
        $redirect->setPath($sRedirect);
        
        //$redirect->addMessage(xt('Bienvenue %s', $user->getArgument('first-name')), 'success');
      }
    }
    
    return $redirect;
  }
}


