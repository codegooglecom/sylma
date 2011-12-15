<?php

namespace sylma\dom;

require_once('node.php');

/**
 * Nodes containing other nodes
 */

interface complex extends node {
  
  /**
   * Evaluation of an xpath expression with a list returned
   * 
   * @param string $sQuery Query to evaluate
   * @param array $aNS Prefixes as keys and related namespaces
   * 
   * @return dom\collection The result of the evaluated expression
   */
  function query($sQuery = '', array $aNS = array(), $bConvert = true);
  
  /**
   * Evaluation of an xpath expression with an element returned
   * 
   * @param string $sQuery Query to evaluate
   * @param array $aNS Prefixes as keys and related namespaces
   * 
   * @return dom\element|null The first element resulting from the XPath query
   */
  function get($sQuery, array $aNS = array());
  
  /**
   * Evaluation of an xpath expression with text returned
   * 
   * @param string $sQuery The query to evaluate
   * @param array $aNS Prefixes as keys and related namespaces
   * 
   * @return string The result of the evaluated expression
   */
  function read($sQuery = '', array $aNS = array());
  
  /**
   * Remove the children then add the mixed values given in argument with {@link add()}
   * @param XML_Document|XML_Element|XML_Attribute|XML_Text|XML_NodeList|string Value(s) to replace actual content
   * @return XML_Element|XML_Text|XML_Attribute The value(s) given in argument
   */
  function set();
  
  /**
   * Add the mixed values given in argument with {@link insert()} at the end of the children's list
   * @param XML_Document|XML_Element|XML_Attribute|XML_Text|XML_NodeList|string Value(s) to add to actual content
   * @return XML_Element|XML_Text|XML_Attribute The last object added to content
   */
  function add();
  
  /**
   * @return dom\collection The list of children nodes
   */
  function getChildren();
  
  /**
   * @return boolean TRUE if element contains other nodes
   */
  function hasChildren();
  
  /**
   * @return dom\node|null The first child node
   */
  function getFirst();
  
  function addElement($sName, $mContent = '', array $aAttributes = array(), $sUri = null);
}