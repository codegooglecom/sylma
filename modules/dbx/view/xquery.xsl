<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:lc="http://www.sylma.org/schemas" xmlns:dbx="http://www.sylma.org/modules/dbx" version="1.0">
  <xsl:import href="../build-query.xsl"/>
  <xsl:template match="/lc:sylma-schema">
    <xsl:variable name="headers" select="*[3]"/>
    let $self := <xsl:value-of select="$path"/>
    <xsl:apply-templates select="$headers/dbx:element" mode="prepare"/>
    return element {name($self)} {
      $self/@*, <xsl:apply-templates select="$headers/dbx:element" mode="result"/>
    }
  </xsl:template>
</xsl:stylesheet>
