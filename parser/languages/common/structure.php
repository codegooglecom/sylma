<?php

namespace sylma\parser\languages\common;

interface structure {

  function setContent(array $aContent);

  /**
   * @return array
   */
  function getContent();

  function isExtracted();
}