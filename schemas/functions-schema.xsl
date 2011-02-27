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
  
  <func:function name="lc:schema-get-schema">
    <xsl:param name="schema"/>
    <xsl:param name="source" select="."/>
    <xsl:variable name="model" select="lc:get-model($source)"/>
    <xsl:choose>
      <xsl:when test="$model">
        <xsl:choose>
          <xsl:when test="$model/@base">
            <func:result select="/*/lc:schemas/lc:base[@name = $model/@base]"/>
          </xsl:when>
          <xsl:when test="$schema">
            <func:result select="$schema/*/lc:element[@name = local-name($source)]"/>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$schema">
        <func:result select="$schema/*/lc:element[@name = local-name($source)]"/>
      </xsl:when>
    </xsl:choose>
  </func:function>
  
  <func:function name="lc:schema-get-schema-test">
    <xsl:param name="schema"/>
    <xsl:param name="source" select="."/>
    <xsl:variable name="model" select="lc:get-model($source)"/>
    <xsl:choose>
      <xsl:when test="$model">
        <xsl:choose>
          <xsl:when test="$model/@base">
            <func:result select="'BASE'"/>
          </xsl:when>
          <xsl:when test="$schema">
            <xsl:variable name="element" select="$schema/*/lc:element[@name = local-name($source)]"/>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
      <xsl:when test="$schema">
        <xsl:variable name="element" select="$schema/*/lc:element[@name = local-name($source)]"/>
      </xsl:when>
    </xsl:choose>
  </func:function>
  
  <func:function name="lc:schema-get-schema-attribute">
    <xsl:param name="schema"/>
    <xsl:param name="source" select="."/>
    <xsl:if test="schema">
      <func:result select="$schema/lc:attribute[@name = local-name($source)]"/>
    </xsl:if>
  </func:function>
  
  <func:function name="lc:schema-is-complex">
    <xsl:param name="schema"/>
    <xsl:if test="$schema">
      <func:result select="lc:boolean($schema/@complex)"/>
    </xsl:if>
  </func:function>
  
  <func:function name="lc:schema-is-simple">
    <xsl:param name="schema"/>
    <func:result select="not(lc:schema-is-complex($schema))"/>
  </func:function>
  
  <func:function name="lc:schema-get-type">
    <xsl:param name="schema"/>
    <xsl:choose>
      <xsl:when test="$schema and not(lc:schema-is-complex($schema))">
        <func:result select="$schema/@type"/>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="''"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  
  <func:function name="lc:schema-is-required">
    <xsl:param name="schema"/>
    <xsl:param name="source" select="."/>
    <func:result select="boolean($schema and ($schema/@required or not($schema/@minOccurs) or ($schema/@minOccurs != '' and $schema/@minOccurs &gt; 0)))"/>
  </func:function>
  
  <func:function name="lc:schema-get-title">
    <xsl:param name="schema"/>
    <xsl:param name="source"/>
    <xsl:choose>
      <xsl:when test="$schema/@lc:title">
        <func:result select="$schema/@lc:title"/>
      </xsl:when>
      <xsl:when test="$source/@name">
        <func:result select="$source/@name"/>
      </xsl:when>
      <xsl:otherwise>
        <func:result select="local-name($source)"/>
      </xsl:otherwise>
    </xsl:choose>
  </func:function>
  
  <func:function name="lc:schema-is-string">
    <xsl:param name="schema"/>
    <func:result select="boolean(lc:schema-get-type($schema) = 'xs:string')"/>
  </func:function>
  <func:function name="lc:schema-is-boolean">
    <xsl:param name="schema"/>
    <func:result select="boolean(lc:schema-get-type($schema) = 'xs:boolean')"/>
  </func:function>
  <func:function name="lc:schema-is-date">
    <xsl:param name="schema"/>
    <func:result select="boolean(lc:schema-get-type($schema) = 'xs:date')"/>
  </func:function>
  <func:function name="lc:schema-is-integer">
    <xsl:param name="schema"/>
    <func:result select="boolean(lc:schema-get-type($schema) = 'xs:integer')"/>
  </func:function>
  <func:function name="lc:schema-is-enum">
    <xsl:param name="schema"/>
    <func:result select="lc:schema-is-simple($schema) and $schema and $schema/lc:restriction/lc:enumeration"/>
  </func:function>
  <func:function name="lc:schema-is-keyref">
    <xsl:param name="schema"/>
    <func:result select="boolean($schema/@lc:key-ref)"/>
  </func:function>
  
</xsl:stylesheet>
