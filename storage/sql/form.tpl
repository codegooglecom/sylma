<?xml version="1.0" encoding="utf-8"?>
<view:view
  xmlns:crud="http://2013.sylma.org/view/crud"
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:ls="http://2013.sylma.org/parser/security"
>

  <view:template>
    <form js:class="sylma.ui.Form">
      <js:include>/#sylma/template/form.js</js:include>
      <tpl:apply mode="root"/>
      <tpl:apply use="form-cols" mode="container"/>
      <input type="submit" value="Envoyer"/>
    </form>
  </view:template>

  <view:template match="*" mode="container">
    <tpl:register/>
    <div class="field clearfix">
      <label for="form-{alias()}"><tpl:apply select="alias()"/> :</label>
      <tpl:apply mode="input"/>
    </div>
  </view:template>

  <view:template match="*" mode="input">
    <input class="field-input field-input-element" type="text" id="form-{alias()}" value="{value()}" name="{alias()}"/>
  </view:template>

  <view:template match="sql:string-long" mode="input" sql:ns="ns">
    <textarea id="form-{alias()}" name="{alias()}" class="field-input field-input-element">
      <tpl:apply/>
    </textarea>
  </view:template>

  <view:template match="sql:foreign" mode="input" sql:ns="ns">
    <select id="form-{alias()}" name="{alias()}">
      <option value="0">&lt; Choisissez &gt;</option>
      <tpl:apply select="all()" mode="select"/>
    </select>
  </view:template>

  <view:template match="ssd:password" mode="input" ssd:ns="ns">
    <input class="field-input field-input-element" type="password" id="form-{alias()}" value="{value()}" name="{alias()}"/>
  </view:template>

</view:view>
