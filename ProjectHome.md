### Welcome ###

Sylma is a XML programming language and a web components library written in PHP, it allows easy web developement with XML document organised in directory tree, then compiled into PHP script to insure best performances.

**WARNING : There is actually no stable release. Please, contact main author for support**

The main principles are :
  * Edit all your site from a web interface
  * Use one type of datas that need only one interface to be updated

Our choice has been naturally turned to XML, for his large implementation on the web and his good support on PHP.

## Installation ##
**Pre-Requested** is an apache PHP Server with the following extensions enabled: DOM, XSLT, Rewrite, GD2 and the _.htaccess_ use right.
In major cases, only XSLT and Rewrite need to be explicitly activated.

To begin, you need the following **file structure** to run Sylma.

Additional recommanded modules like the XML Editor are not yet online, ask the author to get it.

`*` _optional to run, but should be copied and adapted for a complete installation_

```
+ (root) // any name
  - .htaccess
  - index.php
  - server-config.yml * // local server configuration
  + (protected) // resources within are protected by apache and calls are transfered to Sylma
    - .htaccess
    - index.eml
    - (sylma) // main library directory
```

## Supported technologies ##
  * [XML](http://www.w3.org/TR/xml/), [Namespaces](http://www.w3.org/TR/xml-names/)
  * [XPath 1.0](http://xmlfr.org/w3c/TR/xpath/)
  * [XSLT 1.0](http://www.w3.org/TR/xslt) & [EXSLT](http://www.exslt.org/)
  * **WIP** [W3C XML Schema](http://www.w3.org/XML/Schema) 1.1 ([structures](http://www.w3.org/TR/xmlschema11-1/) & [datatypes](http://www.w3.org/TR/xmlschema11-2/))
  * [MySQL relational DB](http://www.mysql.com/)
## Features ##

**Actions** are the beginning of all :
  * First of all, an action can produce simple XML code (like HTML).
  * Call others actions, for actions aggregation.
  * Build PHP object and call their methods with controlled arguments
  * Control arguments sent to the actions with Class or base type limitations
  * Call other XML parser for customized element with own namespace.
  * Use variables inside the actions

**Security** files and elements defines permissions on files or elements, with a **group**, an **owner** and a 3 numbers **mode**. Like a basic Unix system. The rights are applied in cascading.

**JS Binder** is a _processor_ that let the developper define elements that will act as Javascript's anchors in the HTML code. Used to generate Javascript interfaces using [MooTools Framework](http://mootools.net/).

**Schemas** can be used to generate tables, the [CRUD](http://en.wikipedia.org/wiki/Crud) and any view needed with form elements, js binding and many more.

**DOM API** for an easy use of basic DOM manipulation, including XPath and XSL.