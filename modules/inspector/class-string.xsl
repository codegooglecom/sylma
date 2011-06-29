<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" extension-element-prefixes="func" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ins="http://www.sylma.org/modules/inspector" xmlns:func="http://exslt.org/functions" xmlns:set="http://exslt.org/sets">
  
  <xsl:output method="text"/>
  <xsl:import href="functions.xsl"/>
  
<xsl:variable name="break">
<xsl:text>
</xsl:text>
</xsl:variable>

<xsl:variable name="tab">
<xsl:text>  </xsl:text>
</xsl:variable>

<xsl:template match="/*">
  <xsl:text>&lt;?php</xsl:text>
  <xsl:value-of select="$break"/>
  <xsl:value-of select="$break"/>
  <xsl:apply-templates select="ins:comment">
    <xsl:with-param name="suffix"/>
  </xsl:apply-templates>
  <xsl:value-of select="concat('class ', @name)"/> <xsl:apply-templates select="ins:extension | ins:interfaces"/>
  <xsl:text>{</xsl:text>
  <xsl:apply-templates select="ins:constant"/>
  <xsl:apply-templates select="ins:property"/>
  <xsl:apply-templates select="ins:method"/>
  <xsl:value-of select="$break"/>
  <xsl:text>}</xsl:text>
</xsl:template>

<xsl:template match="ins:extension"> extends <xsl:value-of select="."/></xsl:template>
<xsl:template match="ins:interface"> implements <xsl:value-of select="ins:implode(*)"/></xsl:template>

<xsl:template match="ins:constant"></xsl:template>

<xsl:template match="ins:property">
  <xsl:value-of select="concat($break, $tab, $break, $tab)"/>
  <xsl:apply-templates select="ins:comment"/>
  <xsl:apply-templates select="ins:access"/>$<xsl:value-of select="@name"/><xsl:apply-templates select="ins:default"/>
  <xsl:text>;</xsl:text>
</xsl:template>

<xsl:template match="ins:method">
  <xsl:value-of select="concat($break, $tab, $break, $tab)"/>
  <xsl:apply-templates select="ins:comment"/>
  <xsl:apply-templates select="ins:access"/>function <xsl:value-of select="@name"/>(<xsl:apply-templates select="ins:parameter"/>
  <xsl:value-of select="concat(') {', $break)"/>
  <xsl:value-of select="ins:source"/>
  <xsl:text>  }</xsl:text>
</xsl:template>

<xsl:template match="ins:comment">
  <xsl:param name="suffix" select="$tab"/>
  <xsl:value-of select="ins:source"/>
  <xsl:value-of select="$break"/>
  <xsl:value-of select="$suffix"/>
</xsl:template>

<xsl:template match="ins:default"> = <xsl:value-of select="."/></xsl:template>
<xsl:template match="ins:access">public </xsl:template>

</xsl:stylesheet>

