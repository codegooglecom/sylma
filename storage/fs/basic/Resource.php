<?php

namespace sylma\storage\fs\basic;
use sylma\core, sylma\dom, sylma\storage\fs;

abstract class Resource implements fs\resource {

  const NS = 'http://www.sylma.org/storage/fs/basic/resource';

  protected $aRights = array();

  protected $sPath = '';
  protected $sName = '';
  protected $sFullPath = '';

  /**
   * Parent directory
   * @var fs\directory
   */
  protected $parent = null;
  protected $controler = null;

  protected $bExist = false;

  public function getControler($sName = '') {

    if ($sName) {

      $controler = $this->getControler();
      $result = $controler->getControler($sName);
    }
    else {

      if ($this->getParent()) {

        $result = $this->getParent()->getControler();
      }
      else {

        $result = $this->controler;
      }
    }

    return $result;
  }

  public function doExist() {

    return $this->bExist;
  }

  protected function getOwner() {

    return $this->aRights['owner'];
  }

  protected function getGroup() {

    return $this->aRights['group'];
  }

  protected function getMode() {

    return $this->aRights['mode'];
  }

  public function getName() {

    return $this->sName;
  }

  protected function getFullPath() {

    return $this->sFullPath;
  }

  public function getParent() {

    return $this->parent;
  }

  public function getUserMode() {

    if (!array_key_exists('user-mode', $this->aRights)) {

      $user = $this->getControler('user');
      $this->aRights['user-mode'] = $user->getMode($this->getOwner(), $this->getGroup(), $this->getMode());
    }

    return $this->aRights['user-mode'];
  }

  /**
   * Read or set if resource accesses has ever been loaded
   *
   * @param bool|null $bSecured if given, the parameter will change to this value
   * @return boolean TRUE if resource has been secured, FALSE elsewhere
   */
  public function getRights() {

    return $this->aRights;
  }

  /**
   * Put all rights into object
   * @param array|DOMElement|null $mRights Rights to use
   * @return array Rights used
   */
  protected function setRights(array $aRights = array()) {

    if (!$aRights) {

      $aRights = array(
        'owner' => $this->getOwner(),
        'group' => $this->getGroup(),
        'mode' => $this->getMode(),
      );
    }

    $this->aRights = $aRights;

    return $aRights;
  }

  public function checkRights($iMode) {

    if (!$this->getControler()->mustSecure() || \Sylma::read('debug/rights')) return true;
    if ($iMode & $this->getUserMode()) return true;

    return false;
  }

  /**
   * Check rights arguments for update in @method updateRights()
   */
  protected function checkRightsArguments($sOwner, $sGroup, $sMode) {

    if ($this->isOwner()) {

      $bOwner = $sOwner !== $this->getOwner();
      $bGroup = $sGroup !== $this->getGroup();
      $bMode  = $sMode !== $this->getMode();

      if ($bOwner || $bGroup || $bMode) {

        $user = \Sylma::getManager('user');

        // Check validity

        if ($bOwner) {

          $this->throwException(t('Cannot change owner'));
        }

        if ($bGroup && !$user->isMember($sGroup)) {

          $this->throwException(sprintf('You have no rights on group %s or it doesn\'t exist', $sGroup));
        }

        $iMode = $user->getMode($sOwner, $sGroup, $sMode);

        if ($bMode && $iMode === null) {

          $this->throwException(t('Arguments are not valid'));
        }

        if ($bMode && !($iMode & MODE_READ)) {

          $this->throwException(t('You cannot remove read access'));
        }

        // all datas are ok or not modified

        return true;
      }

    } else $this->throwException(t('You are not the owner of this resource'));

    return false;
  }

  protected function log($sMessage, $sStatut = Sylma::LOG_STATUT_DEFAULT) {

    $aPath = array(
      '@namespace ' . self::NS,
      '@path ' . $this->getFullPath(),
    );

    return Sylma::log($aPath, $mMessage, $sStatut);
  }

  protected function throwException($sMessage) {

    $aPath = array(
      //'@namespace ' . self::NS,
      '@resource ' . (string) $this,
    );

    $this->getControler()->throwException($sMessage, $aPath, 3);
  }

  public function asString($iMode = 0) {

    return $this->__toString();
  }

  public function __toString() {

    return $this->getFullPath();
  }
}


