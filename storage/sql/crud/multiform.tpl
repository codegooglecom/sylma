<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
>

  <tpl:template match="sql:table" mode="multi">

    <tpl:variable name="action">
      <tpl:apply mode="init/action"/>
    </tpl:variable>

    <form class="sylma-form" action="{$action}" method="post" js:class="sylma.crud.FormSub" js:parent-name="form">

      <js:include>/#sylma/crud/FormSub.js</js:include>
      <js:name>
        <tpl:read select="name()"/>
      </js:name>
      <js:option name="ajax">1</js:option>

      <tpl:apply mode="form/content"/>

    </form>

  </tpl:template>

  <tpl:template mode="tab">

    <tpl:argument name="title"/>
    <tpl:argument name="new" default="''"/>
    <tpl:argument name="path" default="''"/>
    <tpl:argument name="key" default="position()"/>

    <li js:class="sylma.crud.multiform.TabSub">

      <js:option name="path">
        <tpl:read select="$path"/>
      </js:option>
      <js:option name="key">
        <tpl:read select="$key"/>
      </js:option>

      <js:include>TabSub.js</js:include>

      <tpl:apply mode="tab/content">
        <tpl:read select="$title" tpl:name="title"/>
        <tpl:read select="$path" tpl:name="path"/>
      </tpl:apply>

    </li>

  </tpl:template>

  <tpl:template mode="tab/content">

    <tpl:argument name="title"/>
    <tpl:argument name="path"/>

    <a href="javascript:void(0)">
      <js:event name="click">
        %object%.go(e);
      </js:event>
      <tpl:read select="$title"/>
      <tpl:if test="$path">
        <span class="new">
          <js:event name="click" arguments="e">
            %object%.createItem(e);
          </js:event>
          <tpl:text>+</tpl:text>
        </span>
      </tpl:if>
    </a>

  </tpl:template>

</tpl:collection>
