<?xml version="1.0" encoding="utf-8"?>
<tpl:collection
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"

  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"
>

  <tpl:template match="*" mode="filter/argument">
    <sql:filter>
      <le:get-argument>
        <le:name>
          <tpl:read select="alias()"/>
        </le:name>
      </le:get-argument>
    </sql:filter>
  </tpl:template>

  <tpl:template match="*" mode="filter/post">
    <sql:filter>
      <le:get-argument source="post">
        <le:name>
          <tpl:read select="alias()"/>
        </le:name>
      </le:get-argument>
    </sql:filter>
  </tpl:template>

  <tpl:template match="*" mode="filter/dummy">
    <sql:filter>
      <tpl:read select="root()/dummy()/default()">
        <tpl:read select="name()"/>
      </tpl:read>
    </sql:filter>
  </tpl:template>

</tpl:collection>
