<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:lc="http://www.sylma.org/schemas" xmlns:dbx="http://www.sylma.org/modules/dbx" version="1.0">
  <xsl:import href="../build-query.xsl"/>
  <xsl:template match="/lc:sylma-schema">
    <xsl:call-template name="functions"/>
    <xsl:variable name="headers" select="*[3]"/>
let $page := <xsl:value-of select="$headers/dbx:page"/>
let $pageSize := <xsl:value-of select="$headers/dbx:page-size"/>
let $start := ($page * $pageSize) - $pageSize

let $result := <xsl:call-template name="loop"/>

let $pageTotal := ceiling((count($result) div $pageSize))

return
  element <xsl:value-of select="$parent-name"/> {
    attribute total {$pageTotal}, attribute page {$page}, attribute lc:ns {'null'},
    subsequence($result, ($start + 1), $pageSize)}
  </xsl:template>
</xsl:stylesheet>
