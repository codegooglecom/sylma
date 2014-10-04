<?xml version="1.0" encoding="utf-8"?>
<view:view
  xmlns:view="http://2013.sylma.org/view"
  xmlns="http://www.w3.org/1999/xhtml"
  xmlns:tpl="http://2013.sylma.org/template"
  xmlns:xl="http://2013.sylma.org/storage/xml"
  xmlns:js="http://2013.sylma.org/template/binder"
  xmlns:le="http://2013.sylma.org/action"
  xmlns:build="http://2013.sylma.org/parser/reflector/builder"

  xmlns:cls="http://2013.sylma.org/core/factory"
  xmlns:test="http://2013.sylma.org/modules/stepper"
>

  <tpl:template mode="stepper/options">

    <js:option name="directory" cast="x">
      <tpl:apply select="getDirectory()"/>
    </js:option>
    <js:option name="collection">
      <tpl:apply select="getCollection()"/>
    </js:option>
    <js:option name="path" cast="x">
      <tpl:apply mode="path"/>
    </js:option>
    <js:option name="query">
      <le:path>query</le:path>
    </js:option>
    <js:option name="save">
      <le:path>save</le:path>
    </js:option>
    <js:option name="loadTest">
      <le:path>loadTest</le:path>
    </js:option>
    <js:option name="loadDirectory">
      <le:path>loadDirectory</le:path>
    </js:option>
    <js:option name="items">
      <tpl:apply select="getItems()"/>
    </js:option>

    <tpl:apply mode="stepper/options"/>

  </tpl:template>

  <tpl:template>

    <div id="tester" js:class="sylma.stepper.Main" js:parent-name="main">

      <tpl:apply mode="stepper/options"/>

      <div js:node="board" class="board">
        <div class="actions">
          <button class="record">
            <js:event name="click">%object%.record();</js:event>
            <span>●</span>
          </button>
          <button class="read">
            <js:event name="click">%object%.testAll(this);</js:event>
            <tpl:text>▶</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getRoot().test(null, %object%.getRoot().getCurrent());</js:event>
            <span>▹</span>
          </button>
          <button js:node="next">
            <js:event name="click">%object%.getRoot().goNext();</js:event>
            <span>↴</span>
          </button>
          <button>
            <js:event name="click">%object%.toggleWindow();</js:event>
            <tpl:text>◑</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.save();</js:event>
            <tpl:text>⇥</tpl:text>
          </button>
          <br/>
          <button>
            <js:event name="click">%object%.getRoot().createTest();</js:event>
            <tpl:text>✚</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().addPage();</js:event>
            <tpl:text>⚐</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().getPage().addWatcher();</js:event>
            <tpl:text>⌚</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().getPage().addSnapshot();</js:event>
            <tpl:text>⚀</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().getPage().addCall();</js:event>
            <tpl:text>✍</tpl:text>
          </button>
          <button>
            <js:event name="click">%object%.getTest().getPage().addQuery();</js:event>
            <tpl:text>♦</tpl:text>
          </button>
        </div>
        <tpl:apply select="steps" mode="collection"/>
      </div>
      <div class="window hidder visible" js:node="window">
        <div class="toolbar">
          <input type="text"/>
          <button>
            <js:event name="click">
              %object%.reload();
            </js:event>
            <tpl:text>↻</tpl:text>
          </button>
        </div>
        <iframe js:node="frame"/>
      </div>
    </div>

  </tpl:template>
<!--
  <tpl:template mode="error">
    <div class="error" js:class="sylma.ui.Template" js:alias="error">
      <strong>
        <tpl:read select="type"/>
      </strong> :
      <tpl:read select="message"/>
      <ul>
        <tpl:apply mode="error/path"/>
      </ul>
    </div>
  </tpl:template>

  <tpl:template mode="error/path" js:class="sylma.ui.Template" js:alias="path">
    <li>
      <tpl:read select="value"/>
    </li>
  </tpl:template>
