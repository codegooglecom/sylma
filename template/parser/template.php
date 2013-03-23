<?php

namespace sylma\template\parser;
use sylma\core, sylma\dom;

interface template {

  /**
   * @return \sylma\template\parser\tree
   */
  function getTree();

  function setTree(tree $tree);
  function isCloned();
  function loadElement(dom\element $el);
}

