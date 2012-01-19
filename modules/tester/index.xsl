<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:test="http://www.sylma.org/modules/tester" version="1.0">
  
  <xsl:variable name="class-failed">sylma-test-failed</xsl:variable>
  <xsl:variable name="class-title">sylma-test-title</xsl:variable>
  
  <xsl:template match="/test:tests">
    <div>
      <p><strong>Result : <xsl:call-template name="failed"/></strong></p>
      <xsl:apply-templates/>
    </div>
  </xsl:template>
  
  <xsl:template name="failed">
    <xsl:variable name="failed" select="count(.//test:test[test:result = 'false'])"/>
    <em>
      <xsl:variable name="count" select="count(.//test:test)"/>
      <xsl:choose>
        <xsl:when test="$failed">
          <span><xsl:value-of select="$failed"/></span>
          <xsl:text> / </xsl:text>
          <xsl:value-of select="$count"/> <xsl:text> failed</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$count"/>
          <xsl:text> tests</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </em>
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
        <xsl:attribute name="class">
          <xsl:value-of select="$class-title"/>
        </xsl:attribute>
        <xsl:value-of select="test:description"/> - <xsl:call-template name="failed"/>
      </xsl:element>
      <xsl:apply-templates select="test:group">
        <xsl:with-param name="depth" select="$depth + 1"/>
      </xsl:apply-templates>
      <xsl:apply-templates select="test:test"/>
    </ul>
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
      <xsl:apply-templates select="@expected"/>
      <xsl:if test="test:message">
        <p>
          <xsl:value-of select="test:message"/>
        </p>
      </xsl:if>
    </li>
  </xsl:template>
  
  <xsl:template match="@expected"> @valid <xsl:value-of select="."/></xsl:template>
  
</xsl:stylesheet>
