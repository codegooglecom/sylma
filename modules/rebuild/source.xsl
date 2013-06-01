<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:fs="http://www.sylma.org/storage/fs"

  version="1.0"
>

  <xsl:template match="/">
    <div>
      <div style="-moz-columns: 3;">
        <xsl:apply-templates select="*"/>
      </div>
      <div>
        <button onclick="sylma.rebuild.main.toggle()" id="rebuild-toggle">Start</button>
      </div>
    </div>
  </xsl:template>

  <xsl:template match="fs:file">
    <div style="margin-bottom: 5px;">
      <a href="{fs:path}" class="rebuild-file rebuild-ready" user-data="{fs:path}">
        <xsl:value-of select="fs:path"/>
      </a>
      <span></span>
    </div>
  </xsl:template>


</xsl:stylesheet>
