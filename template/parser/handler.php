<?php

namespace sylma\template\parser;
use sylma\core, sylma\dom, sylma\storage\fs;

interface handler {

  function lookupTemplate($sName, $sNamespace, $sMode, $bRoot = false);
  function getCurrentTemplate();

  function importFile(fs\file $file);
  function importTree(fs\file $file, $sType);
  function createTree($sReflector);
  function isInternal();

  function addToResult($mContent, $bAdd = true, $bFirst = false);

  /**
   * @return Pather
   */
  function getPather();
}

