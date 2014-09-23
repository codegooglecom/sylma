<?php
namespace sylma\modules\users;
use sylma\core;

class Module extends core\module\Domed {

  const NS = 'http://www.sylma.org/modules/users';
  const GROUP_AUTH = 'user';

  public function __construct(core\argument $args, core\argument $post, core\argument $contexts) {

    $this->setDirectory(__file__);

    $this->aContext = array(
      'arguments' => $args,
      'post' => $post,
      'contexts' => $contexts,
    );

    $this->setSettings(include('core/user/settings.xml.php'));
  }

  /**
   * @return string
   */
  public function login() {

    $sResult = '';
    $aGroups = array();

    $contexts = $this->aContext['contexts'];

    $this->loadDefaultArguments();

    $post = $this->aContext['post'];

    $doc = $this->getScript('login/default/check', array(), $contexts->query() , $post->query());

    if (!$doc->isEmpty()) {

      list($sID, $sPassword, $sGroups) = explode(' ', $doc->readx());
      $aGroups = array_filter(explode(',', $sGroups));

      if ($sPassword == crypt($post->read('password'), $sPassword)) {

        $sResult = $sID;
      }
    }

    $msg = $contexts->get('messages');

    sleep($this->read('login/delay'));

    if (!$sResult) {

      $msg->add(array('content' => $this->translate('Authentication failed'), 'arguments' => array('error' => true)));
    }
    else {

      $aGroups[] = self::GROUP_AUTH;

      $bRemember = true; //(bool) $post->get('remember', false);
      $user = $this->getManager('user');
      $user->authenticate($post->read('name'), $sResult, $aGroups);

      \Sylma::setManager('user', $user);
      $user->load($bRemember);

      $msg->add(array('content' => $this->translate('Authentication successed')));
    }

    return $sResult;
  }
}


