<?xml version="1.0" encoding="utf-8"?>
<crud:crud
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

  <crud:route groups="list">

    <view:view mode="view" groups="view" _debug="x">


    </view:view>

    <!-- Internal list -->

    <view:view name="list" _debug="x">

      <crud:import>pager.tpl</crud:import>

      <view:template>
        <tpl:apply mode="internal"/>
      </view:template>

    </view:view>

  </crud:route>

  <crud:route name="insert" groups="form">

    <view:view mode="hollow" groups="view">

      <view:template mode="init">
        <tpl:token name="action"><le:path/>/insert/do.json</tpl:token>
        <tpl:apply mode="title"/>
      </view:template>

    </view:view>

    <view:view name="do" mode="insert"/>

  </crud:route>

  <crud:route name="update" groups="form">

    <view:view mode="view" _debug="x" groups="view">

      <view:template match="*" mode="container">

        <tpl:argument name="alias" default="alias()"/>
        <tpl:argument name="title" default="title()"/>
        <tpl:argument name="type" default="'text'"/>
        <tpl:argument name="value" default="value()"/>

        <tpl:apply mode="container">
          <tpl:read tpl:name="alias" select="$alias"/>
          <tpl:read tpl:name="title" select="$title"/>
          <tpl:read tpl:name="type" select="$type"/>
          <tpl:read tpl:name="value" select="$value"/>
        </tpl:apply>

      </view:template>

      <view:template mode="init">
        <tpl:token name="action"><le:path/>/update/do.json</tpl:token>
        <sql:filter name="id"><le:get-argument name="id" escape="x"/></sql:filter>
        <js:option name="id"><tpl:read select="id"/></js:option>
        <input type="hidden" name="{id/alias()}" value="{id/value()}"/>
        <tpl:apply mode="title"/>
      </view:template>

      <view:template match="sql:foreign" mode="input" sql:ns="ns">
        <tpl:apply mode="select-test"/>
      </view:template>

    </view:view>

    <view:view name="do" mode="update">
      <sql:filter name="id" single="single"><le:get-argument name="id" source="post" escape="x"/></sql:filter>
    </view:view>

  </crud:route>
  <!--<view:view mode="delete"/>-->

  <crud:global ssd:ns="ns">

    <tpl:constant name="form-cols">* ^ id,date-update,date-insert</tpl:constant>
    <tpl:constant name="list-cols">*</tpl:constant>
    <tpl:constant name="list-order">id</tpl:constant>

  </crud:global>

  <crud:group name="view">

    <js:include>/#sylma/template/crud.js</js:include>

  </crud:group>

  <crud:group name="form">

    <sql:resource/>
    <crud:import>form.tpl</crud:import>

  </crud:group>

  <crud:group name="list">

    <sql:resource multiple="multiple"/>
    <crud:import>list.tpl</crud:import>

  </crud:group>

</crud:crud>
