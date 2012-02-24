<?php

namespace sylma\dom;
use \sylma\dom;

require_once('complex.php');
require_once('namespaced.php');

interface element extends complex, namespaced {

  const NS = 'http://www.sylma.org/dom/element';

  const COMPARE_SUCCESS = 0;
  const COMPARE_BAD_ELEMENT = 1;
  const COMPARE_BAD_ATTRIBUTE = 2;
  const COMPARE_BAD_CHILD = 3;

  /**
   * @param string $sName The local name of the element
   * @param string $sUri The URI of the element
   *
   * @return dom\element|null The element getted by its name and optionnaly its URI
   */
  function getByName($sName, $sNamespace = null);

  function readAttribute($sName, $sNamespace = '');

  /**
   * Alias of \DOMElement::getAttributeNode() and \DOMElement::getAttributeNodeNS()
   * 
   * @param string $sName The local name of the attribute
   * @param string $sUri The URI of the element
   *
   * @return dom\attribute|null The corresponding attribute node
   */
  function loadAttribute($sName, $sNamespace = '');
  function testAttribute($sAttribute, $mDefault = null, $sNamespace = '');
  function lookupNamespace($sPrefix);

  /**
   * Insert the value given in argument before the $oNext element, if null insert at the end of the children's list
   * @param XML_Document|XML_Element|XML_Attribute|XML_Text|XML_NodeList|string $mValue The value to add to actual content
   * @param XML_Element $oNext The element that will follow the value
   * @return XML_Element|XML_Text|XML_Attribute The object added to content
   */
  function insert($mValue, dom\node $next = null);


}