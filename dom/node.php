<?php

namespace sylma\dom;
use sylma\core;

interface node extends core\tokenable, core\stringable {

  const HANDLER = 66;
  const ELEMENT = \XML_ELEMENT_NODE;
  const ATTRIBUTE = \XML_ATTRIBUTE_NODE;
  const TEXT = \XML_TEXT_NODE;
  const CDATA = \XML_CDATA_SECTION_NODE;
  const COMMENT = \XML_COMMENT_NODE;
  const FRAGMENT = \XML_DOCUMENT_FRAG_NODE;
  const INSTRUCTION = \XML_PI_NODE;

  const COMPARE_SUCCESS = 0;
  const COMPARE_BAD_ELEMENT = 1;
  const COMPARE_BAD_ATTRIBUTE = 2;
  const COMPARE_BAD_CHILD = 3;

  /**
   * @return dom\document The parent document
   */
  function getDocument();
  //function getHandler();
  function getType();
  function getParent();
  function __toString();
}

