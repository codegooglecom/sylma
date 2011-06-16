<?php
/**
 * This file contains the code for querying and manipulating eXist XML:DB via SOAP interface.
 *
 * PHP version 5
 *
 * @category   Web Services
 * @package    SOAP
 * @author     Oscar Celma <ocelma@iua.upf.edu> Original Author
 * @author     François-Xavier Prunayre <fx.prunayre@gmail.com> Author
 * @license    GPL
 * @link       http://query-exist.sourceforge.net
 */

/**
 * eXist Class
 *
 * This class is the main interface for making soap requests to eXist XML:DB.
 *
 * basic usage:<code>
 *   $db = new eXist();
 *   # Connect
 *   $db->connect() or die ($db->getError());
 *
 *   $query = 'for $line in //SPEECH[SPEAKER = "BERNARDO"]/LINE return $line';
 *
 *   print "<p><b>XQuery:</b></p><pre>$query</pre>";
 *   $result = $db->xquery($query) or die ($db->getError());
 *   # Get results
 *   $hits = $result["HITS"];
 *   $queryTime = $result["QUERY_TIME"];
 *   $collections = $result["COLLECTIONS"];
 *
 *   print "<p>found $hits hits in $queryTime ms.</p>";
 *
 *   # Show results
 *   print "<p><b>Result of the XQuery:</b></p>";
 *   print "<pre>";
 *   if ( !empty($result["XML"]) )
 *	  foreach ( $result["XML"] as $xml)
 *        	print htmlspecialchars($xml) . "<br />";
 *   print "</pre>";</code>
 *
 * @access   public
 */

class eXist
{ 
  protected $_wsdl = "";
  protected $_user = "guest";
  protected $_password = "guest";
  
  protected $_soapClient = null;
  protected $_session = null;
  protected $_error = "";
 
  protected $_debug = false;
  protected $_highlight = false;
  
  public function __construct($user="guest", $password="guest", $wsdl="http://localhost:8080/exist/services/Query?wsdl")
  {
	  $this->_user = $user;
	  $this->_password = $password;
	  $this->_wsdl = $wsdl;

	  $this->_soapClient = new SoapClient ($this->_wsdl);
  }
  
  public function __destruct() {
  }
  
  public function disconnect()
  {
	  if ( $this->getError() )
	  	return false;
	  try
	  {
		  //$this->_soapClient->disconnect($this->_session);
		  $parameters = array('sessionId' => $this->_session);
		  $this->_session = $this->soapCall('disconnect', $parameters);
	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
	  }
  }
  
  public function connect()
  {
	  if ( $this->getError() )
	  	return false;
	  try
	  {
		  $parameters = array('userId' => $this->_user, 'password' => $this->_password );
		  $this->_session = $this->soapCall('connect', $parameters);
	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
		  return false;
	  }
	  return true;
  }

  public function getError()
  {
	if ( $this->_error != "" )
	  	return $this->_error;
	return false;
  }
  
  public function xquery($query)
  {
	  //if ( $this->getError() )
	  	//return false;
	  if ( empty($query) )
	  {
		  $this->_error = "ERROR: Query is empty!";
		  return false;
	  }
	  try
	  {
      $this->setError();
		  // encode only to base64 if php version lesser than 5.1
		  // patch by Bastian Gorke bg/at\ipunkt/dot\biz
		  if (!version_compare(PHP_VERSION, '5.1.0', 'ge')) {
			$query = base64_encode($query);
		  }
		  //$queryResponse = $this->_soapClient->xquery($this->_session, $queryBase64); 
		  $parameters = array('sessionId' => $this->_session , 'xquery' => $query );
		  $queryResponse = $this->soapCall('xquery', $parameters);
	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
		  return false;
	  }
	  
	  if ( $this->_debug && is_object($queryResponse) )
	  {
	      // xquery call Result
	      print "===========================================================================";
	      print "<p><b>Result of the <i>xquery</i> SOAP call (in PHP array format)</b></p>";
	      print "===========================================================================";
	      print "<p>\$queryResponse:<p><pre>";
	      print_r($queryResponse);
	      print "</pre>";
	      print "===========================================================================";
	  }

	  if ( is_object($queryResponse) && $queryResponse->hits > 0)
	  {
		  //$xml = $this->_soapClient->retrieve($this->_session, 1, $queryResponse->hits, true, true, "both");
		  /*
		  <element name="sessionId" type="xsd:string"/>
		  <element name="start" type="xsd:int"/>
		  <element name="howmany" type="xsd:int"/>
		  <element name="indent" type="xsd:boolean"/>
		  <element name="xinclude" type="xsd:boolean"/>
		  <element name="highlight" type="xsd:string"/>
		  */
		  $parameters = array('sessionId' => $this->_session , 
		  		'start' => 1, 
				'howmany' => $queryResponse->hits, 
				'indent' => TRUE, 
				'xinclude' => TRUE, 
				'highlight' => $this->_highlight
				);
		  $xml = $this->soapCall('retrieve', $parameters);
	  }
	  else
	  {
		  $this->_error = "ERROR: No data found!";
		  return false;
	  }

	  if ( $this->_debug && $xml != "" )
	  {
	      // xquery call Result
	      print "======================================================";
	      print "<p><b>Result of the <i>xquery</i> (in XML)</b></p>";
	      print "======================================================";
	      print "<pre>";
	      print "<pre>";
		  if ( !empty($result["XML"]) )
		  	foreach ( $result["XML"] as $xml)
		  		print htmlspecialchars($xml) . "<br />";
	      print "</pre>";
	      print "======================================================";
	  }
    
	  $result = array(
	      "HITS" => $queryResponse->hits,
	      "COLLECTIONS" => $queryResponse->collections,
	      "QUERY_TIME" => $queryResponse->queryTime,
	      "XML" => $xml
	    );
    
	 return $result;
  }
  
