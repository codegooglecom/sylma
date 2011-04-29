<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
  <xsl:template match="/">
    <table style="width: 45%;">
      <xsl:apply-templates/>
    </table>
  </xsl:template>
  <xsl:template match="/*/*">
    <tr>
      <td style="font-weight: bold; width: 0; text-align: right;">
        <xsl:value-of select="concat(text(), ' ')"/>
      </td>
      <td>
        <xsl:value-of select="@key"/>
      </td>
    </tr>
  </xsl:template>
</xsl:stylesheet>
