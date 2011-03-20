<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions" xmlns:lx="http://ns.sylma.org/xslt" xmlns:dbx="http://www.sylma.org/modules/dbx" xmlns:ls="http://www.sylma.org/security" xmlns:lc="http://www.sylma.org/schemas" version="1.0" extension-element-prefixes="func">
  
  <xsl:param name="max-length">100</xsl:param>
  <xsl:param name="module"/>
  <xsl:param name="directory"/>
  
  <xsl:import href="../../schemas/functions.xsl"/>
  <xsl:import href="/sylma/xslt/string.xsl"/>
  <xsl:import href="/sylma/xslt/date.xsl"/>
  
  <xsl:template match="/*">
    <xsl:choose>
      <xsl:when test="*[4]/*">
        <xsl:apply-templates select="*[4]/*" mode="root"/>
      </xsl:when>
      <xsl:otherwise>
        <tr>
          <td colspan="99">
            <p class="no-result">Aucun résultat</p>
          </td>
        </tr>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="*" mode="root">
    <xsl:variable name="id" select="@xml:id"/>
    <xsl:variable name="self" select="."/>
    <tr>
      <td class="tools">
        <a title="Editer" href="{$module}/edit/{$id}"><img src="{$directory}/images/write.png"/></a>
        <a title="Supprimer" href="{$module}/delete/{$id}"><img src="{$directory}/images/delete.png"/></a>
        <a title="Voir" href="{$module}/view/{$id}"><img src="{$directory}/images/search.png"/></a>
        <a title="Imprimer" href="{$module}/view/{$id}.print"><img src="{$directory}/images/print.png"/></a>
      </td>
      
      <xsl:apply-templates select="*" mode="field">
        <xsl:with-param name="parent-element" select="lc:get-root-element()"/>
      </xsl:apply-templates>
    </tr>
  </xsl:template>
  
  <xsl:template match="*" mode="field">
    <xsl:param name="parent-element"/>
    
    <xsl:variable name="local" select="local-name()"/>
    <xsl:variable name="element" select="lc:element-get-element($parent-element)"/>
    
    <xsl:choose>
      <xsl:when test="*">
        <xsl:apply-templates select="*" mode="field">
          <xsl:with-param name="parent-element" select="$element"/>
        </xsl:apply-templates>
      </xsl:when>
      <xsl:otherwise>
        <td>
          <xsl:choose>
            <xsl:when test="lc:element-is-boolean($element)">
              <xsl:variable name="icone">
                <xsl:choose>
                  <xsl:when test=". = '0' or . = ''">delete</xsl:when>
                  <xsl:otherwise>ok</xsl:otherwise>
                </xsl:choose>
              </xsl:variable>
              <img src="{$directory}/images/{$icone}.png"/>
            </xsl:when>
            <xsl:when test=".">
              <xsl:choose>
                <xsl:when test="lc:element-is-date($element)">
                  <xsl:value-of select="lx:format-date(., '', 'simple')"/>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:value-of select="lx:string-resume(., $max-length)"/>
                </xsl:otherwise>
              </xsl:choose>
            </xsl:when>
          </xsl:choose>
        </td>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>
