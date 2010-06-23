<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:la="/sylma/processors/action-builder/schema" xmlns:le="http://www.sylma.org/execution" version="1.0">
  <le:mark>/sylma/processors/action-builder/schema</le:mark>
  <xsl:import href="directory.xsl"/>
  <xsl:import href="file.xsl"/>
  <xsl:template match="/*">
    <xsl:choose>
      <xsl:when test="name() = 'directory'">
        <xsl:call-template name="directory"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:call-template name="file"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>