-->

  <tpl:template mode="path">
    <le:path>/</le:path>
  </tpl:template>

  <tpl:template match="test:directory" mode="hollow">
    <tpl:apply select="*"/>
  </tpl:template>

  <tpl:template match="test:group" mode="hollow">
    <tpl:apply select="*"/>
  </tpl:template>

  <tpl:template match="test:group" mode="directory">
    <tpl:apply select="*" mode="hollow"/>
  </tpl:template>

  <tpl:template match="test:steps" mode="collection">

    <tpl:apply select="group" mode="directory"/>

    <div class="collection" js:class="sylma.stepper.Collection" js:alias="collection" js:parent-name="collection" js:all="x" js:autoload="x">
      <tpl:apply select="group"/>
      <tpl:apply select="group" mode="hollow"/>
    </div>

  </tpl:template>

  <tpl:template match="test:group">
    <div class="group" js:class="sylma.stepper.Group" js:alias="group" js:parent-name="group" js:all="x">
      <button class="edit">
        <js:event name="click">%object%.test();</js:event>
        <span>▷</span>
      </button>
      <button class="edit">
        <js:event name="click">%object%.toggleSelect();</js:event>
        <span>▶</span>
      </button>
      <h4>
        <tpl:read select="name"/>
      </h4>
      <div js:node="items" class="sylma-hidder zoom items">
        <tpl:apply select="*"/>
      </div>
    </div>
  </tpl:template>

  <tpl:template match="test:directory">
    <div js:class="sylma.stepper.Directory" js:alias="directory" class="directory" js:parent-name="directory">
      <button class="edit">
        <js:event name="click">%object%.test();</js:event>
        <span>▷</span>
      </button>
      <button class="edit">
        <js:event name="click">%object%.toggleSelect();</js:event>
        <span>▶</span>
      </button>
      <h4>
        <tpl:read select="path"/>
      </h4>
      <div js:node="items" class="sylma-hidder zoom items">
        <tpl:apply select="*"/>
      </div>
    </div>
  </tpl:template>

  <tpl:template match="test:test">
    <div js:class="sylma.stepper.Test" js:alias="test" class="test" js:parent-name="test">
      <js:option name="width">
        <tpl:read select="width"/>
        <tpl:read select="height"/>
      </js:option>
      <tpl:apply mode="rename"/>
      <button class="edit">
        <js:event name="click">%object%.test();</js:event>
        <span>▷</span>
      </button>
      <button class="edit">
        <js:event name="click">%object%.toggleSelect();</js:event>
        <span>▶</span>
      </button>
      <h4 js:node="name">
        <tpl:read select="file"/>
      </h4>
      <div js:node="pages" class="sylma-hidder zoom items pages">
        <tpl:apply select="*"/>
      </div>
    </div>
  </tpl:template>

  <tpl:template match="test:page">
    <ul js:class="sylma.stepper.Page" js:alias="page" class="page" js:parent-name="page">
      <tpl:apply mode="delete"/>
      <button class="edit">
        <js:event name="click">%object%.select(null, true);</js:event>
        <span>▶</span>
      </button>
      <tpl:apply mode="rename"/>
      <a href="{@url}" js:node="name">
        <js:event name="click">
          e.preventDefault();
          %object%.goURL();
        </js:event>
        <tpl:read select="@url"/>
      </a>
      <tpl:apply select="steps"/>
    </ul>
  </tpl:template>

  <tpl:template match="test:steps">
    <div js:class="sylma.ui.Template" js:alias="steps" js:all="x" js:autoload="x">
      <tpl:apply select="*"/>
    </div>
  </tpl:template>

  <tpl:template match="test:*" mode="title">
    <strong><tpl:read select="name()"/></strong>
  </tpl:template>

  <tpl:template match="test:*" mode="selector">
    <div js:class="sylma.stepper.Selector" js:alias="selector" class="selector clearfix" title="{@element}">
      <button js:node="toggle" class="sylma-hidder edit">
        <js:event name="click" arguments="e">%object%.activate(function(target) { this.changeElement(target); }.bind(%object%), e);</js:event>
        <tpl:text>◌</tpl:text>
      </button>
      <button type="button" class="sylma-hidder edit">
        <js:event name="click">%object%.selectNext();</js:event>
        <tpl:text>▹</tpl:text>
      </button>
      <button type="button" class="sylma-hidder edit">
        <js:event name="click">%object%.selectPrevious();</js:event>
        <tpl:text>◃</tpl:text>
      </button>
      <button type="button" class="sylma-hidder edit">
        <js:event name="click">%object%.selectChild();</js:event>
        <tpl:text>▿</tpl:text>
      </button>
      <button type="button" class="sylma-hidder edit">
        <js:event name="click">%object%.selectParent();</js:event>
        <tpl:text>▵</tpl:text>
      </button>
      <a title="{@element}" js:node="display">
        <tpl:read select="@element"/>
      </a>
    </div>
  </tpl:template>

  <tpl:template match="test:*" mode="actions">
    <tpl:apply mode="delete"/>
    <tpl:apply mode="edit"/>
  </tpl:template>

  <tpl:template match="test:*" mode="delete">
    <button type="button" js:class="sylma.ui.Template" js:alias="delete" js:autoload="x" class="delete">
      <js:event name="click">%parent%.remove(true);</js:event>
      <tpl:text>✕</tpl:text>
    </button>
  </tpl:template>

  <tpl:template match="test:*" mode="edit">
    <button js:class="sylma.ui.Template" js:alias="edit" js:autoload="x" class="edit">
      <js:event name="click">this.blur(); %parent%.go();</js:event>
      <span>▶</span>
    </button>
  </tpl:template>

  <tpl:template match="test:*" mode="refresh">
    <button type="button" js:class="sylma.ui.Template" js:alias="refresh" js:autoload="x" class="sylma-hidder edit refresh">
      <js:event name="click">%parent%.refresh();</js:event>
      <span>↻</span>
    </button>
  </tpl:template>

  <tpl:template match="test:*" mode="rename">
    <div js:class="sylma.ui.Template" js:alias="rename">
      <div class="sylma-hidder zoom name" js:node="input">
        <input type="text" value="{@file}">
          <js:event name="input">
            %parent%.setFile(this.get('value'));
          </js:event>
        </input>
      </div>
      <button class="edit">
        <js:event name="click">%object%.getNode('input').toggleClass('sylma-visible');</js:event>
        <tpl:text>...</tpl:text>
      </button>
    </div>
  </tpl:template>

  <tpl:template match="test:event">
    <li js:class="sylma.stepper.Event" js:alias="event">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <tpl:text> </tpl:text>
      <span>
        <tpl:read select="@name"/>
      </span>
      <tpl:apply mode="selector"/>
    </li>
  </tpl:template>

  <tpl:template match="test:watcher">
    <li js:class="sylma.stepper.Watcher" js:alias="watcher" class="watcher">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <tpl:apply mode="selector"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <button type="button">
          <js:event name="click">%object%.addProperty();</js:event>
          <tpl:text>+</tpl:text>
        </button>
        <button type="button">
          <js:event name="click">%object%.addVariable();</js:event>
          <tpl:text>$</tpl:text>
        </button>
        <button type="button">
          <js:event name="click">%object%.getNode('delay').toggleClass('sylma-visible');</js:event>
          <tpl:text>⌚</tpl:text>
        </button>
        <div js:node="delay" class="delay zoom sylma-hidder">
          <input type="text" value="{@delay}">
            <js:event name="input">
              %object%.setDelay(this.get('value'));
            </js:event>
          </input>
        </div>
        <tpl:apply select="property"/>
        <tpl:apply select="variable"/>
      </form>
    </li>
  </tpl:template>

  <tpl:template match="test:property">
    <div js:class="sylma.stepper.Property" js:alias="property" class="property">
      <select js:node="name">
        <js:event name="change">%object%.onChange()</js:event>
        <option>&lt;choose&gt;</option>
        <option>reload</option>
        <option>content</option>
        <option>display</option>
        <option>children</option>
        <option>class</option>
        <option>iframe</option>
        <optgroup label="style">
          <option>height</option>
          <option>width</option>
          <option>opacity</option>
          <option>z-index</option>
          <option>margin-top</option>
          <option>margin-right</option>
          <option>margin-bottom</option>
          <option>margin-left</option>
          <option>top</option>
          <option>right</option>
          <option>bottom</option>
          <option>left</option>
          <option>scroll</option>
        </optgroup>
      </select>
      <input type="text" js:node="value" value="{read()}">
        <js:event name="input">
          %object%.updateValue(this.get('value'));
        </js:event>
      </input>
      <tpl:apply mode="delete"/>
      <tpl:apply mode="refresh"/>
    </div>
  </tpl:template>

  <tpl:template match="test:snapshot">
    <li js:class="sylma.stepper.Snapshot" js:alias="snapshot">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="refresh"/>
      <tpl:apply mode="title"/>
      <tpl:apply mode="selector"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <button type="button">
          <js:event name="click">%object%.addExclude();</js:event>
          <tpl:text>^</tpl:text>
        </button>
        <div js:class="sylma.ui.Template" js:alias="excluder" js:autoload="x">
          <tpl:apply select="exclude"/>
        </div>
      </form>
    </li>
  </tpl:template>

  <tpl:template match="test:exclude">
    <tpl:apply mode="selector"/>
  </tpl:template>

  <tpl:template match="test:call">
    <li js:class="sylma.stepper.Call" js:alias="call">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <input type="text" js:node="path" value="{@path}"/>
        <button type="button">
          <js:event name="click">%object%.addVariable();</js:event>
          <tpl:text>$</tpl:text>
        </button>
        <input type="checkbox" js:node="method"/>
        <label> GET</label>
        <tpl:apply select="variable"/>
      </form>
    </li>
  </tpl:template>

  <tpl:template match="test:variable">
    <div class="variable" js:class="sylma.stepper.Variable" js:alias="variable">
      <tpl:apply mode="delete"/>
      <strong>variable</strong>
      <input type="text" js:node="name" value="{@name}"/>
    </div>
  </tpl:template>

  <tpl:template match="test:input">
    <li js:class="sylma.stepper.Input" js:alias="input">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <tpl:apply mode="selector"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <textarea js:node="input" type="text">
          <js:event name="keyup">
            %object%.updateElement();
          </js:event>
          <tpl:read/>
        </textarea>
      </form>
    </li>
  </tpl:template>

  <tpl:template match="test:query">
    <li js:class="sylma.stepper.Query" js:alias="query">
      <tpl:apply mode="actions"/>
      <tpl:apply mode="title"/>
      <form js:node="form" class="sylma-hidder zoom options">
        <input js:node="value" type="text" value="{read()}"/>
        <input js:node="creation" type="text" value="{@creation}"/>
        <input js:node="connection" type="text" value="{@connection}"/>
      </form>
    </li>
  </tpl:template>

</view:view>
