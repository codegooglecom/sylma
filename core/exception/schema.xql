<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/core/exception"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
>

  <table name="exception">
    <field name="id" type="sql:id"/>
    <field name="message" type="sql:string"/>
    <field name="message_html" type="sql:html"/>
    <field name="context" type="sql:html"/>
    <field name="backtrace" type="sql:html"/>
    <field name="session" type="sql:string-long"/>
    <field name="request" type="sql:string-long"/>
    <field name="server" type="sql:string-long"/>
    <field name="files" type="sql:string-long"/>
    <field name="archive" type="sql:boolean" default="null"/>
    <field name="insert" type="sql:datetime"/>
  </table>

</schema>

