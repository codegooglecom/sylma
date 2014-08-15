<?php

namespace sylma\modules\html\context;
use sylma\core, sylma\dom, sylma\storage\fs;

class CSS extends Basic implements dom\domable {

  const EXTENSION = 'css';

  protected $aImports = array();

  public function asDOM() {

    $result = null;

    if ($aStyles = $this->loadContent()) {

      $bChildren = false;
      $doc = $this->buildDocument($aStyles, \Sylma::read('namespaces/html'), $bChildren);
      $result = $bChildren ? $doc->getChildren(): $doc->getRoot();
    }

    return $result;
  }

  protected function addFile(fs\file $file, $bReal = false) {

    $aResult = array('link' => array(
      '@href' => $bReal ? '/' . $file->getRealPath() : (string) $file,
      '@type' => 'text/css',
      '@media' => 'all',
      '@rel' => 'stylesheet',
    ));

    return $aResult;
  }

  protected function readFile(fs\file $file) {

    $sResult = $this->parse(parent::readFile($file), $file->getParent());

    return "/** @file $file **/\n\n" . $sResult;
  }

  protected function getCache(array $aFiles, $aContent) {

    if ($aImports = $this->aImports) {

      array_unshift($aContent, implode("\n", $aImports));
    }
    
    return parent::getCache($aFiles, $aContent);
  }

  protected function parse($sValue, fs\directory $dir) {

    return $this->parseImports($this->parseURL($sValue, $dir));
  }

  protected function parseURL($sValue, fs\directory $dir) {

    $fs = \Sylma::getManager('fs');

    $sResult = preg_replace_callback("/url\((?:'|\")?([^\)'\"]+)('|\")?\)/", function($aMatches) use ($fs, $dir) {

      $sMatch = $aMatches[1];

      if (!preg_match('`https?://`', $sMatch)) {

        if ($sMatch{0} === '/') {

          $sURL = $sMatch;
        }
        else {

          $sURL = (string) $fs->getFile($sMatch, $dir);
        }
      }
      else {

        $sURL = $sMatch;
      }

      return "url('$sURL')";

    }, $sValue);

    return $sResult;
  }

  protected function parseImports($sValue) {

    $sImport = '';

    $sValue = preg_replace_callback("/@import [^;]+;\s*/", function($aMatches) use (&$sImport) {

      $sImport = trim($aMatches[0]);

      return '';

    }, $sValue);

    if ($sImport) {

      $this->aImports[] = $sImport;
    }

    return $sValue;
  }

  protected function addText($sValue) {

    return array('style' => array(
      '@type' => 'text/css',
      $sValue,
    ));
  }
}

