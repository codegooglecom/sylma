<?php

namespace sylma\core\user;
use sylma\core;

require_once('core/module/Argumented.php');

class Cookie extends core\module\Argumented {

  protected $sUser = '';

  /**
   * Expiration time
   */
  public $iExpiration = 0;

  // controler :: nothing, @todo

  public function __construct(Controler $controler, core\argument $args) {

    $this->setControler($controler);
    $this->setArguments($args);

    $this->validate();
  }

  public function getUser() {

    return $this->sUser;
  }

  public function save($sUser, $bRemember = false) {

    if ($bRemember) $iExpiration = time() + $this->readArgument('lifetime/normal'); // 14 days
    else $iExpiration = time() + $this->readArgument('lifetime/short'); // 8 hours

    if ($bRemember) setcookie($this->readArgument('remember/name'), 'true', time() + $this->readArgument('remember/lifetime'), '/');
    else setcookie($this->readArgument('remember/name'), '', 0, '/');

    $sCookie = $this->generate($sUser, $iExpiration);

    if (!setcookie($this->readArgument('name'), $sCookie, $iExpiration, '/') ) {

      dspm(t('Impossible de créer le cookie, les paramètres de votre navigateur ne l\'autorise peut-être pas.'), 'error');
    }
    else {

      dspm(t('Cookie enregistré.'), 'success');
    }
  }

  private function generate($sID, $iExpiration) {

    $sKey = hash_hmac( 'md5', $sID . $iExpiration, $this->readArgument('secret-key') );
    $sHash = hash_hmac( 'md5', $sID . $iExpiration, $sKey );

    $sCookie = $sID . '|' . $iExpiration . '|' . $sHash;

    return $sCookie;
  }

  public function kill() {

    $sName = $this->readArgument('name');

    unset($_COOKIE[$sName]);
    setcookie($sName, '', 0, '/'); // , '/', '/sylma/modules/users/', time() - 42000

    //$this->dspm(xt('Cookie %s détruit', new HTML_Strong($sName)));
  }

  public function validate() {

    if ($sCookie = array_val($this->readArgument('name'), $_COOKIE)) {

      list($sID, $iExpiration, $sHmac) = explode('|', $sCookie);

      if ($iExpiration > time()) {

        $sKey = hash_hmac('md5', $sID . $iExpiration, $this->readArgument('secret-key'));
        $sHash = hash_hmac('md5', $sID . $iExpiration, $sKey);

        if ($sHmac == $sHash) {

          $this->sUser = $sID;
          $this->iExpiration = $iExpiration;
        }
      }
    }
  }
}