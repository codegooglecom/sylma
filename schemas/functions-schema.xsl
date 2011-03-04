<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:lc="http://www.sylma.org/schemas" xmlns:func="http://exslt.org/functions" version="1.0" extension-element-prefixes="func exsl">
  
  <func:function name="lc:boolean">
    <xsl:param name="val"/>
    <xsl:param name="default" select="boolean(0)"/>
    <xsl:choose>
      <xsl:when test="$val and ($val = 'false' or $val = 'true')">
        <xsl:if test="$val = 'true'">
          <func:result select="boolean(1)"/>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="$default"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  
  <func:function name="lc:get-root-element">
    <xsl:param name="source" select="."/>
    <func:result select="/*/lc:schemas/lc:element[@name = local-name($source)]"/>
  </func:function>
  
  <func:function name="lc:element-get-element">
    <xsl:param name="element"/>
    <xsl:param name="source" select="."/>
    <xsl:variable name="model" select="lc:get-model($source)"/>
    <xsl:if test="$element">
      <xsl:variable name="schema" select="lc:element-get-schema($element)"/>
      <xsl:if test="$schema">
        <func:result select="$schema/*/lc:element[@name = local-name($source)]"/>
      </xsl:if>
    </xsl:if>
  </func:function>
  
  <func:function name="lc:element-get-schema">
    <xsl:param name="element"/>
    <xsl:if test="$element and $element/@type">
      <func:result select="/*/lc:schemas/lc:base[@name = $element/@type]"/>
    </xsl:if>
  </func:function>
  
  <func:function name="lc:element-get-attribute">
    <xsl:param name="element"/>
    <xsl:param name="source" select="."/>
    <xsl:param name="source-name" select="name($source)"/>
    <xsl:if test="$element">
      <xsl:variable name="schema" select="lc:element-get-schema($element)"/>
      <xsl:if test="$schema">
        <func:result select="$schema/lc:attribute[@name = $source-name]"/>
      </xsl:if>
    </xsl:if>
  </func:function>
  
  <func:function name="lc:element-is-complex">
    <xsl:param name="element"/>
    <xsl:if test="$element and not($element/@basic-type)">
      <xsl:variable name="schema" select="lc:element-get-schema($element)"/>
      <xsl:choose>
        <xsl:when test="$schema">
          <func:result select="lc:boolean($schema/@complex)"/>
        </xsl:when>
        <xsl:otherwise>
          <func:result select="lc:boolean($element/@complex)"/>
        </xsl:otherwise>
      </xsl:choose>
      
    </xsl:if>
  </func:function>
  
  <func:function name="lc:element-is-simple">
    <xsl:param name="element"/>
    <func:result select="not(lc:element-is-complex($element))"/>
  </func:function>
  
  <func:function name="lc:element-get-type">
    <xsl:param name="element"/>
    <xsl:choose>
      <xsl:when test="$element and lc:element-is-simple($element)">
        <func:result select="$element/@basic-type"/>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="''"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  
  <func:function name="lc:element-is-required">
    <xsl:param name="element"/>
    <func:result select="boolean($element and ($element/@required or not($element/@minOccurs) or ($element/@minOccurs != '' and $element/@minOccurs &gt; 0)))"/>
  </func:function>
  
  <func:function name="lc:element-get-title">
    <xsl:param name="element"/>
    <xsl:param name="source"/>
    <xsl:choose>
      <xsl:when test="$element/@lc:title">
        <func:result select="$element/@lc:title"/>
      </xsl:when>
      <xsl:when test="$element/@name">
        <func:result select="$element/@name"/>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="local-name($source)"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  
  <func:function name="lc:element-is-string">
    <xsl:param name="element"/>
    <func:result select="boolean(lc:element-get-type($element) = 'xs:string')"/>
  </func:function>
  <func:function name="lc:element-is-boolean">
    <xsl:param name="element"/>
    <func:result select="boolean(lc:element-get-type($element) = 'xs:boolean')"/>
  </func:function>
  <func:function name="lc:element-is-date">
    <xsl:param name="element"/>
    <func:result select="boolean(lc:element-get-type($element) = 'xs:date')"/>
  </func:function>
  <func:function name="lc:element-is-integer">
    <xsl:param name="element"/>
    <func:result select="boolean(lc:element-get-type($element) = 'xs:integer')"/>
  </func:function>
  <func:function name="lc:element-is-enum">
    <xsl:param name="element"/>
    <func:result select="lc:element-is-simple($element) and $element and $element/lc:restriction/lc:enumeration"/>
  </func:function>
  <func:function name="lc:element-is-keyref">
    <xsl:param name="element"/>
    <func:result select="boolean($element/@lc:key-ref)"/>
  </func:function>
  
</xsl:stylesheet>
