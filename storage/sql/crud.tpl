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

    <view:view mode="view" _debug="x">

      <sql:resource multiple="multiple"/>

      <view:template>
        <div>
          <a>
            <tpl:token name="href">
              <le:path/>/insert
            </tpl:token>
            Insert
          </a>
          <a ls:owner="root" ls:group="admin" ls:mode="700">
            <tpl:token name="href">
              <le:path>/sylma/storage/sql/alter</le:path>?path=<view:get-schema/>
            </tpl:token>
            Structure
          </a>
          <table js:class="sylma.ui.Base">
            <tpl:apply select="static()" mode="row"/>
            <crud:include path="list"/>
          </table>
        </div>
      </view:template>

      <view:template match="*" mode="row">
        <thead>
          <tr>
            <th></th>
            <tpl:apply use="list-cols" mode="cell"/>
          </tr>
        </thead>
      </view:template>

      <view:template match="*" mode="cell">
        <th>
          <a href="#" js:class="sylma.ui.Base">
            <js:option name="name"><tpl:apply select="alias()"/></js:option>
            <js:event name="click">
              %parent%.getObject('container').update({order : %object%.get('name')});
              return false;
            </js:event>
            <tpl:apply select="alias()"/>
          </a>
        </th>
      </view:template>

    </view:view>

    <view:view name="list" _debug="x">

      <sql:resource multiple="multiple"/>

      <view:template mode="init">

        <tpl:apply mode="init-pager"/>

        <le:check-argument name="order" format="string">
          <le:default>
            <tpl:apply select="$$list-order"/>
          </le:default>
        </le:check-argument>

        <sql:order>
          <le:argument name="order"/>
        </sql:order>

      </view:template>

      <view:template>
        <tpl:apply mode="init"/>
        <tbody js:name="container" js:class="sylma.ui.Container">
          <js:option name="path">
            <crud:path/>
          </js:option>
          <tpl:apply select="*" mode="row"/>
          <tr>
            <td colspan="99">
              <tpl:apply select="pager()"/>
            </td>
          </tr>
        </tbody>
      </view:template>

      <view:template match="*" mode="row">
        <tr js:class="sylma.ui.Base">
          <td>
            <a title="Editer">
              <tpl:token name="href">
                <le:path/>/update?id=<tpl:read select="id"/>
              </tpl:token>
              E
            </a>
          </td>
          <tpl:apply use="list-cols" mode="cell"/>
        </tr>
      </view:template>

      <view:template match="*" mode="cell">
        <td>
          <tpl:apply/>
        </td>
      </view:template>

      <crud:import>pager.tpl</crud:import>

    </view:view>

  </crud:route>

  <crud:route name="insert" groups="form">

    <view:view mode="hollow">

      <view:template mode="root">
        <tpl:token name="action"><le:path/>/insert/do.json</tpl:token>
      </view:template>

    </view:view>

    <view:view name="do" mode="insert"/>

  </crud:route>

  <crud:route name="update" groups="form">

    <view:view mode="view" _debug="x">

      <view:template mode="root">
        <tpl:token name="action"><le:path/>/update/do.json</tpl:token>
        <sql:filter name="id"><le:argument name="id" escape="x"/></sql:filter>
        <js:option name="id"><tpl:read select="id"/></js:option>
        <input type="hidden" name="{id/alias()}" value="{id/value()}"/>
      </view:template>

      <view:template match="sql:foreign" mode="input" sql:ns="ns">
        <tpl:apply mode="select-test"/>
      </view:template>

    </view:view>

    <view:view name="do" mode="update">
      <sql:filter name="id" single="single"><le:argument name="id" source="post" escape="x"/></sql:filter>
    </view:view>

  </crud:route>
  <!--<view:view mode="delete"/>-->

  <crud:global ssd:ns="ns">

    <tpl:constant name="form-cols">* ^ id,date-update,date-insert</tpl:constant>
    <tpl:constant name="list-cols">*</tpl:constant>
    <tpl:constant name="list-order">id</tpl:constant>

  </crud:global>

  <crud:group name="form">

    <sql:resource/>

    <crud:import>form.tpl</crud:import>

  </crud:group>

  <crud:group name="list">

    <view:template match="sql:foreign">
      <tpl:apply select="ref()"/>
    </view:template>

  </crud:group>

</crud:crud>
