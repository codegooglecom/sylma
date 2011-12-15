<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ls="http://www.sylma.org/security" xmlns:php="http://www.sylma.org/parser/action/compiler" version="1.0">
  
  <xsl:output method="text"/>
  
  <xsl:template match="php:window">&lt;?php

namespace sylma\parser\action;

require_once('parser\action\Basic.php');

class ActionTest extends Basic {
  
  public function __construct() {
    
  }
  
  protected function parseAction() {
    
    <xsl:for-each select="*">
      <xsl:apply-templates select="."/>
      <xsl:text>;</xsl:text>
    </xsl:for-each>
    
  }
  
  public function asArgument() {
    
    $result = $this->parseAction();
    
    if ($result) $result = $result->asArgument();
    
    return $result;
  }
}

  </xsl:template>
  
  <xsl:template match="php:call">
    <xsl:apply-templates select="php:called"/>
    <xsl:text>-&gt;</xsl:text>
    <xsl:apply-templates select="@name"/>
    <xsl:text>(</xsl:text>
    <xsl:call-template name="arguments"/>
    <xsl:text>)</xsl:text>
  </xsl:template>
  
  <xsl:template match="php:called">
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template match="php:var">
    $<xsl:value-of select="@name"/>
  </xsl:template>
  
  <xsl:template match="php:argument">
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template name="arguments">
    <xsl:for-each select="php:argument">
      <xsl:apply-templates select="."/>
      <xsl:if test="following-sibling::php:argument">, </xsl:if>
    </xsl:for-each>
  </xsl:template>
  
  <xsl:template match="php:string">
    <xsl:text>'</xsl:text>
    <xsl:value-of select="."/>
    <xsl:text>'</xsl:text>
  </xsl:template>
  <xsl:template match="php:numeric">
    <xsl:value-of select="."/>
  </xsl:template>
  
</xsl:stylesheet>
