<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:js="http://www.sylma.org/parser/languages/js" xmlns:php="http://www.sylma.org/parser/languages/php" version="1.0">

  <xsl:include href="common.xsl"/>

<xsl:variable name="break">
<xsl:text>
</xsl:text>
</xsl:variable>

  <xsl:template match="php:*">
    <xsl:copy>
        <xsl:apply-templates select="@*|node()"/>
    </xsl:copy>
  </xsl:template>

  <xsl:template match="@*">
    <xsl:copy/>
  </xsl:template>

  <xsl:template match="js:window">
    <php:concat>
      <xsl:apply-templates/>
    </php:concat>
  </xsl:template>

</xsl:stylesheet>
