<?php

namespace sylma\dom;
use sylma\core, sylma\dom, sylma\storage\fs;

interface handler extends dom\document, dom\complex {

  const NS = 'http://www.sylma.org/dom/handler';
  const STRING_INDENT = 1;
  const STRING_HEAD = 2;
  //const STRING_NOPREFIX = 3;

  function setFile(fs\file $file);
  function getFile();

  /**
   * Register some couples prefix => namespaces that will be used in next queries
   *   Used in @method dom\element\get, @method dom\element\query and @method dom\element\read
   * @param array $aNS The couples prefix => namespaces
   */
  function registerNamespaces(array $aNS = array());

  /**
   * Set the used class for returned child nodes
   * @param core\argument $settings The classes to use for child node
   */
  public function registerClasses(core\argument $settings = null);

  function createElement($sName, $mContent = '', array $aAttributes = array(), $sUri = null);
  function loadText($sContent);
  function setContent($sContent);
}