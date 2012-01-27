<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ins="http://www.sylma.org/modules/inspector" xmlns:func="http://exslt.org/functions">

  <xsl:output method="text"/>
  <xsl:import href="/sylma/xslt/string.xsl"/>

  <xsl:param name="namespace"/>

<xsl:variable name="break">
<xsl:text>
</xsl:text>
</xsl:variable>

<xsl:variable name="tab">
<xsl:text>  </xsl:text>
</xsl:variable>

<xsl:template match="/ins:classes">
  <xsl:text>&lt;?php</xsl:text>
  <xsl:value-of select="concat($break, 'namespace ', $namespace, ';', $break)"/>
  <xsl:apply-templates select="*"/>
</xsl:template>

<xsl:template match="ins:class">
  <xsl:value-of select="$break"/>
  <xsl:value-of select="$break"/>
  <xsl:apply-templates select="@file"/>
  <xsl:value-of select="$break"/>
  <xsl:value-of select="$break"/>
  <xsl:value-of select="concat('class ', @valid-name)"/>
  <xsl:text> extends \</xsl:text>
  <xsl:value-of select="@name"/>
  <xsl:text> {</xsl:text>
  <xsl:apply-templates select="ins:method[@access != 'private']"/>
  <xsl:value-of select="$break"/>
  <xsl:text>}</xsl:text>
</xsl:template>

<xsl:template match="@file">require_once('<xsl:value-of select="substring(., 8)"/>');</xsl:template>

<xsl:template match="ins:method">
  <xsl:value-of select="concat($break, $tab, $break, $tab)"/>
  <xsl:apply-templates select="@access | @static[. = 'true']"/>function <xsl:apply-templates select="@reference[. = 'true']"/><xsl:value-of select="@name"/>(<xsl:value-of select="lx:implode(ins:parameter)"/>

  <xsl:value-of select="concat(') {', $break)"/>
    $timer = \Sylma::getControler('timer');
    $timer->open(__method__);
    $result = parent::<xsl:value-of select="@name"/>(<xsl:value-of select="ins:implode-call(ins:parameter)"/>);
    $timer->close();

    return $result;
  <xsl:text>}</xsl:text>
</xsl:template>

<xsl:template match="ins:parameter" mode="call">
  <xsl:value-of select="concat('$', @name)"/>
</xsl:template>

<xsl:template match="@reference">&amp;</xsl:template>

<xsl:template match="ins:parameter">
  <xsl:apply-templates select="ins:cast"/>
  <xsl:apply-templates select="@reference[. = 'true']"/>
  <xsl:value-of select="concat('$', @name)"/>
  <xsl:apply-templates select="ins:default"/>
</xsl:template>

<xsl:template match="ins:default"> = <xsl:value-of select="."/></xsl:template>
<xsl:template match="@access"><xsl:value-of select="concat(., ' ')"/></xsl:template>
<xsl:template match="@static">static </xsl:template>
<xsl:template match="ins:cast"><xsl:value-of select="concat(., ' ')"/></xsl:template>

<func:function name="ins:implode-call">
  <xsl:param name="items" />
  <xsl:param name="separator" select="', '" />
  <xsl:if test="$items">
    <func:result>
      <xsl:for-each select="$items">
        <xsl:if test="position() &gt; 1">
          <xsl:value-of select="$separator" />
        </xsl:if>
        <xsl:apply-templates select="." mode="call"/>
      </xsl:for-each>
    </func:result>
  </xsl:if>
</func:function>

</xsl:stylesheet>

