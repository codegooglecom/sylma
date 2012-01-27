<?php

namespace sylma\dom;
use sylma\core;

require_once('core/tokenable.php');

interface node extends core\tokenable {

  const HANDLER = 666;
  const ELEMENT = \XML_ELEMENT_NODE;
  const ATTRIBUTE = \XML_ATTRIBUTE_NODE;
  const TEXT = \XML_TEXT_NODE;
  const CDATA = \XML_CDATA_SECTION_NODE;
  const COMMENT = \XML_COMMENT_NODE;
  const FRAGMENT = \XML_DOCUMENT_FRAG_NODE;

  /**
   * @return dom\document The parent document
   */
  function getDocument();
  function getType();
  function getParent();
  function asString($bFormat = false);
  function __toString();
}