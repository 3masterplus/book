---
layout: post
title: Art of Android Development Reading Notes 1
categories: android
date: 2015-11-29 23:07:49
---
《Android开发艺术探索》读书笔记 (1) 第1章 Activity的生命周期和启动模式 <!--more-->

本节和《Android群英传》中的**第8章Activity和Activity调用栈分析**有关系，[建议先阅读该章的总结](https://hujiaweibujidao.github.io/blog/2015/11/28/Android-Heros-Reading-Notes-4/)

### **第1章 Activity的生命周期和启动模式**
#### 1.1 Activity生命周期全面分析
1.1.1 典型情况下生命周期分析
(1)一般情况下，当当前Activity从不可见重新变为可见状态时，`onRestart`方法就会被调用。
(2)当用户打开新的Activity或者切换到桌面的时候，回调如下：`onPause` -> `onStop`，但是如果新Activity采用了透明主题，那么`onStop`方法不会被回调。当用户再次回到原来的Activity时，回调如下：`onRestart` -> `onStart` -> `onResume`。
(3)`onStart`和`onStop`对应，它们是从Activity是否可见这个角度来回调的；`onPause`和`onResume`方法对应，它们是从Activity是否位于前台这个角度来回调的。
(4)从Activity A进入到Activity B，回调顺序是`onPause(A) -> onCreate(B) -> onStart(B) -> onResume(B) -> onStop(A)`，所以不能在onPause方法中做重量级的操作。

1.1.2 异常情况下生命周期分析
(1)`onSaveInstanceState`方法只会出现在Activity被异常终止的情况下，它的调用时机是在`onStop`之前，它和`onPause`方法没有既定的时序关系，可能在它之前，也可能在它之后。当Activity被重新创建的时候，`onRestoreInstanceState`会被回调，它的调用时机是`onStart`之后。
**系统只会在Activity即将被销毁并且有机会重新显示的情况下才会去调用onSaveInstanceState方法。**
**当Activity在异常情况下需要重新创建时，系统会默认为我们保存当前Activity的视图结构，并且在Activity重启后为我们恢复这些数据，比如文本框中用户输入的数据、listview滚动的位置等，这些view相关的状态系统都会默认为我们恢复。具体针对某一个view系统能为我们恢复哪些数据可以查看view的源码中的onSaveInstanceState和onRestoreInstanceState方法。**
(2)Activity按优先级的分类
前台Activity；可见但非前台Activity；后台Activity
(3)`android:configChanges="xxx"`属性，常用的主要有下面三个选项：
`local`：设备的本地位置发生了变化，一般指切换了系统语言；
`keyboardHidden`：键盘的可访问性发生了变化，比如用户调出了键盘；
`orientation`：屏幕方向发生了变化，比如旋转了手机屏幕。
配置了`android:configChanges="xxx"`属性之后，Activity就不会在对应变化发生时重新创建，而是调用Activity的`onConfigurationChanged`方法。

#### 1.2 Activity的启动模式
1.2.1 启动模式
(1)当任务栈中没有任何Activity的时候，系统就会回收这个任务栈。
(2)从非Activity类型的Context(例如ApplicationContext、Service等)中以`standard`模式启动新的Activity是不行的，因为这类context并没有任务栈，所以需要为待启动Activity指定`FLAG_ACTIVITY_NEW_TASK`标志位。
(3)任务栈分为前台任务栈和后台任务栈，后台任务栈中的Activity位于暂停状态，用户可以通过切换将后台任务栈再次调到前台。
(4)参数`TaskAffinity`用来指定Activity所需要的任务栈，意为任务相关性。默认情况下，所有Activity所需的任务栈的名字为应用的包名。TaskAffinity属性主要和`singleTask`启动模式或者`allowTaskReparenting`属性配对使用，在其他情况下没有意义。
当TaskAffinity和singleTask启动模式配对使用的时候，它是具有该模式的Activity的目前任务栈的名字，待启动的Activity会运行在名字和TaskAffinity相同的任务栈中；
当TaskAffinity和allowTaskReparenting结合的时候，当一个应用A启动了应用B的某个Activity C后，如果Activity C的allowTaskReparenting属性设置为true的话，那么当应用B被启动后，系统会发现Activity C所需的任务栈存在了，就将Activity C从A的任务栈中转移到B的任务栈中。
(5)singleTask模式的具体分析：当一个具有singleTask启动模式的Activity请求启动之后，系统首先会寻找是否存在A想要的任务栈，如果不存在，就重新创建一个任务栈，然后创建Activity的实例把它放到栈中；如果存在Activity所需的任务栈，这时候要看栈中是否有Activity实例存在，如果有，那么系统就会把该Activity实例调到栈顶，并调用它的onNewIntent方法(它之上的Activity会被迫出栈，所以**singleTask模式具有FLAG_ACTIVITY_CLEAR_TOP效果**)；如果Activity实例不存在，那么就创建Activity实例并把它压入栈中。
(6)设置启动模式既可以使用xml属性`android:launchMode`，也可以使用代码`intent.addFlags()`。**区别在于限定范围不同，前者无法直接为Activity设置FLAG_ACTIVITY_CLEAR_TOP标识，而后者无法为Activity指定singleInstance模式。**

1.2.2 Activity的Flags
`FLAG_ACTIVITY_NEW_TASK`,`FLAG_ACTIVITY_SINGLE_TOP`,`FLAG_ACTIVITY_CLEAR_TOP`
`FLAG_ACTIVITY_EXCLUDE_FROM_RECENTS`：具有这个标记的Activity不会出现在历史Activity列表中，当某些情况下我们不希望用户通过历史列表回到我们的Activity的时候这个标记比较有用，它等同于属性设置`android:excludeFromRecents="true"`。

#### 1.3 IntentFilter的匹配规则
(1)IntentFilter中的过滤信息有action、category、data，为了匹配过滤列表，需要同时匹配过滤列表中的action、category、data信息，否则匹配失败。**一个过滤列表中的action、category、data可以有多个，所有的action、category、data分别构成不同类别，同一类别的信息共同约束当前类别的匹配过程。只有一个Intent同时匹配action类别、category类别和data类别才算完全匹配，只有完全匹配才能成功启动目标Activity。此外，一个Activity中可以有多个intent-filter，一个Intent只要能匹配任何一组intent-filter即可成功启动对应的Activity。**
```
<intent-filter>
    <action android:name="com.ryg.charpter_1.c" />
    <action android:name="com.ryg.charpter_1.d" />

    <category android:name="com.ryg.category.c" />
    <category android:name="com.ryg.category.d" />
    <category android:name="android.intent.category.DEFAULT" />

    <data android:mimeType="text/plain" />
</intent-filter>
```
(2)action匹配规则
只要Intent中的action能够和过滤规则中的任何一个action相同即可匹配成功，action匹配区分大小写。
(3)category匹配规则
Intent中如果有category那么所有的category都必须和过滤规则中的其中一个category相同，如果没有category的话那么就是默认的category，即`android.intent.category.DEFAULT`，所以为了Activity能够接收隐式调用，配置多个category的时候必须加上默认的category。
(4)data匹配规则
data的结构很复杂，语法大致如下：
```
<data android:scheme="string"
  android:host="string"
  android:port="string"
  android:path="string"
  android:pathPattern="string"
  android:pathPrefix="string"
  android:mimeType="string" />
```
主要由`mimeType`和`URI`组成，其中mimeType代表媒体类型，而URI的结构也复杂，大致如下：
`<scheme>://<host>:<port>/[<path>]|[<pathPrefix>]|[pathPattern]`
例如`content://com.example.project:200/folder/subfolder/etc`
`scheme、host、port`分别表示URI的模式、主机名和端口号，其中如果scheme或者host未指定那么URI就无效。
`path、pathPattern、pathPrefix`都是表示路径信息，其中path表示完整的路径信息，pathPrefix表示路径的前缀信息；pathPattern表示完整的路径，但是它里面包含了通配符(*)。

**data匹配规则：Intent中必须含有data数据，并且data数据能够完全匹配过滤规则中的某一个data。**
**URI有默认的scheme！**
如果过滤规则中的mimeType指定为`image/*`或者`text/*`等这种类型的话，那么即使过滤规则中没有指定URI，URI有默认的scheme是content和file！如果过滤规则中指定了scheme的话那就不是默认的scheme了。
```
//URI有默认值
<intent-filter>
    <data android:mimeType="image/*"/>
    ...
</intent-filter>
//URI默认值被覆盖
<intent-filter>
    <data android:mimeType="image/*" android:scheme="http" .../>
    ...
</intent-filter>
```

**如果要为Intent指定完整的data，必须要调用`setDataAndType`方法！**
不能先调用setData然后调用setType，因为这两个方法会彼此清除对方的值。
```
intent.setDataAndType(Uri.parse("file://abc"), "image/png");
```

data的下面两种写法作用是一样的：
```
<intent-filter>
    <data android:scheme="file" android:host="www.github.com"/>
</intent-filter>

<intent-filter>
    <data android:scheme="file"/>
    <data android:host="www.github.com"/>
</intent-filter>
```

**如何判断是否有Activity能够匹配我们的隐式Intent？**
(1)`PackageManager`的`resolveActivity`方法或者Intent的`resolveActivity`方法：如果找不到就会返回null
(2)PackageManager的`queryIntentActivities`方法：它返回所有成功匹配的Activity信息
针对Service和BroadcastReceiver等组件，PackageManager同样提供了类似的方法去获取成功匹配的组件信息，例如`queryIntentServices`、`queryBroadcastReceivers`等方法

有一类action和category比较重要，它们在一起用来标明这是一个入口Activity，并且会出现在系统的应用列表中。
```
<intent-filter>
    <action android:name="android.intent.action.MAIN" />
    <category android:name="android.intent.category.LAUNCHER" />
</intent-filter>
```

OK，本章结束，谢谢阅读。


