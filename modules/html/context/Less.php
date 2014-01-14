<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser\context, sylma\dom, sylma\storage\fs;

class Less extends CSS {

  public function __construct(array $aArray = array(), core\argument $fusion = null, core\window\context $js = null) {

    parent::__construct($aArray, $fusion);
    $this->js = $js; // TODO : change to context getter
  }

  protected function addFile(fs\file $file, $bReal = false) {

    $aResult = parent::addFile($file, $bReal);

    if ($file->getExtension() === 'less') {

      $this->js->add(\Sylma::getManager('fs')->getFile('/#sylma/modules/html/medias/less.js'));
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

    $sResult = '';
    require_once('lessc.php');

    $less = new \lessc;
    //$less->setImportDir($file->getParent()->getRealPath());
//echo (string) $file->getControler()->getDirectory()->getRealPath();
    $less->setImportDir($file->getControler()->getDirectory()->getRealPath());

    try {

      $sResult = $less->compileFile($file->getRealPath());
    }
    catch (\Exception $e) {

      throw \Sylma::loadException($e);
    }

    return $sResult;
  }
}

