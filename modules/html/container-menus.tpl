<?xml version="1.0" encoding="utf-8"?>
<view:view
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:xl="http://2013.sylma.org/storage/xml"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"

  xmlns:menus="http://2013.sylma.org/modules/menus"
>

  <tpl:import>/#sylma/modules/menus/main.tpl</tpl:import>

  <tpl:template match="menus:menus">
    <ul id="{@id}" class="clearfix">
      <tpl:apply select="*"/>
    </ul>
  </tpl:template>

  <tpl:template match="menus:menu" mode="content">
    <a href="{@href}">
      <span class="title"><tpl:read select="@title"/></span>
      <span class="description"><tpl:read select="description"/></span>
    </a>
  </tpl:template>

</view:view>
