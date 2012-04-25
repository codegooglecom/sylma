<?php

namespace sylma\storage\fs\basic\security;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('core/module/Namespaced.php');
require_once('storage/fs/security/manager.php');

class Manager extends core\module\Namespaced implements fs\security\manager {

  const FILENAME = 'directory.sml';

  /**
   * Used only for exception reports
   */
  const NS = 'http://www.sylma.org/storage/fs/basic/security';
  const PREFIX = 'fs';

  const USER_CONTROLER = 'user';

  private $document;
  private $directory;

  private $controler;

  protected $bReady = false;

  public function __construct(fs\directory $directory) {

    $this->directory = $directory;
    //$sPath = $directory->getFullPath() . '/' . self::FILENAME;

    $this->setNamespace($this->getControler()->getNamespace(), self::PREFIX);
    $this->loadDocument();
  }

  public function isReady($bValue = null) {

    if (!is_null($bValue)) $this->bReady = $bValue;

    return $this->bReady;
  }

  protected function getControler() {

    return $this->directory->getControler();
  }

  protected function getDocument() {

    return $this->document;
  }

  public function loadDocument() {

    if (\Sylma::getControler(self::USER_CONTROLER, false, false)) {

      require_once(dirname(dirname(__dir__)) . '/resource.php');

      if ($file = $this->directory->getFreeFile(self::FILENAME, fs\resource::DEBUG_NOT)) {

        if ($this->document = $file->getFreeDocument()) {

          $this->document->registerNamespaces($this->getNS());
          $this->isReady(true);
        }
      }
      else {

        $this->isReady(true);
      }
    }

  }

  public function getParent() {

    return $this->oDirectory;
  }

  public function getDirectory() {

//    if (!$this->isReady()) $this->loadDocument();

    $el = null;
    if ($this->getDocument()) $el = $this->getDocument()->getx('self', array(), false);

    return $this->extractRights($el);
  }

  public function getPropagation() {

    $el = null;
    if ($this->getDocument()) $el = $this->getDocument()->getx(self::PREFIX . ':propagate', $this->getNS(), false);

    return $this->extractRights($el);
  }

  public function getFile($sName) {

//    if (!$this->isReady()) $this->loadDocument();

    $el = null;
    $spName = $this->escape($sName);

    if ($this->getDocument()) $el = $this->getDocument()->getx(self::PREFIX . ":file[@name=$spName]", $this->getNS(), false);

    return $this->extractRights($el);
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

    if ($el && ($el = $el->getByName('security', self::NS))) {

      $sOwner = $el->readByName('owner', self::NS);
      $sGroup = $el->readByName('group', self::NS);
      $sMode = $el->readByName('mode', self::NS);

      $user = \Sylma::getControler('user');
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

    return $aResult;
  }
}

