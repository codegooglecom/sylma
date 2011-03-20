<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lc="http://www.sylma.org/schemas" xmlns:lx="http://ns.sylma.org/xslt" version="1.0" extension-element-prefixes="func lx">
  
  <xsl:import href="form-file.xsl"/>
  
  <xsl:param name="name"/>
  <xsl:param name="title"/>
  
  <xsl:template match="/*">
    
    <xsl:apply-templates select="." mode="field">
      <xsl:with-param name="name" select="concat($name, '[sylma-', generate-id(), ']')"/>
      <xsl:with-param name="title" select="$title"/>
    </xsl:apply-templates>
    
  </xsl:template>
  
</xsl:stylesheet>
