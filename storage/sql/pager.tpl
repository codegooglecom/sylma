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

  <view:template mode="init-pager">

    <le:argument name="page" format="integer">
      <le:default>1</le:default>
    </le:argument>

    <sql:pager>
      <sql:current>
        <le:get-argument name="page"/>
      </sql:current>
      <sql:count>10</sql:count>
    </sql:pager>

  </view:template>

  <view:template match="sql:pager">

    <div class="pager" js:class="sylma.ui.Base">

      <tpl:if test="!is-multiple()">
        <tpl:token name="class">form-disable</tpl:token>
      </tpl:if>

      <div class="clearfix">

        <a href="#" title="Page précédente" class="button pager-previous">
          <tpl:if test="is-first()">
            <tpl:token name="class">form-disable</tpl:token>
          </tpl:if>
          <js:option name="prev">
            <tpl:apply select="previous"/>
          </js:option>
          <js:event name="click">
            %parent%.update({page : %object%.get('prev')});
            return false;
          </js:event>
          &lt;&lt;
        </a>

        <span class="button pager-infos">

          <a href="#" title="Première page" class="pager-current">
            <tpl:if test="is-first()">
              <tpl:token name="class">form-disable</tpl:token>
            </tpl:if>
            <js:event name="click">
              %parent%.update({page : 1});
              return false;
            </js:event>
            <tpl:read select="current"/>
          </a>

          <span class="pager-separator">/</span>

          <a href="#" title="Dernière page" class="pager-total">
            <tpl:if test="is-last()">
              <tpl:token name="class">form-disable</tpl:token>
            </tpl:if>
            <js:option name="last">
              <tpl:apply select="last"/>
            </js:option>
            <js:event name="click">
              %parent%.update({page : %object%.get('last')});
              return false;
            </js:event>
            <tpl:read select="last"/>
          </a>

        </span>

        <a href="#" title="Page suivante" class="button pager-next">
          <tpl:if test="is-last()">
            <tpl:token name="class">form-disable</tpl:token>
          </tpl:if>
          <js:option name="next">
            <tpl:apply select="next"/>
          </js:option>
          <js:event name="click">
            %parent%.update({page : %object%.get('next')});
            return false;
          </js:event>
          &gt;&gt;
        </a>
      </div>
    </div>

  </view:template>

</view:view>
