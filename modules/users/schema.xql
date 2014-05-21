<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/core/user"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"
>

  <table name="user" reflector="\sylma\modules\users\Form">
    <field name="id" type="sql:id"/>
    <field name="name" title="name" type="sql:string"/>
    <field name="password" title="password" type="ssd:password"/>
  </table>

</schema>

