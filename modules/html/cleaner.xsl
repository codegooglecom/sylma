<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:html="http://www.w3.org/1999/xhtml"
  xmlns:shtml="http://2014.sylma.org/html"
  xmlns:builder="http://2013.sylma.org/parser/reflector/builder"

  version="1.0"
>

  <xsl:variable name="break"><![CDATA[
]]></xsl:variable>

  <xsl:variable name="html">http://www.w3.org/1999/xhtml</xsl:variable>

  <xsl:template match="html:script | shtml:script">
    <xsl:element name="script" namespace="{$html}">
      <xsl:apply-templates select="@*"/>
      <xsl:choose>
        <xsl:when test="text()">
          <xsl:text disable-output-escaping="yes">//&lt;![CDATA[</xsl:text>
          <xsl:value-of select="$break"/>
          <!--
          <xsl:value-of select="normalize-space(text())" disable-output-escaping="yes"/>
          -->
          <xsl:value-of select="text()" disable-output-escaping="yes"/>
          <xsl:value-of select="$break"/>
          <xsl:text disable-output-escaping="yes">//]]&gt;</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text> </xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:element>
  </xsl:template>

  <xsl:template match="/">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="*">
    <xsl:apply-templates select="*"/>
  </xsl:template>

  <xsl:template match="html:html | shtml:html">
    <xsl:element name="{local-name()}" namespace="{$html}">
      <xsl:apply-templates select="@* | * | text()"/>
    </xsl:element>
  </xsl:template>

  <xsl:template name="xmlns">

  </xsl:template>

  <!-- Empty elements that can be display as simple tag !-->

  <xsl:template match="shtml:link | shtml:meta | shtml:br | shtml:img | shtml:input | shtml:hr">
    <xsl:apply-templates select="." mode="simple"/>
  </xsl:template>

  <xsl:template match="html:link | html:meta | html:br | html:img | html:input | html:hr">
    <xsl:apply-templates select="." mode="simple"/>
  </xsl:template>

  <xsl:template match="*" mode="simple">
    <xsl:element name="{local-name()}" namespace="{$html}">
      <xsl:apply-templates select="@*"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="html:* | shtml:*">
    <xsl:element name="{local-name()}" namespace="{$html}">
      <xsl:apply-templates select="@* | * | text()"/>
      <xsl:if test="not(normalize-space(.))"><![CDATA[]]></xsl:if>
    </xsl:element>
  </xsl:template>

  <xsl:template match="text()">
    <xsl:copy-of select="current()"/>
  </xsl:template>

  <xsl:template match="@*">
    <xsl:attribute name="{local-name()}">
      <xsl:value-of select="."/>
    </xsl:attribute>
  </xsl:template>

  <xsl:template match="@builder:source"/>
  
  <xsl:template match="@builder:element">
    <xsl:attribute name="data-source">
      <xsl:value-of select="."/>
    </xsl:attribute>
  </xsl:template>

  <xsl:template match="*" mode="disable">
    <p>Elément interdit : <xsl:value-of select="concat(namespace-uri(.), ' - ', name())"/></p>
  </xsl:template>

</xsl:stylesheet>
