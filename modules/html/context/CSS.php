<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\parser\context, sylma\dom, sylma\storage\fs;

class CSS extends context\Basic implements dom\domable {

  public function asDOM() {

    $result = null;
    $aStyles = array();
    $aFiles = array();

    foreach ($this->query() as $mValue) {

      if ($mValue instanceof fs\file) {

        $sFile = (string) $mValue;

        if (!array_key_exists($sFile, $aFiles)) {

          $aStyle = array('link' => array(
            '@href' => $sFile,
            '@type' => 'text/css',
            '@media' => 'all',
          ));

          switch ($mValue->getExtension()) {

            case 'css' :

              $aStyle['link']['@rel'] = 'stylesheet';
              break;

            case 'less' :

              $aStyle['link']['@rel'] = 'stylesheet/less';
              break;

            default :

              $this->launchException('Unknown css type', get_defined_vars());
          }

          $aFiles[$sFile] = true;
        }
        else {

          $aStyle = array();
        }
      }
      else {

        $aStyle = array('style' => array(
          '@type' => 'text/css',
          $mValue,
        ));
      }

      if ($aStyle) $aStyles[] = $aStyle;
    }

    if ($aStyles) {

      $bChildren = false;
      $doc = $this->buildDocument($aStyles, \Sylma::read('namespaces/html'), $bChildren);
      $result = $bChildren ? $doc->getChildren(): $doc->getRoot();
    }

    return $result;
  }
}

