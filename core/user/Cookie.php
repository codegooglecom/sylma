<?php

namespace sylma\core\user;
use sylma\core;

class Cookie extends core\module\Argumented {

  protected $sContent = '';

  /**
   * Expiration time
   */
  public $iExpiration = 0;

  public function __construct(Manager $manager, core\argument $args) {

    $this->setManager($manager);
    $this->setArguments($args);

    $this->validate();
  }

  public function getContent() {

    return json_decode($this->sContent, true);
  }

  public function save($mContent, $bRemember = false) {

    $sContent = json_encode($mContent);

    if ($bRemember) $iExpiration = time() + $this->readArgument('lifetime/normal'); // 14 days
    else $iExpiration = time() + $this->readArgument('lifetime/short'); // 8 hours

    if ($bRemember) setcookie($this->readArgument('remember/name'), 'true', time() + $this->readArgument('remember/lifetime'), '/');
    else setcookie($this->readArgument('remember/name'), '', 0, '/');

    $sCookie = $this->generate($sContent, $iExpiration);

    if (!setcookie($this->readArgument('name'), $sCookie, $iExpiration, '/') ) {

      //dspm(t('Impossible de créer le cookie, les paramètres de votre navigateur ne l\'autorise peut-être pas.'), 'error');
    }
    else {

      //dspm(t('Cookie enregistré.'), 'success');
    }
  }

  private function generate($sContent, $iExpiration) {

    $sKey = hash_hmac( 'md5', $sContent . $iExpiration, $this->readArgument('secret-key') );
    $sHash = hash_hmac( 'md5', $sContent . $iExpiration, $sKey );

    $sCookie = $sContent . '|' . $iExpiration . '|' . $sHash;

    return $sCookie;
  }

  public function kill() {

    $sName = $this->readArgument('name');

    unset($_COOKIE[$sName]);
    setcookie($sName, '', 0, '/'); // , '/', '/sylma/modules/users/', time() - 42000

    //$this->dspm(xt('Cookie %s détruit', new HTML_Strong($sName)));
  }

  public function validate() {

    $sKey = $this->readArgument('name');
    $sCookie = array_key_exists($sKey, $_COOKIE) ? $_COOKIE[$sKey] : '';

    if ($sCookie) {

      list($sID, $iExpiration, $sHmac) = explode('|', $sCookie);

      if ($iExpiration > time()) {

        $sKey = hash_hmac('md5', $sID . $iExpiration, $this->readArgument('secret-key'));
        $sHash = hash_hmac('md5', $sID . $iExpiration, $sKey);

        if ($sHmac == $sHash) {

          $this->sContent = $sID;
          $this->iExpiration = $iExpiration;
        }
      }
    }
  }
}