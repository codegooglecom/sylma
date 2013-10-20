<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser\context, sylma\dom, sylma\storage\fs;

class Less extends CSS {

  protected function addFile(fs\file $file, $bReal = false) {

    $aResult = parent::addFile($file, $bReal);

    if ($file->getExtension() === 'less') {

      $aResult['link']['@rel'] = 'stylesheet/less';
    }

    return $aResult;
  }

  protected function readFile(fs\file $file) {

    $sResult = '';

    switch ($file->getExtension()) {

      case 'css' : $sResult = parent::readFile($file); break;
      case 'less' : $sResult = $this->parse($this->parseLess($file), $file->getParent()); break;
      default :

        $this->launchException('Uknown extension type');
    }

    return $sResult;
  }

  protected function parseLess(fs\file $file) {

    require_once('lessc.php');

    $less = new \lessc;
    //$less->setImportDir($file->getParent()->getRealPath());
//echo (string) $file->getControler()->getDirectory()->getRealPath();
    $less->setImportDir($file->getControler()->getDirectory()->getRealPath());

    return $less->compileFile($file->getRealPath());
  }
}

