<?php

namespace sylma\parser\action;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\parser;

require_once('dom/domable.php');

interface cached extends dom\domable {

  const CONTEXT_DEFAULT = 'default';

  function __construct(fs\file $file, fs\directory $dir, parser\action $controler, array $aArguments);
  function getParentParser($bRoot = false);

  /**
   *
   * @param type $sNamespace
   * @return parser\cached\documented
   */
  function loadParser($sNamespace);
}
