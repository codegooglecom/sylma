<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:lem="http://www.sylma.org/action/monitor" xmlns="http://www.w3.org/1999/xhtml" xmlns:ld="http://www.sylma.org/directory" version="1.0">
  
  <xsl:template match="lem:controler">
    <div class="msg-actions">
      <xsl:apply-templates select="lem:action"/>
    </div>
  </xsl:template>
  
  <xsl:template match="lem:action">
    <div class="msg-action">
      <div class="msg-action-main">
        <xsl:if test="lem:sub-actions">
          <div class="msg-action-sub-weights">
            <xsl:apply-templates select="lem:stats/*" mode="normal">
              <xsl:with-param name="color" select="@sub-weight-color"/>
              <xsl:with-param name="sub">1</xsl:with-param>
            </xsl:apply-templates>
          </div>
        </xsl:if>
        <div class="msg-action-label">
          <a href="{$path-editor}?path={@path}">
            <xsl:value-of select="@path"/>
          </a>
        </div>
        <xsl:if test="lem:variables and lem:variables/*">
          <div class="msg-action-hidden msg-action-variables">
            <span class="msg-actions-hidden-count">
              <xsl:value-of select="count(lem:variables/*)"/>
            </span>
            <xsl:apply-templates select="lem:variables/*"/>
          </div>
        </xsl:if>
        <xsl:if test="lem:path and lem:path/*">
          <div class="msg-action-hidden msg-action-arguments">
            <span class="msg-actions-hidden-count">
              <xsl:value-of select="count(lem:path/*)"/>
            </span>
            <xsl:apply-templates select="lem:path/*"/>
          </div>
        </xsl:if>
        <xsl:if test="lem:files">
          <div class="msg-action-hidden msg-action-files">
            <span class="msg-actions-hidden-count">
              <xsl:value-of select="count(lem:files/ld:file)"/>
            </span>
            <xsl:apply-templates select="lem:files/*"/>
          </div>
        </xsl:if>
        <xsl:if test="lem:queries">
          <div class="msg-action-hidden msg-action-queries">
            <span class="msg-actions-hidden-count">
              <xsl:value-of select="count(lem:queries/*)"/>
            </span>
            <div>
              <xsl:for-each select="lem:queries/lem:query">
                <xsl:sort select="@count" data-type="number" order="descending"/>
                <span>
                  <xsl:value-of select="concat(@count, ' : ')"/>
                </span>
                <xsl:value-of select="."/>
                <br/>
              </xsl:for-each>
            </div>
          </div>
        </xsl:if>
      </div>
      <xsl:if test="lem:sub-actions">
        <div class="msg-action-sub clearfix">
          <xsl:for-each select="lem:sub-actions/lem:action">
            <div class="msg-action-weights">
              <xsl:apply-templates select="lem:stats/*" mode="sub"/>
            </div>
            <xsl:apply-templates select="."/>
          </xsl:for-each>
        </div>
      </xsl:if>
    </div>
  </xsl:template>
  
  <xsl:template match="lem:variable">
    <div>
      <span>
        <xsl:value-of select="concat(@name, ' : ')"/>
      </span>
      <xsl:copy-of select="."/>
    </div>
  </xsl:template>
  
  <xsl:template match="lem:argument">
    <div>
      <xsl:choose>
        <xsl:when test="@name">
          <span class="msg-action-argument-assoc">
            <xsl:value-of select="concat(@name, ' : ')"/>
          </span>
        </xsl:when>
        <xsl:otherwise>
          <span class="msg-action-argument-index">
            <xsl:value-of select="concat(@index, ' : ')"/>
          </span>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:copy-of select="."/>
    </div>
  </xsl:template>
  
  <xsl:template match="ld:file">
    <xsl:choose>
      <xsl:when test="@first-time">
        <a href="{$path-editor}?path={@full-path}">
          <xsl:value-of select="@full-path"/>
        </a>
      </xsl:when>
      <xsl:otherwise>
        <a href="{$path-editor}?path={@full-path}" class="old-file">
          <xsl:value-of select="@full-path"/>
        </a>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="lem:stat" mode="sub">
    <xsl:apply-templates select="." mode="normal">
      <xsl:with-param name="color" select="@weight-color"/>
    </xsl:apply-templates>
  </xsl:template>
  
  <xsl:template match="lem:stat" mode="normal">
    <xsl:param name="color" select="string('rgb(123, 123, 123)')"/>
    <xsl:param name="sub"/>
    <div style="background-color: {$color}" class="msg-action-stat msg-action-stat-{@name}">
      <xsl:attribute name="title">
        <xsl:value-of select="@name"/>
        <xsl:text> : </xsl:text>
        <xsl:choose>
          <xsl:when test="$sub">
            <xsl:value-of select="@value"/>
            <xsl:value-of select="concat(' (', @sub-value, ')')"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="@sub-value"/>
            <xsl:value-of select="concat(' (', @total-value, ')')"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:attribute>
      <span>
        <xsl:value-of select="substring(@name, 1, 1)"/>
      </span>
    </div>
  </xsl:template>
  
</xsl:stylesheet>
