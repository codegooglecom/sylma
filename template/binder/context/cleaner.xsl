<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:html="http://www.w3.org/1999/xhtml" version="1.0">

  <xsl:variable name="break"><![CDATA[
]]></xsl:variable>


  <xsl:template match="/">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template name="xmlns">

  </xsl:template>

  <!-- Empty elements that can be display as simple tag !-->

  <xsl:template match="link | meta | br | img | input | hr">
    <xsl:element name="{local-name()}" namespace="{namespace-uri()}">
      <xsl:apply-templates select="@*"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="*">
    <xsl:element name="{local-name()}" namespace="{namespace-uri()}">
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

</xsl:stylesheet>
