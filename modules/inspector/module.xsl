<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" extension-element-prefixes="func" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ins="http://www.sylma.org/modules/inspector" xmlns:func="http://exslt.org/functions" xmlns:set="http://exslt.org/sets" xmlns="http://www.w3.org/1999/xhtml">
  
	<xsl:template match="/ins:classes">
    <ul class="sylma-ins-module col-1-4 left">
      <h4><xsl:value-of select="$title"/></h4>
      <em><xsl:value-of select="substring($file, 7)"/></em>
      <xsl:apply-templates select="ins:class"/>
    </ul>
  </xsl:template>
  
  <xsl:template match="ins:class">
    <xsl:param name="parent"/>
    <xsl:variable name="key">
      <xsl:value-of select="concat($parent, @key)"/>
    </xsl:variable>
    <li>
      <a href="{$sylma-directory}/class-settings?path={$file}&amp;key={$key}"><xsl:value-of select="@key"/></a>
      <xsl:if test="ins:class">
        <ul>
          <xsl:apply-templates select="ins:class">
            <xsl:with-param name="parent" select="concat($key, '/')"/>
          </xsl:apply-templates>
        </ul>
      </xsl:if>
    </li>
  </xsl:template>
  
</xsl:stylesheet>

