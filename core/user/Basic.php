<?php

namespace sylma\core\user;
use \sylma\core, sylma\storage\fs, sylma\core\functions;

require_once('core/module/Argumented.php');
require_once(dirname(__dir__) . '/user.php');

class Basic extends core\module\Argumented implements core\user {

  const NS = 'http://www.sylma.org/core/user';
  const PUBLIC_ALIAS = 'anonymouse';

  private $sUser = '';
  private $bValid = false;
  private $sSID = ''; // session ID

  /**
   * Used by @method needProfile()
   */
  private $bProfil = false;
  private $bPrivate = false;

  private $aGroups = array();
  private $cookie;

  private $directory;

  // controler :: create, getDocument, createArgument

  public function __construct(Controler $controler, $sName = '', array $aGroups = array(), array $aOptions = array()) {

    $this->setName($sName);
    $this->setNamespace(self::NS);
    $this->setControler($controler);

    $this->setArguments($controler->getArguments());

    /*if ($aOptions) {

    $options = new Arguments($aOptions);
    $this->setOptions($options->getOptions($this->createNode('user')));
    }*/

    $this->setGroups($aGroups);
  }

  public function getArgument($sPath, $mDefault = null, $bDebug = false) {

    return parent::getArgument($sPath, $mDefault, $bDebug);
  }

  protected function setName($sName) {

    return $this->sName = $sName;
  }

  public function getName() {

    return $this->sName;
  }

  public function authenticate($sUser, $sPassword) {

    $sResult = null;

    //$file = $this->getFile();
    //$this->setOptions(new XML_Document(Controler::getSettings()->get("module[@name='users']")));

    if (!$sUser || !$sPassword) {

      $this->throwException(t('Cannot authenticate, bad datas !'));
    }

    $fs = $this->getControler('fs');
    $users = $fs->getFreeFile($this->readArgument('users/path'));
    $users = $users->getFreeDocument($this->getNS());

    if ($users->isEmpty()) {

      $this->throwException(t('No active user'));
    }

    require_once('core/functions/Text.php');
    list($spUser, $spPassword) = functions\text\addQuote(array($sUser, sha1($sPassword)));

    if (!$eUser = $users->getx("//user[@name = $spUser and @password = $spPassword]", array(), false)) {

      $this->throwException('Bad authentication');
    }

    // Authentication successed !

    $sResult = $this->setName($sUser);
    $this->isValid(true);

    return $sResult;
  }

  protected function isValid($bValid = null) {

    if ($bValid !== null) $this->bValid = $bValid;
    return $this->bValid;
  }

  public function logout() {

    if ($this->getCookie()) $this->getCookie()->kill();

    $_SESSION = array();
    // setcookie(session_name(), '', time()-42000, '/');

    return $this->getControler()->create('redirect', array('/'));
  }

  /**
   * Load the user, from either cookie, session or profil if authentication has been done
   */
  public function load($bRemember = false) {

    $this->loadCookie();

    if ($this->isValid() && $this->getName()) {

      // just authenticated via @method authenticate()

      //$this->loadProfile();
      $this->setPrivate();
      if ($this->getCookie()) $this->getCookie()->save($this->getName(), $bRemember);
    }
    else if (!$this->loadSession()) {

      // no session

      if ($this->getCookie() && ($sUser = $this->getCookie()->getUser())) {

        // has cookie

        $this->setName($sUser);
        $this->setPrivate();

        $this->bProfil = true;
      }
      else {

        $controler = $this->getControler();

        // no cookie, select the default user

        $server = $controler->getArgument('server');

        if ($_SERVER['REMOTE_ADDR'] == $server->read('ip')) $options = $server;
        else $options = $controler->getArgument(self::PUBLIC_ALIAS);

        $this->setName($options->read('name'));
        $this->aGroups = $options->query('groups');
        $this->setArguments($options->get('arguments'));
      }
    }
  }

  protected function setDirectory(fs\directory $dir) {

    $this->directory = $dir;
  }

  public function getDirectory($sPath = '', $bDebug = true) {

    $result = null;

    if ($sPath) {

      $result = $this->getDirectory()->getDistantDirectory($sPath, $bDebug);
    }
    else if (!$result = $this->directory) {

      if (!$this->getName()) {

        $this->throwException(t('Cannot get directory of an unnamed user'));
      }

      $fs = $this->getControler('fs');
      $dir = $fs->getDirectory($this->readArgument('path') . '/' . $this->getName());

      $this->setDirectory($dir);
      $result = $this->getDirectory();
    }

    return $result;
  }

