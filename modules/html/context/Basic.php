<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\core\window, sylma\dom, sylma\storage\fs, sylma\modules\html;

class Basic extends core\argument\Readable implements window\context {

  const EXTENSION = 'tmp';

  protected $parent;
  protected $fusion;

  function __construct(array $aArray = array(), core\argument $fusion = null) {

    parent::__construct($aArray);

    if ($fusion) $this->setFusion($fusion);
  }

  protected function setFusion(core\argument $args) {

    $this->fusion = $args;
  }

  protected function getFusion($bDebug = true) {

    return $this->fusion;
  }

  public function add($mValue, $bRef = false) {

    if (is_array($mValue)) {

      foreach ($mValue as $mItem) {

        parent::add($mItem);
      }
    }
    else {

     parent::add($mValue);
    }
  }

  protected function loadContent() {

    $aFiles = $aResultFiles = $aResultTexts = array();
    $bFusion = $this->getFusion() ? $this->getFusion()->read('enable') : false;

    foreach ($this->query() as $mValue) {

      if ($mValue instanceof fs\file) {

        $sFile = (string) $mValue;

        if (!array_key_exists($sFile, $aFiles)) {

          $aFiles[$sFile] = $mValue;

          if ($bFusion) {

            $aResultFiles[] = $this->readFile($mValue);
          }
          else {

            $aResultFiles[] = $this->addFile($mValue);
          }
        }
      }
      else {

        $aResultTexts[] = $this->addText($mValue);
      }
    }

    if ($bFusion && $aFiles) {

      $aResultFiles = $this->getCache($aFiles, $aResultFiles);
    }

    return array_filter(array($aResultFiles, $aResultTexts));
  }

  protected function readFile(fs\file $file) {

    return $file->read();
  }

  /**
   * @return array
   */
  protected function getCache(array $aFiles, $aContent) {

    $sName = crc32(implode('', array_keys($aFiles))) . '.' . static::EXTENSION;

    $fs = \Sylma::getManager('fs/tmp');
    $cache = $fs->getFile($sName, null, false);
    $dir = $fs->getDirectory();
    $bUpdate = false;

    if ($cache && \Sylma::isAdmin()) {

      $bUpdate = $this->checkUpdate($cache, $aFiles);
    }

    if (!$cache || $bUpdate) {

      $cache = $dir->createFile($sName);
      $cache->saveText(implode("\n\n", $aContent));
    }

    return $this->addFile($cache, true);
  }

  protected function checkUpdate(fs\file $cache, array $aFiles) {

    $bResult = $this->getFusion() ? $this->getFusion()->read('update') : false;

    if (!$bResult) {

      $update = $cache->getLastChange();

      foreach ($aFiles as $file) {

        if ($file->getLastChange() > $update) {

          $bResult = true;
          break;
        }
      }
    }

    return $bResult;
  }

  /**
   * @return array
   */
  protected function addFile(fs\file $file) {

    $this->launchException('Must be overrided');
  }

  protected function addText($sValue) {

    $this->launchException('Must be overrided');
  }

  public function loadArray() {

    $aResult = array();
    $aAction = $this->query();

    if (count($aAction) == 1 && is_array(current($aAction))) {

      $aResult = current($aAction);
    }
    else {

      $aResult = $aAction;
    }

    return $aResult;
  }

  protected function createDocument($sElement = '') {

    return \Sylma::getManager('dom')->createDocument($sElement);
  }

  public function asDOM() {

    $result = $this->asObject();

    if ($result && !$result instanceof dom\handler) {

      $result = $this->createDocument($result);
    }

    return $result;
  }

  public function asObject() {

    $result = null;
    $aArguments = $this->loadArray();

    if (count($aArguments) > 1) {

      $this->throwException('Multiple values when object expected');
    }
    else if ($aArguments) {

      $result = reset($aArguments);
    }

    return $result;
  }

  public function asString() {

    //$this->normalize();
    $aResult = $this->loadArray();

    return (string) implode('', $aResult);
  }
}
