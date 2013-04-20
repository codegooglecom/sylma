<?php

namespace sylma\parser\action;
use sylma\core, sylma\dom, sylma\storage\fs, sylma\parser\action;

/**
 * Cached actions are the public part of the parser action. . It means they are They are managed, by default, by the root action.
 * There parsers are available with @method loadParser().
 * The parent actions are accessible with @method getParentParser().
 * Both methods are used in conjunction to get wanted parser
 */
interface cached {

  const CONTEXT_DEFAULT = 'default';

  /**
   * @param $file File pointing to DOM document
   * @param $dir Base directory
   * @param $manager Handler
   * @param array $aContexts Render contexts (body/js/css)
   * @param array $aArguments Arguments
   * @param array $aManagers
   */
  function __construct(fs\file $file, fs\directory $dir, action\handler $manager, core\argument $contexts, array $aArguments = array(), array $aManagers = array());

  /**
   * Get parent or root parser. If none return itself
   * This second usage explains the need of making it public
   *
   * @param bool $bRoot If TRUE, get root parser, else get first parent
   * @return parser\action
   */
  function getParentParser($bRoot = false);

  /**
   * Load dynamic parser. Generally called on root action.
   *
   * @param string $sNamespace
   * @return parser\cached\documented
   */
  function loadParser($sNamespace);
}
