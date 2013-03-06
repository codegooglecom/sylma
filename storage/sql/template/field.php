<?php

namespace sylma\storage\sql\template;
use sylma\core, sylma\template, sylma\schema;

interface field extends schema\parser\element, template\parser\tree {

  function reflectApplyPath(array $aPath);

  function getParent();
  function getQuery();
  function getVar();
}

