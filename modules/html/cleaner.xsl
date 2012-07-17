<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:html="http://www.w3.org/1999/xhtml" version="1.0">

  <xsl:template match="/">
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="html:html">
    <xsl:element name="{local-name()}" namespace="{namespace-uri}">
      <xsl:attribute name="xmlns">
        <xsl:value-of select="namespace-uri()"/>
      </xsl:attribute>
      <xsl:apply-templates select="@* | * | text()"/>
    </xsl:element>
  </xsl:template>

  <!-- Empty elements that can be display as simple tag !-->

  <xsl:template match="html:link | html:meta | html:br | html:img | html:input | html:hr">
    <xsl:element name="{local-name()}" namespace="{namespace-uri}">
      <xsl:apply-templates select="@*"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="html:*">
    <xsl:element name="{local-name()}" namespace="{namespace-uri}">
      <xsl:apply-templates select="@* | * | text()"/>
      <xsl:if test="not(normalize-space(.))">
        <xsl:text> </xsl:text>
      </xsl:if>
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

  <xsl:template match="*" mode="disable">
    <p>Elément interdit : <xsl:value-of select="concat(namespace-uri(.), ' - ', name())"/></p>
  </xsl:template>

</xsl:stylesheet>
