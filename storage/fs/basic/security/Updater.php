<?php

namespace sylma\storage\fs\basic\security;
use \sylma\core, \sylma\dom, \sylma\storage\fs;

require_once('Manager.php');
require_once('storage/fs/security/updater.php');

class Updater extends Manager implements fs\security\updater {
  
  public function build() {

    if ($this->getDocument()) dspm(xt('Le fichier de sécurité dans %s existe déjà', $this->getParent()), 'file/error');
    else {

      $doc = new \XML_Document;
      $doc->addNode('directory', null, null, $this->getControler()->getNamespace());

      $this->getParent()->addFreeDocument(self::NS, $doc);

      $this->doc = $doc;
    }
  }

  public function updateFileName($sName, $sNewName) {

    $bResult = null;

    if ($nFile = $this->getFile($sName)) {

      $nFile->setAttribute('name', $sNewName);
      $bResult = $this->save();
    }

    return $bResult;
  }

  public function updateFile($sName, $sOwner, $sGroup, $sMode) {

    if ($nFile = $this->getFile($sName)) $nFile->remove();
    else if (!$this->getDocument()) $this->build();

    $nFile = new XML_Element('file',
      new XML_Element('ls:security', array(
          new XML_Element('ls:owner', $sOwner, null, SYLMA_NS_SECURITY),
          new XML_Element('ls:group', $sGroup, null, SYLMA_NS_SECURITY),
          new XML_Element('ls:mode', $sMode, null, SYLMA_NS_SECURITY)),
        null, SYLMA_NS_SECURITY),
      array('name' => $sName), SYLMA_NS_DIRECTORY);

    $this->getDocument()->add($nFile);

    return $this->save();
  }

  public function deleteFile($sName) {

    $bResult = null;

    if ($nFile = $this->getFile($sName)) {

      $nFile->remove(); // TODO check if empty
      $bResult = $this->save();
    }

    return $bResult;
  }

  public function updateDirectory($sOwner, $sGroup, $sMode) {

    if ($nDirectory = $this->getDirectory()) $nDirectory->remove();
    else if (!$this->getDocument()) $this->build();

    $nDirectory = new XML_Element('self',
      new XML_Element('ls:security', array(
          new XML_Element('ls:owner', $sOwner, null, SYLMA_NS_SECURITY),
          new XML_Element('ls:group', $sGroup, null, SYLMA_NS_SECURITY),
          new XML_Element('ls:mode', $sMode, null, SYLMA_NS_SECURITY)),
        null, SYLMA_NS_SECURITY), SYLMA_NS_DIRECTORY);

    $this->getDocument()->add($nDirectory);

    return $this->save();
  }

  private function save() {

    if ($this->getDocument()) {

      if ($this->getDocument()->getRoot()->hasChildren()) return $this->getDocument()->saveFree($this->getParent(), SYLMA_SECURITY_FILE);
      else unlink(MAIN_DIRECTORY.$this->getParent()->getFullPath().'/'.SYLMA_SECURITY_FILE);

    } else return null;
  }
}

