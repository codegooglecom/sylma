<?php

namespace sylma\parser;
use sylma\dom, sylma\storage\fs, sylma\parser;

interface action extends dom\domable {

  const NS = 'http://www.sylma.org/parser/action';
  const FILE_DEFAULT_MODE = \Sylma::MODE_READ;

  //const EXPORT_DIRECTORY = '#cache';
  // TODO, unuseful constructor interface
  function __construct(fs\file $file, array $aArguments = array());

  /**
   *
   * @param type $sNamespace
   * @return parser\cached\documented
   */
  function loadParser($sNamespace);

  public function setParentParser(parser\action $parent);
  public function getParentParser($bRoot = false);

  public function setContexts(array $aContexts);
  public function getContexts();

  function useExceptions($mValue = null);
}