  protected function soapCall($function, $params)
  {
	  $return = $this->_soapClient->__soapCall($function, array('parameters'=>$params));
	  $output = $function . "Return";
    
	  return array_key_exists($output, $return) ? $return->$output : 0;
    
	  //return $return->$output;
  }
  
  public function setHighlight($highlight)
  {
	  $this->_highlight = $highlight ? 'both' : FALSE;
  }
  
  public function setDebug($debug=true)
  {
    $this->_debug = $debug;
  }
  
  public function setUser($user)
  {
    $this->_user = $user;
  } 
  
  public function setPassword($passwd)
  {
    $this->_password = $passwd;
  }
  
  public function setWSDL($wsdl)
  {
    $this->_wsdl = $wsdl;
  }
  
  protected function setError($error = '')
  {
    $this->_error = $error;
  }
}



/**
 * eXistAdmin Class
 *
 * This class is the main interface for manipulating the collections and documents into the eXist XML:DB.
 *
 * basic usage:<code>
 * include "existAdmin.php";
 * $db = new eXistAdmin('guest', 'guest', 'http://127.0.0.1:8080/exist/services/Admin?wsdl');
 * $db->connect() or die ($db->getError());
 * 
 * // Store Document
 * echo $db->store('<simple><fxp>françois</fxp></simple>', 
 * 				'UTF-8',
 * 				'/db/test.xml', true);
 * echo $db->store('<simple><fxp>françois</fxp></simple>', 
 * 				'UTF-8',
 * 				'/db/test2suppr.xml', true);
 * // Remove Document
 * echo $db->removeDocument('/db/test2suppr.xml');
 * 
 * // Create collection
 * echo $db->createCollection('/db/existAdminDemo');
 * echo $db->createCollection('/db/existAdminDemo2supp');
 * 
 * // Remove collection
 * echo $db->removeCollection('/db/existAdminDemo2supp');
 * 
 * // XupdateResource
 * $xupdate = "<xupdate:modifications version='1.0' xmlns:xupdate='http://www.xmldb.org/xupdate'>".
 * 		"<xupdate:update select='/simple/fxp'>TITI</xupdate:update></xupdate:modifications>";
 * 
 * echo $db->xupdateResource('/db/test.xml', $xupdate);
 * 
 * $db->disconnect() or die ($db->getError());
 * </code>
 *
 * @access   public
 */

class eXistAdmin extends  eXist
{ 
  public function __construct($user="guest", $password="guest", $wsdl="http://localhost:8080/exist/services/Admin?wsdl")
  {
	  $this->_user = $user;
	  $this->_password = $password;
	  $this->_wsdl = $wsdl;

	  $this->_soapClient = new SoapClient ($this->_wsdl);
  }
  
