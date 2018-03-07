---
layout: post
title: Art of Android Development Reading Notes 9
categories: android
date: 2015-12-05 11:50:54
---
《Android开发艺术探索》读书笔记 (9) 第9章 四大组件的工作过程  <!--more-->

### 第9章 四大组件的工作过程
#### 9.1 四大组件的运行状态
(1)四大组件中只有`BroadcastReceiver`既可以在AndroidManifest文件中注册，也可以在代码中注册，其他三个组件都必须在AndroidManifest文件中注册；`ContentProvider`的调用不需要借助Intent，其他三个组件都需要借助Intent。
(2)Activity是一种展示型组件，用于向用户展示界面，可由显式或者隐式Intent来启动。
(3)Service是一种计算型组件，用于在后台执行计算任务。尽管service是用于后台执行计算的，但是它本身是运行在主线程中的，因此耗时的后台计算仍然需要在单独的线程中去完成。Service组件有两种状态：启动状态和绑定状态。当service处于绑定状态时，外界可以很方便的和service进行通信，而在启动状态中是不可与外界通信的。
(4)BroadcastReceiver是一种消息型组件，用于在不同的组件乃至不同的应用之间传递消息，它工作在系统内部。广播有两种注册方式：静态注册和动态注册。静态注册是在AndroidManifest中注册，在应用安装的时候会被系统解析，这种广播不需要应用启动就可以收到相应的广播。动态注册需要通过`Context.registerReceiver()`来注册，这种广播需要应用启动才能注册并接收广播。BroadcastReceiver组件一般来说不需要停止，它也没有停止的概念。
(5)ContentProvider是一种数据共享型组件，用于向其他组件乃至其他应用共享数据。ContentProvider中的`insert`、`delete`、`update`、`query`方法需要处理好线程同步，因为这几个方法是在Binder线程池中被调用的，另外ContentProvider组件也不需要手动停止。

**[下面对四大组件的工作过程的总结需要感谢[`amurocrash`童鞋的读书笔记](http://blog.csdn.net/amurocrash/article/details/48858353)以及他细心制作的UML图，帮助我从原书复杂的方法调用中跳出来看到整体的大致流程]**

#### 9.2 Activity的工作过程
(1)Activity启动的大致流程
![img](https://hujiaweibujidao.github.io/images/androidart_activity.png)
(2)`ApplicationThread`是`ActivityThread`的一个内部类，它继承自`ApplicationThreadNative`，而`ApplicationThreadNative`继承自`Binder`并实现了`IApplicationThread`接口，`ApplicationThreadNative`的作用其实就和系统为AIDL文件生成的类是一样的。
(3)`ActivityManagerService`(AMS)继承自`ActivityManagerNative`，而`ActivityManagerNative`继承自`Binder`并实现了`IActivityManager`这个Binder接口，因此AMS也是一个Binder。
(4)一个应用只有一个Application对象，它的创建也是通过`Instrumentation`来完成的，这个过程和Activity对象的创建过程一样，都是通过类加载器来实现的。
(5)`ContextImpl`是Context的具体实现，ContextImpl是通过Activity的`attach`方法来和Activity建立关联的，在`attach`方法中Activity还会完成Window的创建并建立自己和Window的关联，*这样当window接收到外部输入事件后就可以将事件传递给Activity*。 **[这里可能有误，应该是Activity将事件传递给window]**

#### 9.3 Service的工作过程
(1)Service有两种状态：启动状态和绑定状态，两种状态是可以共存的。
启动过程：
![img](https://hujiaweibujidao.github.io/images/androidart_service1.png)
绑定过程：
![img](https://hujiaweibujidao.github.io/images/androidart_service2.png)

#### 9.4 BroadcastReceiver的工作过程
(1)BroadcastReceiver的工作过程包括广播注册过程、广播发送和接收过程。
注册过程：静态注册的时候是由`PackageManagerService`来完成整个注册过程，下面是动态注册的过程
![img](https://hujiaweibujidao.github.io/images/androidart_broadcastreceiver1.png)
发送和接收过程：
![img](https://hujiaweibujidao.github.io/images/androidart_broadcastreceiver1.png)
(2)广播的发送有几种类型：普通广播、有序广播和粘性广播，有序广播和粘性广播与普通广播相比具有不同的特性，但是发送和接收过程是类似的。
(3)一个应用处于停止状态分为两种情况：一是应用安装后未运行；二是应用被手动或者其他应用强停了。从Android 3.1开始，处于停止状态的应用无法接受到开机广播。

#### 9.5 ContentProvider的工作过程
(1)当ContentProvider所在的进程启动的时候，它会同时被启动并被发布到AMS中，这个时候它的onCreate要先去Application的onCreate执行。
(2)ContentProvider的启动过程：
1.当一个应用启动时，入口方法是`ActivityThread`的`main`方法，其中创建ActivityThread的实例并创建主线程的消息队列；
2.`ActivityThread`的`attach`方法中会远程调用`ActivityManagerService`的`attachApplication`，并将`ApplicationThread`提供给AMS，ApplicationThread主要用于ActivityThread和AMS之间的通信；
3.`ActivityManagerService`的`attachApplication`会调用`ApplicationThread`的`bindApplication`方法，这个方法会通过`H`切换到ActivityThread中去执行，即调用`handleBindApplication`方法；
4.`handleBindApplication`方法会创建Application对象并加载ContentProvider，注意是先加载ContentProvider，然后调用Application的`onCreate`方法。
(3)ContentProvider的`android:multiprocess`属性决定它是否是单实例，默认值是false，也就是默认是单实例。当设置为true时，每个调用者的进程中都存在一个ContentProvider对象。
(4)当调用ContentProvider的`insert`、`delete`、`update`、`query`方法中的任何一个时，如果ContentProvider所在的进程没有启动的话，那么就会触发ContentProvider的创建，并伴随着ContentProvider所在进程的启动。下图是ContentProvider的query操作的大致过程：
![img](https://hujiaweibujidao.github.io/images/androidart_contentprovider.png)

详细的过程分析建议阅读原书，简直精彩！

**其他学习资料**
1.[Android开发艺术探索读书笔记（三）](http://blog.csdn.net/amurocrash/article/details/48858353)

OK，本章结束，谢谢阅读。


