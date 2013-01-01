<?php

namespace sylma\parser\action;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\parser;

/**
 * Dynamic (on-run) parsers are managed, by default, by the root action.
 * There parsers must be available with @method loadParser().
 * Tree of action is accessible with @method getParentParser()
 */
interface cached extends dom\domable {

  const CONTEXT_DEFAULT = 'default';

  function __construct(fs\file $file, fs\directory $dir, parser\action $controler, array $aContexts, array $aArguments = array(), array $aControlers = array());

  function getParentParser($bRoot = false);

  /**
   * Load dynamic parser. Generally used with root action.
   * @param type $sNamespace
   * @return parser\cached\documented
   */
  function loadParser($sNamespace);
}
