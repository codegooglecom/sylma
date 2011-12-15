<?php

namespace sylma\dom;
use \sylma\dom;

require_once('complex.php');

interface element extends complex {
  
  const NS = 'http://www.sylma.org/dom/element';
  
  /**
   * Allow the localization of the element in string messages
   * 
   * @return string The element for message output
   */
  function getPath();
  
  /**
   * @param string $sName The local name of the element
   * @param string $sUri The URI of the element
   * 
   * @return dom\element|null The element getted by its name and optionnaly its URI
   */
  function getByName($sName, $sNamespace = null);
  
  /**
   * @param boolean $bLocal If TRUE, return the local name, if FALSE return the full name (with prefix)
   * @return string The name of the element
   */
  function getName($bLocal = true);
  
  function getAttribute($sName);
  
  /**
   * Return the namespace of the element, or a one contextual to it
   * 
   * @param string $sPrefix If empty, will return current element namespace, else it will lookup corresponding namespace
   * @return string A namespace or empty string
   */
  function getNamespace($sPrefix = '');
  
  function getPrefix();
  
  /**
   * Insert the value given in argument before the $oNext element, if null insert at the end of the children's list
   * @param XML_Document|XML_Element|XML_Attribute|XML_Text|XML_NodeList|string $mValue The value to add to actual content
   * @param XML_Element $oNext The element that will follow the value
   * @return XML_Element|XML_Text|XML_Attribute The object added to content
   */
  function insert($mValue, dom\node $next = null);
}