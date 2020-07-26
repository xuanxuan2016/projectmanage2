# 目录

* [命名规范](#1)
* [页面布局vue](#3)
* [页面布局小程序](#4)
* [代码格式](#2)

---

### <div id="1">命名规范</div>


对于页面中使用的dom，请遵循下面的规则：

> **1.dom的自定义属性名，自定义属性内容，class值都为小写，单词之间使用中划线连接（-）**
> ```
如【<div data-value='test-data-value' class='show-data'></div>】
> ```
> **2.dom的id都为小写，单词之间使用下划线连接（_）**
> ```
如【<div id='test_id'></div>】
> ```

---

### <div id="3">页面布局vue</div>

对于页面中html的归类，大致按照如下代码分类，具体项目可根据需求进行微调。

```
<div id="app"  v-cloak>
    <div class='page-search'>
        <!--用于页面上的查询条件-->
    </div>
    <div class='page-button'>
        <!--用于页面上的操作按钮-->
    </div>
    <div class='page-list'>
        <!--用于页面上的列表-->
    </div>
    <div class='page-pagination'>
        <!--用于页面上的列表的分页-->
    </div>
    <div class='page-content'>
        <!--用于页面上的非列表的内容-->
    </div>
    <div class='dialog'>
        <!--用于页面上的弹框-->
    </div>
    <div class="auth-button">
        <!--用于控制页面上的按钮与弹框按钮是否显示-->
    </div>
    <div class="auth-role">
        <!--用于存储当前用户的角色id-->
    </div>
</div>
```

---

### <div id="4">页面布局小程序</div>

对于页面中html的归类，大致按照如下代码分类，具体项目可根据需求进行微调。

```
<view class="container">
  <view class="page-search">
        <!--用于页面上的查询条件-->
  </view>
  <view class="page-header">
        <!--用于页面上header-->
  </view>
  <scroll-view class="page-list" scroll-y lower-threshold='30' bindscrolltolower="scrollDown">
        <!--用于页面上的列表-->
  </scroll-view>
  <view class="page-content">
        <!--用于页面上的非列表的内容-->
        <button bindtap='bindtap' data-eventname='test' data-id='1' class='login1'>测试</button>
        <input  type='number' maxlength='6' bindinput='bindinput' bindfocus='bindfocus' bindblur='bindblur' data-eventname='test'></input>
  </view>
  <view class="page-footer">
        <!--用于页面上footer-->
  </view>    
  <block class='dialog'>
        <!--用于页面上的弹框-->
        <!--弹框格式-->
        <block wx:if="{{dialog.is_show_pping_dialog}}">
            <view class="dialog_lay" catchtouchmove='true'></view>
            <view class="dialog_content">
            </view>
        </block>
  </block>
</view>
```

---

### <div id="2">代码格式</div>

为了使代码阅读起来流畅，请注意代码格式。

> 使用NetBeans开发工具时，可通过【右键->格式】来方便的格式化代码。使用其他IDE时，也请注意格式化代码。

<br>