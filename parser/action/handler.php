<?php

namespace sylma\parser\action;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\parser\action;

interface handler extends dom\domable {

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

  public function setParentParser(action\handler $parent);
  public function getParentParser($bRoot = false);

  public function setContexts(core\argument $contexts);
  public function getContexts();

  function useExceptions($mValue = null);
}

