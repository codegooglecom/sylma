<?php
namespace sylma\modules\users;
use sylma\core;

class Module extends core\module\Filed {

  const NS = 'http://www.sylma.org/modules/users';

  public function __construct() {

    $this->setDirectory(__file__);
    $this->setArguments('module.yml');
  }

  /**
   *
   * @return boolean
   */
  public function login() {

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
}


