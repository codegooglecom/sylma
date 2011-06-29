<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:test="http://www.sylma.org/modules/test" version="1.0">
  
  <xsl:template match="/test:tests">
    <div>
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  
  <xsl:template match="test:group">
    <xsl:param name="depth" select="3"/>
    <ul>
      <xsl:variable name="title">
        <xsl:choose>
          <xsl:when test="depth &gt; 6">strong</xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="concat('h', $depth)"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:element name="{$title}">
        <xsl:value-of select="@name"/>
      </xsl:element>
      <xsl:apply-templates select="test:description"/>
      <xsl:apply-templates select="test:group">
        <xsl:with-param name="depth" select="$depth + 1"/>
      </xsl:apply-templates>
      <xsl:apply-templates select="test:test"/>
    </ul>
  </xsl:template>
  
  <xsl:template match="test:description">
    <p><xsl:value-of select="."/></p>
  </xsl:template>
  
  <xsl:template match="test:test">
    <li>
      <xsl:attribute name="class">
        <xsl:text>sylma-test sylma-test-</xsl:text>
        <xsl:choose>
          <xsl:when test=". = 'true'">passed</xsl:when>
          <xsl:otherwise>failed</xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <xsl:value-of select="@name"/>
    </li>
  </xsl:template>
  
</xsl:stylesheet>
