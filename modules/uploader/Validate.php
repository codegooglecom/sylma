<?php

namespace sylma\modules\uploader;
use sylma\core, sylma\storage\fs;

class Validate extends core\module\Domed implements core\stringable {

  protected $aExtensions = array();

  public function setExtensions(array $aExtensions) {

    $this->aExtensions = $aExtensions;
  }

  public function setDirectory($sPath) {

    return parent::setDirectory($this->getManager('fs/editable')->getDirectory($sPath));
  }

  protected function addMessage($sMessage) {

    $msg = $this->getManager(self::PARSER_MANAGER)->getContext('messages');
    $msg->add(array('content' => $sMessage));
  }

  protected function load() {

    $aFile = $_FILES['files'];
    $this->setSettings($aFile);

    preg_match('/\.(\w+)$/', $this->read('name'), $aMatch);
    $sExtension = $aMatch ? $aMatch[1] : '';

    if ($this->read('error', false)) {

      $this->addMessage("File <em>{$this->read('name')}</em> is too large");
    }
    else if (!in_array($sExtension, $this->aExtensions)) {

      $this->addMessage("Extension not allowed in <em>{$this->read('name')}</em>");
      $this->set('error', true);
    }
    else {

      $this->set('extension', $sExtension);
      $this->set('path', $this->read('tmp_name'));
      $this->set('size', ceil($this->read('size') / 1000));

      $file = $this->getManager('fs/root')->getFile($this->read('tmp_name'));
      $sName = uniqid('sylma') . '.' . $sExtension;

      if ($this->moveFile($file, $sName)) {

        $this->set('path', $sName);
        $this->addMessage("File <em>{$this->read('name')}</em> added");
      }
      else {

        $this->set('error', true);
        $this->addMessage("An error occured when adding <em>{$this->read('name')}</em>");
      }


    }
  }

  protected function moveFile(fs\editable\file $file, $sName) {

    return $file->move($this->getDirectory(), $sName);
  }

  public function validate() {

    $this->load();

    return !$this->read('error', false);
  }

  public function read($sPath, $bDebug = true) {

    return parent::read($sPath, $bDebug);
  }

  public function asString() {

    return 1;
  }
}

