<?php

namespace sylma\storage\fs\basic\security;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

class Manager extends core\module\Namespaced implements fs\security\manager {

  const FILENAME = 'directory.sml';
  const PREFIX = 'fs';

  const USER_CONTROLER = 'user';

  private $document;
  private $parent;

  public function __construct(fs\directory $dir) {

    $this->setParent($dir);
    //$sPath = $directory->getFullPath() . '/' . self::FILENAME;

    $this->setNamespace(self::NS, self::PREFIX);
    $this->loadDocument();
  }

  protected function getParent() {

    return $this->parent;
  }

  protected function setParent($dir) {

    $this->parent = $dir;
  }

  protected function getControler() {

    return $this->parent->getControler();
  }

  protected function getDocument() {

    return $this->document;
  }

  public function loadDocument() {

    require_once(dirname(dirname(__dir__)) . '/resource.php');

    if ($file = $this->getParent()->getFreeFile(self::FILENAME, fs\resource::DEBUG_NOT)) {

      $this->document = $file->getFreeDocument($this->getNS());
    }
  }

  public function getDirectory() {

    $el = null;

    if ($this->getDocument()) {

      $el = $this->getDocument()->getx(self::PREFIX . ":self", array(), false);
    }

    return $this->extractRights($el);
  }

  public function getPropagation() {

    $el = null;
    if ($this->getDocument()) $el = $this->getDocument()->getx(self::PREFIX . ':propagate', $this->getNS(), false);

    return $this->extractRights($el);
  }

  public function getFile($sName) {

    $el = null;
    $spName = $this->escape($sName);

    if ($this->getDocument()) {

      $el = $this->getDocument()->getx(self::PREFIX . ":file[@name=$spName]", array(), false);
    }

    return $this->extractRights($el);
  }

  protected function escape($sValue) {

    return "'".addslashes($sValue)."'";
  }

  /*
   * Extract and check validity of security datas in element
   *
   * @param* dom\element $el The element to extract the values from
   * @return an array of validated security parameters
   * - @key user-mode will indicate the user's current rights on the file
   **/
  protected function extractRights(dom\element $el = null) {

    $aResult = array();
//$bTest=false;if ($el && $el->readAttribute('name', false, false) == 'file.txt') $bTest = true;

    if ($el && ($el = $el->getByName('security', self::NS))) {

      $sOwner = $el->readByName('owner', self::NS);
      $sGroup = $el->readByName('group', self::NS);
      $sMode = $el->readByName('mode', self::NS);

      $user = $this->getControler()->getControler('user');
      $iMode = $user->getMode($sOwner, $sGroup, $sMode);

      if ($iMode !== null) {

        $aResult = array(
          'owner' => $sOwner,
          'group' => $sGroup,
          'mode' => $sMode,
          'user-mode' => $iMode
        );
      }
    }
 //if ($bTest) print_r($aResult);
    return $aResult;
  }
}

