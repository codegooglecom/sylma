<?php

namespace sylma\dom;
use \sylma\dom;

require_once('complex.php');
require_once('namespaced.php');

interface element extends complex, namespaced {

  const NS = 'http://www.sylma.org/dom/element';

  /**
   * @param string $sName The local name of the element
   * @param string $sUri The URI of the element
   *
   * @return dom\element|null The element getted by its name and optionnaly its URI
   */
  function getByName($sName, $sNamespace = null);


  /**
   * Alias of \DOMElement::getAttribute() and \DOMElement::getAttributeNS()
   *
   * @param string $sName The local name of the attribute
   * @param string $sNamespace The URI of the element
   *
   * @return string The value of the attribute or empty string if not exists
   */
  function readAttribute($sName, $sNamespace = '', $bDebug = true);

  /**
   * Alias of \DOMElement::getAttributeNode() and \DOMElement::getAttributeNodeNS()
   *
   * @param string $sName The local name of the attribute
   * @param string $sNamespace The URI of the element
   *
   * @return dom\attribute|null The corresponding attribute node
   */
  function loadAttribute($sName, $sNamespace = '', $bDebug = true);

  /**
   * Alias of \DOMElement::setAttribute() and \DOMElement::setAttributeNS()
   *
   * @param string $sName The local name of the attribute
   * @param string $sNamespace The URI of the element
   *
   * @return dom\attribute|null The corresponding attribute node
   */
  function createAttribute($sName, $sValue, $sNamespace = null);

  function testAttribute($sAttribute, $mDefault = null, $sNamespace = '');

  function lookupNamespace($sPrefix);

  /**
   * Insert the value given in argument before the $next element, if null insert at the end of the children's list
   * @param mixed $mValue The value to add to actual content
   * @param dom\node $next The element that will follow the value
   * @return dom\node The node resulting of the insertion
   */
  function insert($mValue, dom\node $next = null);

  /**
   * Allow use of NMTOKENS typed attributes. like html:class
   * @param type $sAttribute
   * @param type $sValue
   */
  function addToken($sAttribute, $sValue, $sNamespace = null, $sSeparator = ' ');
  function removeToken($sAttribute, $sValue, $sNamespace = null, $sSeparator = ' ');

  function isComplex();
  function isElement($sName, $sNamespace = null);

  /**
   * @return dom\handler
   */
  function getHandler();

  function read();
}

