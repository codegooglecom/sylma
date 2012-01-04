<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ls="http://www.sylma.org/security" xmlns:php="http://www.sylma.org/parser/action/compiler" version="1.0">
  
  <xsl:template match="php:window">
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template match="php:*">
    <xsl:apply-templates/>
  </xsl:template>
  
  <xsl:template match="php:insert">
    <xsl:processing-instruction name="php">
      <xsl:text>echo $aArguments[</xsl:text>
      <xsl:value-of select="@key"/>
      <xsl:text>]; </xsl:text>
    </xsl:processing-instruction>
  </xsl:template>
  
  <xsl:template match="*">
    <xsl:element name="{local-name()}" namespace="{namespace-uri()}">
      <xsl:apply-templates select="* | @*"/>
    </xsl:element>
  </xsl:template>
  
  <xsl:template match="@*">
    <xsl:copy-of select="."/>
  </xsl:template>
  
</xsl:stylesheet>
