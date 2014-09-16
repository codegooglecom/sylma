<?xml version="1.0" encoding="utf-8"?>
<schema
  xmlns="http://2013.sylma.org/storage/sql"
  xmlns:xs="http://www.w3.org/2001/XMLSchema"
  xmlns:ssd="http://2013.sylma.org/schema/ssd"

  xmlns:stat="http://2013.sylma.org/modules/todo/statut"
  xmlns:proj="http://2013.sylma.org/modules/todo/project"
  xmlns:user="http://2013.sylma.org/modules/users"

  targetNamespace="http://2013.sylma.org/modules/todo/tag"
>

  <table name="todo_tag" connection="common">

    <field name="id" type="sql:id"/>

    <field name="name" type="sql:string-short"/>
    <foreign name="project" occurs="0..1" table="proj:todo_project" import="project.xql"/>
    <foreign name="todo" occurs="1..1" table="todo:todo" import="todo.xql"/>

  </table>

</schema>

