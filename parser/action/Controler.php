<?php

namespace sylma\parser\action;
use \sylma\core, sylma\parser, sylma\dom, sylma\storage\fs;

require_once('parser/action.php');
require_once('core/module/Filed.php');

class Controler extends core\module\Filed {

  const FS_EDITABLE = 'fs/editable';

  public function __construct() {

    //$this->loadDefaultArguments();

    $this->setDirectory(__file__);
    $this->setArguments('controler.yml');
    $this->setNamespace(parser\action::NS);
  }

  public function runAction($sPath, array $aArguments = array()) {

    $action = $this->getAction($sPath, $aArguments);
    return $action->asDOM();
  }

  public function getAction($sPath, array $aArguments = array()) {

    $fs = \Sylma::getControler('fs');
    $file = $fs->getFile($sPath);

    return $this->create('action', array($file, $aArguments));
  }

  public function buildAction(dom\handler $doc, array $aArguments = array(), fs\editable\directory $dir = null, fs\directory $base = null) {

    $fs = $this->getControler(self::FS_EDITABLE);

    if (!$dir) {

      $user = $this->getControler('user');
      $tmp = $fs->getDirectory((string) $user->getDirectory('#tmp'));

      $dir = $tmp->createDirectory();
    }

    $file = $dir->createFile('eml', true);

    $doc->saveFile($file);

    $result = $this->create('action', array($file, $aArguments, $base));

    return $result;
  }

  public function getDirectory($sPath = '', $bDebug = true) {

    return parent::getDirectory($sPath = '', $bDebug = true);
  }
}