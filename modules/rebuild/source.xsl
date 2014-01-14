<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:fs="http://www.sylma.org/storage/fs"

  version="1.0"
>

  <xsl:template match="/">
    <div id="sylma-rebuild">
      <h3><xsl:value-of select="count(*[1]/*)"/> fichiers</h3>
      <div style="-moz-columns: 3;">
        <xsl:apply-templates select="*"/>
      </div>
      <div>
        <button onclick="sylma.rebuild.main.toggle()" id="sylma-rebuild-toggle">Start</button>
        <button onclick="sylma.rebuild.main.clearLog()">Clear</button>
      </div>
      <div id="sylma-rebuild-log"/>
    </div>
  </xsl:template>

  <xsl:template match="fs:file">
    <div style="margin-bottom: 5px;">
      <a href="{fs:action-path}" class="rebuild-file rebuild-ready" user-data="{fs:path}">
        <xsl:value-of select="fs:path"/>
      </a>
      <span></span>
    </div>
  </xsl:template>


</xsl:stylesheet>
