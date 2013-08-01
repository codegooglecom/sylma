<?php

namespace sylma\modules\uploader;
use sylma\core;

class Download extends core\module\Domed {

  protected $sContent = '';
  protected $sName = '';

  public function __construct(array $aArguments) {

    $this->setSettings($aArguments);
    $this->setDirectory($this->getManager(self::FILE_MANAGER)->getDirectory($this->read('directory')));

    $file = $this->getFile($this->read('path'));

    $this->setContent($file->read());
    $this->setName($this->read('name'));
  }

  protected function setContent($sContent) {

    $this->sContent = $sContent;
  }

  protected function getContent() {

    return $this->sContent;
  }

  protected function setName($sName) {

    $this->sName = $sName;
  }

  protected function getName() {

    return $this->sName;
  }

  public function __toString() {

    header('Content-Disposition: attachment; filename="' . $this->getName() . '"');

    return $this->getContent();
  }
}

