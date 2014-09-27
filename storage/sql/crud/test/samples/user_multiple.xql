<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/modules/stepper/test/user01"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
  xmlns:sql="http://2013.sylma.org/storage/sql"

  xmlns:group="http://2013.sylma.org/modules/stepper/test/group01"
>

  <table name="stepper_user_multiple" connection="test">
    <field name="id" type="sql:id"/>
    <field name="name" type="sql:string-short"/>
    <foreign name="group" occurs="0..n" table="group:sylma_stepper_group01" junction="stepper_user_group" import="group01.xql"/>
  </table>

</schema>

