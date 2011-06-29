<?php

require_once('schemas/XML_Schema.php');
require_once('modules/test/Test.php');

class schemaTest extends Test {
  
  const NS_SCHEMA = 'http://www.sylma.org/schemas/test';
  const NS_TARGET = 'http://www.sylma.org';
  
  public function __construct() {
    
    $this->setDirectory(__file__);
    
    $this->setNamespaces(array(
      'self' => self::NS_SCHEMA,
      'target' => self::NS_TARGET,
      'schema' => XSD_Parser::NS,
    ));
    
    parent::__construct();
  }
  
  public function generate(DocumentInterface $doc) {
    
    $aResult = array();
    
    foreach ($doc->query('//self:document', $this->getNS()) as $doc) {
      
    }
    
    return $aResult;
  }
  
  public function validate(DocumentInterface $doc) {
    
    $eSchema = $doc->get('self:schema', $this->getNS());
    
    if (!$eSchema || $eSchema->isEmpty()) $this->throwException('No schema defined', '@file ' . $doc->getFile());
    
    $schema = new XML_Document;
    $schema->addNode('schema', $eSchema->getChildren(), array('targetNamespace' => $this->getNamespace('target')), $this->getNamespace());
    
    $aResult = array();
    
    foreach ($doc->query('self:document', $this->getNS()) as $test) {
      
      $tmpDoc = new XML_Document($test->getChildren());
      $bResult = $tmpDoc->validate($schema);
      
      $aResult[] = array(
        'result' => booltostr($test->testAttribute('expected') === $bResult),
        '@name' => $test->getAttribute('name'));
    }
    
    return array(
      '@name' => $doc->getFile()->getName(),
      'description' => $doc->read('self:description', $this->getNS()),
      '#test' => $aResult,
    );
  }
  
  public function parse() {
    
    $aValidation = $aGeneration = array();
    
    foreach ($this->getDirectory()->getFiles(array('xml')) as $file) {
      
      $doc = $file->getDocument();
      
      switch ($doc->getRoot()->getName()) {
        
        case 'validation' : $aValidation[] = $this->validate($doc); break;
        case 'generation' : $aGeneration[] = $this->generate($doc); break;
      }
    }
    
    $result = Arguments::buildDocument(array(
      'group' => array(
        '@name' => 'Schemas',
        'description' => t('W3C Schemas validation and generation'),
        '#group' => array(
          array(
            '@name' => 'validation',
            'description' => t('Validation of document'),
            '#group' => $aValidation,
          ),
          array(
            '@name' => 'generation',
            'description' => t('Generation of model'),
            '#group' => $aGeneration,
          ),
        ),
      ),
    ), $this->getNamespace());
    
    return $result;
  }
}

