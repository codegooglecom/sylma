<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/view/test/sample1"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"

  xmlns:group="http://2013.sylma.org/view/test/sample2"
>

  <table name="user7" connection="test">

    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>

    <foreign occurs="1..1" name="group_main" table="group:group" import="group2c.xql"/>
    <foreign occurs="1..1" name="group_sub" table="group:group" import="group2c.xql"/>

  </table>

</schema>

