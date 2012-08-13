<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:lc="http://www.sylma.org/schemas" xmlns:dbx="http://www.sylma.org/modules/dbx" version="1.0">

  <xsl:import href="build-query.xsl"/>

  <xsl:template match="/lc:sylma-schema">

    <xsl:call-template name="functions"/>
    <xsl:variable name="headers" select="*[3]"/>
    <xsl:variable name="pageSize" select="$headers/dbx:page-size"/>

<xsl:if test="$pageSize != 0">
let $page := <xsl:value-of select="$headers/dbx:page"/>
let $pageSize := <xsl:value-of select="$pageSize"/>
let $start := ($page * $pageSize) - $pageSize
</xsl:if>

let $result := <xsl:call-template name="loop"/>

<xsl:if test="$pageSize != 0">
let $pageTotal := ceiling((count($result) div $pageSize))
</xsl:if>

return
  element <xsl:value-of select="$parent-name"/> {
    <xsl:if test="$pageSize != 0">attribute total {$pageTotal}, attribute page {$page},</xsl:if>
    attribute lc:ns {'null'},
    <xsl:choose>
      <xsl:when test="$pageSize != 0">subsequence($result, ($start + 1), $pageSize)</xsl:when>
      <xsl:otherwise>$result</xsl:otherwise>
    </xsl:choose>
  }
  </xsl:template>
</xsl:stylesheet>
