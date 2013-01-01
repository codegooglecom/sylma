<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:la="http://www.sylma.org/parser/js/binder/cached" xmlns:php="test" version="1.0">

  <xsl:template match="*">
    <xsl:param name="id"/>
    <xsl:element name="{local-name()}" namespace="{namespace-uri()}">
      <xsl:if test="$id">
        <xsl:attribute name="id">
          <xsl:value-of select="$id"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:apply-templates select="node() | @*"/>
    </xsl:element>
  </xsl:template>

  <xsl:template match="la:*">
    <xsl:if test="count(*) != 1">
      <php:function name="\sylma\parser\js\binder\Cached::xslException"/>
    </xsl:if>
    <xsl:variable name="child" select="*"/>
    <xsl:variable name="id">
      <xsl:choose>
        <xsl:when test="not($child/@id)">
          <xsl:value-of select="generate-id()"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$child/@id"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <xsl:element name="{local-name()}" namespace="{namespace-uri()}">
      <xsl:attribute name="id">
        <xsl:value-of select="$id"/>
      </xsl:attribute>
      <xsl:apply-templates select="node() | @*">
        <xsl:with-param name="id" select="$id"/>
      </xsl:apply-templates>
    </xsl:element>
  </xsl:template>

  <xsl:template match="@*">
    <xsl:copy/>
  </xsl:template>

  <xsl:template match="text()">
    <xsl:copy/>
  </xsl:template>

</xsl:stylesheet>
