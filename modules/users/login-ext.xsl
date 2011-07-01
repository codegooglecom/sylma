<xsl:stylesheet extension-element-prefixes="usr" xmlns:usr="http://www.sylma.org/modules/users/login" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" usr:ns="null">
  
  <xsl:param name="https"/>
  
  <xsl:template match="usr:*" mode="form-child">
    <xsl:attribute name="id">sylma-user-login</xsl:attribute>
  </xsl:template>
  <xsl:template match="usr:password" mode="input">
    <input type="password" class="field-input field-input-element" id="field-password" name="password" value=""/>
  </xsl:template>
  <xsl:template match="usr:*" mode="notice"/>
  <xsl:template match="usr:*" mode="actions">
    <div class="field-actions">
      <xsl:if test="not($https)">
        <button type="button" onclick="document.location = document.location.href.replace(/^http/, 'https');">
          Passer en mode sécurisé
        </button>
      </xsl:if>
      <input type="submit" value="Connexion"/>
    </div>
  </xsl:template>
  <xsl:template match="usr:*" mode="label">
    <xsl:param name="element"/>
    <xsl:param name="id"/>
    
    <label for="{$id}">
      <xsl:value-of select="lx:first-case(lc:element-get-title($element))"/>
      <xsl:if test="not(lc:element-is-boolean($element))"> : </xsl:if>
    </label>
    
  </xsl:template>
</xsl:stylesheet>