  /**
   * Define if the profil need to be load by main Controler.
   * Used when session is lost, profil cannot be loaded before filesys
   */
  public function needProfile() {

    return $this->bProfil;
  }

  /*
  public function loadProfile() {

    $sProfil = $this->readArgument('profil');
    $dProfil = $this->getDocument($sProfil);

    if (!$dProfil || $dProfil->isEmpty()) {

      $this->log($this->readArgument('path') . '/' . $this->getName());
      $this->log(sprintf('Cannot load profile in @file %s', $this->getDirectory().'/'.$sProfil));
    }
    else {

      $dProfil->addNode('full-name', $dProfil->readByName('first-name') . ' ' . $dProfil->readByName('last-name'));
      //$this->setOptions($dProfil);
    }

    $this->loadGroups();
    $this->saveSession();
  }
  */

  protected function loadGroups() {

    $aGroups = $this->getArgument('authenticated/groups')->query();
    $sUser = $this->getName();

    $oAllGroups = $this->getDocument('/config/groups.xml', MODE_EXECUTION);

    if ($oAllGroups) {

      $oGroups = $oAllGroups->query("group[@owner = '$sUser']/@name | group[member = '$sUser']/@name");
      foreach ($oGroups as $oAttribute) $aGroups[] = $oAttribute->getValue();
    }

    if (Controler::isAdmin()) $aGroups = array_merge($aGroups, $this->getArgument('root/groups')->query());

    $this->setGroups(array_unique($aGroups));
  }

  public function getCookie() {

    return $this->cookie;
  }

  protected function loadCookie() {

    $this->cookie = $this->getControler()->create('cookie', array($this->getControler(), $this->getArgument('cookies')));
  }

  protected function loadSession() {

    $sKey = $this->readArgument('session/name');
    $sSession = array_key_exists($sKey, $_SESSION) ? $_SESSION[$sKey] : '';

    if ($sSession) {

      $aSession = unserialize($sSession);

      $this->setName($aSession[0]);
      $this->setGroups($aSession[1]);
      //if ($aSession[2]) $this->setOptions($aSession[2]);
    }

    return $this->getName();
  }

  protected function saveSession() {

    $options = null;
    //if ($this->getOptions()) $options = $this->getOptions()->getDocument();

    $_SESSION[$this->readArgument('session/name')] = serialize(array($this->getName(), $this->getGroups(), $options));
  }

  /*** Groups ***/

  protected function setGroups(array $aGroups) {

    $this->aGroups = $aGroups;
  }

  protected function getGroups() {

    return $this->aGroups;
  }

  public function isMember($mGroup) {

    if (is_array($mGroup)) {

      foreach ($mGroup as $sGroup) if (!$this->isMember($sGroup)) return false;
      return true;
    }
    else {

      return in_array($mGroup, $this->aGroups, true);
    }

    return false;
  }

  public function getMode($sOwner, $sGroup, $sMode, $sSource = '[undefined]') {

    $sMode = (string) $sMode;

    // Validity control of the arguments

    if (!$sOwner) {

      $this->throwException(sprintf('Owner not defined in %s', $sSource));
    }

    if (strlen($sMode) < 3 || !is_numeric($sMode)) {

      $this->throwException(sprintf('Invalid mode in %s', $sSource));
    }

    if (!strlen($sGroup)) {

      $this->throwException(sprintf('Group not defined in %s', $sSource));
    }

    $iOwner = intval($sMode{0});
    $iGroup = intval($sMode{1});
    $iPublic = intval($sMode{2});

    if ($iOwner > 7 || $iGroup > 7 || $iPublic > 7) {

      $this->throwException(sprintf('Invalid mode in %s', $sSource));
    }

    // everything is ok
    $iMode = $iPublic;

    if ($sOwner == $this->getName()) $iMode |= $iOwner;
    if ($this->isMember($sGroup)) $iMode |= $iGroup;

    return $iMode;
  }

  protected function setPrivate($bValue = true) {

    $this->bPrivate = $bValue;
  }

  public function isPublic() {

    return !$this->isPrivate();
  }

  public function isPrivate() {

    return $this->bPrivate;
  }

  public function asArgument() {

    $sName = $this->getName() . ' [' . implode(', ', $this->getGroups()) . ']';

    return $controler->createArgument(array(
      'a' => array(
        '@href' => $this->readArgument('edit') . '/' . $this->getName(),
        '' => $sName,
      ),
    ), \Sylma::read('namespaces/html'));
  }

  public function __toString() {

    return $this->getName();
  }
}
