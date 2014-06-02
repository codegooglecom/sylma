<?xml version="1.0" encoding="utf-8"?>
<schema
  targetNamespace="http://2013.sylma.org/core/user"
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"

  xmlns:group="http://2013.sylma.org/core/user/group"
>

  <table name="user" reflector="\sylma\modules\users\Form">
    <field name="id" type="sql:id"/>
    <field name="name" title="name" type="sql:string"/>
    <field name="password" title="password" type="ssd:password"/>
    <foreign name="groups" occurs="0..n" table="group:group" import="group.xql" junction="user_group"/>
  </table>

</schema>

