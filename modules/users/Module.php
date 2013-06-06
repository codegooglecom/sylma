<?php
namespace sylma\modules\users;
use sylma\core;

class Module extends core\module\Domed {

  const NS = 'http://www.sylma.org/modules/users';

  public function __construct() {

    $this->setDirectory(__file__);
    //$this->setArguments('module.yml');
  }

  /**
   *
   * @return boolean
   */
  public function _login() {

    $redirect = $this->getControler('redirect');

    if (!$post = $redirect->getArgument('post')) {

      $this->dspm('Aucune données d\'authentification !', 'warning');
    }
    else {

      $sUser = $post->read('name');
      $sPassword = $post->read('password');

      $bRemember = true; //(bool) $post->get('remember', false);

      $user = $this->getControler('user');

      if ($user->authenticate($sUser, $sPassword, $bRemember)) {

        \Sylma::setControler('user', $user);
        $user->load($bRemember);

        $sRedirect = $this->readArgument('redirect');
        if ($sRedirect) $redirect->setPath($sRedirect);
      }
    }

    return $redirect;
  }

  /**
   * @return string
   */
  public function login() {

    $sResult = '';

    $contexts = $this->getActionContexts();
    $contexts->set('internal', $this->createArgument(array()));
    
    $this->loadDefaultArguments();

    $post = $this->createArgument($this->getManager('init')->loadPOST());

    $doc = $this->getScript('login/default/check', array(), $contexts->query() , $post->query());

    $msg = $contexts->get('messages');

    if (!$doc->isEmpty()) {

      list($sID, $sPassword) = explode(' ', $doc->readx());
      if ($sPassword == crypt($post->read('password'), $sPassword)) {

        $sResult = $sID;
      }
    }

    if (!$sResult) {

      $msg->add($this->translate('Authentication failed'));
    }
    else {

      $msg->add($this->translate('Authentication successed'));
    }

    return $sResult;
  }
}