  public function __destruct() {
  }
 
/*
 * Store
 *    <element name="store">
 *    <complexType>
 *     <sequence>
 *      <element name="sessionId" type="xsd:string"/>
 *      <element name="data" type="xsd:base64Binary"/>
 *      <element name="encoding" type="xsd:string"/>
 *      <element name="path" type="xsd:string"/>
 *      <element name="replace" type="xsd:boolean"/>
 *     </sequence>
 *    </complexType>
 *   </element>
 */
  public function store($data, $encoding = "UTF-8", $path = "/db", $replace = false)
  {
	  if ( $this->getError() )
	  	return false;
	  if ( empty($data) )
	  {
		  $this->_error = "ERROR: No data to load !";
		  return false;
	  }
	  
	  try
	  {
		  // encode only to base64 if php version lesser than 5.1
		  // patch by Bastian Gorke bg/at\ipunkt/dot\biz
		  if (!version_compare(PHP_VERSION, '5.1.0', 'ge')) {
			$data = base64_encode($data);
		  }
		  //$queryResponse = $this->_soapClient->xquery($this->_session, $queryBase64); 
		  $parameters = array('sessionId' => $this->_session , 
							'data' => $data, 
							'encoding' => $encoding,
							'path' => $path, 							
							'replace' => $replace );

		  $queryResponse = $this->soapCall('store', $parameters);

	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
		  return false;
	  }
  
	  if ( $this->_debug && is_object($queryResponse) )
	  {
		  // xquery call Result
		  print "===========================================================================";
		  print "<p><b>Result of the <i>store</i> SOAP call (in PHP array format)</b></p>";
		  print "===========================================================================";
		  print "<p>\$queryResponse:<p><pre>";
		  print_r($queryResponse);
		  print "</pre>";
		  print "===========================================================================";
	  }

 	  return true;
  }

/*
 * createCollection
 *       
 *  <element name="createCollection">
 *    <complexType>
 *     <sequence>
 *      <element name="sessionId" type="xsd:string"/>
 *      <element name="path" type="xsd:string"/>
 *     </sequence>
 *   </complexType>
 */
  public function createCollection($path)
  {
	  if ( $this->getError() )
	  	return false;
	  if ( empty($path) )
	  {
		  $this->_error = "ERROR: path is empty!";
		  return false;
	  }
	  try
	  {
		  // encode only to base64 if php version lesser than 5.1
		  // patch by Bastian Gorke bg/at\ipunkt/dot\biz
		  if (!version_compare(PHP_VERSION, '5.1.0', 'ge')) {
			$xupdate = base64_encode($xupdate);
		  }
		  //$queryResponse = $this->_soapClient->xquery($this->_session, $queryBase64); 
		  $parameters = array('sessionId' => $this->_session , 'path' => $path);

		  $queryResponse = $this->soapCall('createCollection', $parameters);
	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
		  return false;
	  }
  
	  if ( $this->_debug && is_object($queryResponse) )
	  {
		  // xquery call Result
		  print "===========================================================================";
		  print "<p><b>Result of the <i>store</i> SOAP call (in PHP array format)</b></p>";
		  print "===========================================================================";
		  print "<p>\$queryResponse:<p><pre>";
		  print_r($queryResponse);
		  print "</pre>";
		  print "===========================================================================";
	  }


 	  return $queryResponse->createCollectionReturn;
  }

/*
 * removeCollection
 *       
 *  <element name="removeCollection">
 *    <complexType>
 *     <sequence>
 *      <element name="sessionId" type="xsd:string"/>
 *      <element name="path" type="xsd:string"/>
 *     </sequence>
 *   </complexType>
 */
  public function removeCollection($path)
  {
	  if ( $this->getError() )
	  	return false;
	  if ( empty($path) )
	  {
		  $this->_error = "ERROR: path is empty!";
		  return false;
	  }
	  try
	  {
		  // encode only to base64 if php version lesser than 5.1
		  // patch by Bastian Gorke bg/at\ipunkt/dot\biz
		  if (!version_compare(PHP_VERSION, '5.1.0', 'ge')) {
			$xupdate = base64_encode($xupdate);
		  }
		  //$queryResponse = $this->_soapClient->xquery($this->_session, $queryBase64); 
		  $parameters = array('sessionId' => $this->_session , 'path' => $path);

		  $queryResponse = $this->soapCall('removeCollection', $parameters);
	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
		  return false;
	  }

  
	  if ( $this->_debug && is_object($queryResponse) )
	  {
		  // xquery call Result
		  print "===========================================================================";
		  print "<p><b>Result of the <i>store</i> SOAP call (in PHP array format)</b></p>";
		  print "===========================================================================";
		  print "<p>\$queryResponse:<p><pre>";
		  print_r($queryResponse);
		  print "</pre>";
		  print "===========================================================================";
	  }

 	  return $queryResponse->removeCollectionReturn;
  }

/*
 * removeDocument
 *       
 *  <element name="removeDocument">
 *    <complexType>
 *     <sequence>
 *      <element name="sessionId" type="xsd:string"/>
 *      <element name="path" type="xsd:string"/>
 *     </sequence>
 *   </complexType>
 */
  public function removeDocument($path)
  {
	  if ( $this->getError() )
	  	return false;
	  if ( empty($path) )
	  {
		  $this->_error = "ERROR: path is empty!";
		  return false;
	  }
	  try
	  {
		  // encode only to base64 if php version lesser than 5.1
		  // patch by Bastian Gorke bg/at\ipunkt/dot\biz
		  if (!version_compare(PHP_VERSION, '5.1.0', 'ge')) {
			$xupdate = base64_encode($xupdate);
		  }
		  $parameters = array('sessionId' => $this->_session , 'path' => $path);

		  $queryResponse = $this->soapCall('removeDocument', $parameters);
	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
		  return false;
	  }

  
	  if ( $this->_debug && is_object($queryResponse) )
	  {
		  // xquery call Result
		  print "===========================================================================";
		  print "<p><b>Result of the <i>store</i> SOAP call (in PHP array format)</b></p>";
		  print "===========================================================================";
		  print "<p>\$queryResponse:<p><pre>";
		  print_r($queryResponse);
		  print "</pre>";
		  print "===========================================================================";
	  }

 	  return $queryResponse->removeDocumentReturn;
  }

/*
 * xupdateResource
 *       
 *   <element name="xupdate">
 *    <complexType>
 *     <sequence>
 *      <element name="sessionId" type="xsd:string"/>
 *      <element name="collectionName" type="xsd:string"/>
 *      <element name="xupdate" type="xsd:string"/>
 *     </sequence>
 *    </complexType>
 */
  public function xupdate($collectionName, $xupdate)
  {
	  if ( $this->getError() )
	  	return false;
	  if ( empty($xupdate) )
	  {
		  $this->_error = "ERROR: Xupdate query is empty!";
		  return false;
	  }
	  try
	  {
		  // encode only to base64 if php version lesser than 5.1
		  // patch by Bastian Gorke bg/at\ipunkt/dot\biz
		  if (!version_compare(PHP_VERSION, '5.1.0', 'ge')) {
			$xupdate = base64_encode($xupdate);
		  }
		  //$queryResponse = $this->_soapClient->xquery($this->_session, $queryBase64); 
		  $parameters = array('sessionId' => $this->_session , 'collectionName' => $collectionName, 'xupdate' => $xupdate );

		  $queryResponse = $this->soapCall('xupdate', $parameters);
	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
		  return false;
	  }

	  if ( $this->_debug && is_object($queryResponse) )
	  {
		  // xquery call Result
		  print "===========================================================================";
		  print "<p><b>Result of the <i>store</i> SOAP call (in PHP array format)</b></p>";
		  print "===========================================================================";
		  print "<p>\$queryResponse:<p><pre>";
		  print_r($queryResponse);
		  print "</pre>";
		  print "===========================================================================";
	  }

 	  return $queryResponse->xupdateReturn;
  }

/*
 * xupdateResource
 *       
 * <element name="xupdateResource">
 *   <complexType>
 *    <sequence>
 *     <element name="sessionId" type="xsd:string"/>
 *     <element name="documentName" type="xsd:string"/>
 *     <element name="xupdate" type="xsd:string"/>
 *    </sequence>
 *   </complexType>
 *   </element>
 */
  public function xupdateResource($documentName, $xupdate)
  {
	  if ( $this->getError() )
	  	return false;
	  if ( empty($xupdate) )
	  {
		  $this->_error = "ERROR: Xupdate query is empty!";
		  return false;
	  }
	  try
	  {
		  // encode only to base64 if php version lesser than 5.1
		  // patch by Bastian Gorke bg/at\ipunkt/dot\biz
		  if (!version_compare(PHP_VERSION, '5.1.0', 'ge')) {
			$xupdate = base64_encode($xupdate);
		  }
		  //$queryResponse = $this->_soapClient->xquery($this->_session, $queryBase64); 
		  $parameters = array('sessionId' => $this->_session , 'documentName' => $documentName, 'xupdate' => $xupdate );

		  $queryResponse = $this->soapCall('xupdateResource', $parameters);
	  }
	  catch( SoapFault $e )
	  {
		  $this->setError($e->faultstring);
		  return false;
	  }
  
	  if ( $this->_debug && is_object($queryResponse) )
	  {
		  // xquery call Result
		  print "===========================================================================";
		  print "<p><b>Result of the <i>store</i> SOAP call (in PHP array format)</b></p>";
		  print "===========================================================================";
		  print "<p>\$queryResponse:<p><pre>";
		  print_r($queryResponse);
		  print "</pre>";
		  print "===========================================================================";
	  }

 	  return $queryResponse->xupdateResourceReturn;
  }
  
  // TODO : getCollectionDesc
  // TODO : getBinaryResource
}
?>
