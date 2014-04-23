<?xml version="1.0" encoding="utf-8"?>
<view:view
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"

  xmlns:cap="http://2013.sylma.org/modules/captcha"
>

  <tpl:template match="*" mode="captcha/view">
    <tpl:apply reflector="Reflector"/>
  </tpl:template>

  <tpl:template match="*" mode="captcha/register">
    <tpl:apply reflector="Reflector" mode="register"/>
  </tpl:template>

  <tpl:template match="cap:root" mode="register">
    <tpl:register/>
  </tpl:template>

  <tpl:template match="cap:root">
    <div id="sylma-captcha">
      <img alt="Test visuel">
        <tpl:token name="src">
          <le:path>image</le:path>
          <tpl:text>.png</tpl:text>
        </tpl:token>
      </img>
      <tpl:apply select="element()" mode="container/empty"/>
    </div>
  </tpl:template>

</view:view>
