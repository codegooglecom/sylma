<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lc="http://www.sylma.org/schemas" xmlns:lx="http://ns.sylma.org/xslt" version="1.0" extension-element-prefixes="func lx">
  
  <xsl:import href="form-functions.xsl"/>
  
  <xsl:param name="element-name"/>
  
  <xsl:template match="/*">
    
    <xsl:variable name="element" select="lc:get-root-element(current()/*[1])"/>
    
    <xsl:apply-templates select="*[1]/*" mode="search">
      <xsl:with-param name="parent" select="''"/>
      <xsl:with-param name="parent-element" select="$element"/>
    </xsl:apply-templates>
    
  </xsl:template>
  
  <xsl:template match="*" mode="search">
    
    <xsl:param name="parent"/>
    <xsl:param name="parent-element"/>
    
    <xsl:choose>
      
      <xsl:when test="local-name() = $element-name">
        <xsl:apply-templates select="." mode="field">
          <xsl:with-param name="parent" select="$parent"/>
          <xsl:with-param name="parent-element" select="$parent-element"/>
        </xsl:apply-templates>
      </xsl:when>
      
      <xsl:otherwise>
        <xsl:variable name="element" select="lc:element-get-element($parent-element)"/>
        <xsl:apply-templates select="*" mode="search">
          <xsl:with-param name="parent" select="lc:build-name($element, $parent)"/>
          <xsl:with-param name="parent-element" select="$element"/>
        </xsl:apply-templates>
      </xsl:otherwise>
      
    </xsl:choose>
    
  </xsl:template>
  
</xsl:stylesheet>
