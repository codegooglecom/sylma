<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/view/test/sample2"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema"
>

  <table name="group">
    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>
    <reference name="users" table="user:user2" foreign="user:group_id" import="user6.xql"/>
  </table>

</schema>

