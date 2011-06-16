<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:menu="http://www.sylma.org/modules/tree-menu" version="1.0">
  <xsl:param name="extension"/>
  <xsl:template match="/*">
    <ul id="menu-{@name}" class="multi-menu">
      <xsl:apply-templates select="*/*">
        <xsl:with-param name="parent" select="@name"/>
      </xsl:apply-templates>
    </ul>
  </xsl:template>
  <xsl:template match="menu:category">
    <xsl:param name="parent"/>
    <li>
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:if test="@id">
        <xsl:attribute name="id">
          <xsl:value-of select="concat($parent, '-', @id)"/>
        </xsl:attribute>
      </xsl:if>
      <xsl:choose>
        <xsl:when test="@no-link = 'true'">
          <span>
            <xsl:value-of select="@title"/>
          </span>
        </xsl:when>
        <xsl:otherwise>
          <a href="{@absolute-path}{$extension}">
            <xsl:value-of select="@title"/>
          </a>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="*">
        <xsl:choose>
          <xsl:when test="menu:category">
            <ul>
              <xsl:apply-templates select="menu:category[not(@no-display)]" mode="sub"/>
            </ul>
          </xsl:when>
          <xsl:otherwise>
            <xsl:copy-of select="*[name() != 'category']"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:if>
    </li>
  </xsl:template>
  <xsl:template match="menu:category" mode="sub">
    <li>
      <xsl:if test="@class">
        <xsl:attribute name="class">
          <xsl:value-of select="@class"/>
        </xsl:attribute>
      </xsl:if>
      <a href="{@absolute-path}{$extension}">
        <xsl:value-of select="@title"/>
      </a>
    </li>
  </xsl:template>
</xsl:stylesheet>
