<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" xmlns:cmd="http://www.pronoxa.ch/commandes" xmlns:la="http://www.sylma.org/processors/action-builder" version="1.0">
  <xsl:template match="/*">
    <div class="pager">
      <div class="clear-block">
        <xsl:if test="@total &gt; 1">
          <xsl:choose>
            <xsl:when test="@page &gt; 1">
              <a href="#" class="button pager-previous">
                <la:event name="click"><![CDATA[return %ref-object%.previousPage()]]></la:event>
                <xsl:text>&lt;&lt;</xsl:text>
              </a>
            </xsl:when>
            <xsl:otherwise>
              <span class="button pager-previous">&lt;&lt;</span>
            </xsl:otherwise>
          </xsl:choose>
          <span class="button pager-infos">
            <xsl:choose>
              <xsl:when test="@page &gt; 1">
                <a href="#" title="Revenir à la première page" class="pager-current">
                  <la:event name="click"><![CDATA[return %ref-object%.updatePage(1)]]></la:event>
                  <xsl:value-of select="@page"/>
                </a>
              </xsl:when>
              <xsl:otherwise>
                <span class="pager-current">
                  <xsl:value-of select="@page"/>
                </span>
              </xsl:otherwise>
            </xsl:choose>
            <span class="pager-separator">/</span>
            <xsl:choose>
              <xsl:when test="@page != @total">
                <a href="#" class="pager-total" title="Aller à la dernière page">
                  <la:event name="click"><![CDATA[return %ref-object%.lastPage()]]></la:event>
                  <xsl:value-of select="@total"/>
                </a>
              </xsl:when>
              <xsl:otherwise>
                <span class="pager-total">
                  <xsl:value-of select="@total"/>
                </span>
              </xsl:otherwise>
            </xsl:choose>
          </span>
          <xsl:choose>
            <xsl:when test="@page != @total">
              <a href="#" class="button pager-next">
                <la:event name="click"><![CDATA[return %ref-object%.nextPage()]]></la:event>
                <xsl:text>&gt;&gt;</xsl:text>
              </a>
            </xsl:when>
            <xsl:otherwise>
              <span class="button pager-next">&gt;&gt;</span>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:if>
      </div>
    </div>
  </xsl:template>
</xsl:stylesheet>
