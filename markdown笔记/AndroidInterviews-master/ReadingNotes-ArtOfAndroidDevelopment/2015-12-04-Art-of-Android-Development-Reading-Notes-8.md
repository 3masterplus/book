---
layout: post
title: Art of Android Development Reading Notes 8
categories: android
date: 2015-12-04 10:50:54
---
《Android开发艺术探索》读书笔记 (8) 第8章 理解Window和WindowManager <!--more-->

### 第8章 理解Window和WindowManager
#### 8.1 Window和WindowManager
(1)`Window`是抽象类，具体实现是`PhoneWindow`，通过`WindowManager`就可以创建Window。WindowManager是外界访问Window的入口，但是Window的具体实现是在`WindowManagerService`中，WindowManager和WindowManagerService的交互是一个IPC过程。所有的视图例如Activity、Dialog、Toast都是附加在Window上的。
(2)通过WindowManager添加View的过程：将一个Button添加到屏幕坐标为(100,300)的位置上
```
mFloatingButton = new Button(this);
mFloatingButton.setText("test button");
mLayoutParams = new WindowManager.LayoutParams(
        LayoutParams.WRAP_CONTENT, LayoutParams.WRAP_CONTENT, 0, 0,
        PixelFormat.TRANSPARENT);//0,0 分别是type和flags参数，在后面分别配置了
mLayoutParams.flags = LayoutParams.FLAG_NOT_TOUCH_MODAL
        | LayoutParams.FLAG_NOT_FOCUSABLE
        | LayoutParams.FLAG_SHOW_WHEN_LOCKED;
mLayoutParams.type = LayoutParams.TYPE_SYSTEM_ERROR;
mLayoutParams.gravity = Gravity.LEFT | Gravity.TOP;
mLayoutParams.x = 100;
mLayoutParams.y = 300;
mFloatingButton.setOnTouchListener(this);
mWindowManager.addView(mFloatingButton, mLayoutParams);
```
flags参数解析：
`FLAG_NOT_FOCUSABLE`：表示window不需要获取焦点，也不需要接收各种输入事件。此标记会同时启用`FLAG_NOT_TOUCH_MODAL`，最终事件会直接传递给下层的具有焦点的window；
`FLAG_NOT_TOUCH_MODAL`：在此模式下，系统会将window区域外的单击事件传递给底层的window，当前window区域内的单击事件则自己处理，一般都需要开启这个标记；
`FLAG_SHOW_WHEN_LOCKED`：开启此模式可以让Window显示在锁屏的界面上。  **[奇怪的是我删除这个标记还是在锁屏看到了添加的组件orz]**

type参数表示window的类型，**window共有三种类型：应用window，子window和系统window。应用window对应着一个Activity，子window不能独立存在，需要附属在特定的父window之上，比如Dialog就是子window。系统window是需要声明权限才能创建的window，比如Toast和系统状态栏这些都是系统window，需要声明的权限是`<uses-permission android:name="android.permission.SYSTEM_ALERT_WINDOW" />`。**
(3)window是分层的，每个window都对应着`z-ordered`，层级大的会覆盖在层级小的上面，应用window的层级范围是`1~99`，子window的层级范围是`1000~1999`，系统window的层级范围是`2000~2999`。
[注意，应用window的层级范围并不是`1~999`哟]
(4)WindowManager继承自`ViewManager`，常用的只有三个方法：`addView`、`updateView`和`removeView`。

#### 8.2 Window的内部机制
(1)Window是一个抽象的概念，不是实际存在的，它也是以View的形式存在。在实际使用中无法直接访问Window，只能通过WindowManager才能访问Window。**每个Window都对应着一个View和一个`ViewRootImpl`，Window和View通过ViewRootImpl来建立联系。**
(2)Window的添加、删除和更新过程都是IPC过程，以Window的添加为例，WindowManager的实现类对于`addView`、`updateView`和`removeView`方法都是委托给`WindowManagerGlobal`类，该类保存了很多数据列表，例如所有window对应的view集合`mViews`、所有window对应的ViewRootImpl的集合`mRoots`等，之后添加操作交给了ViewRootImpl来处理，接着会通过`WindowSession`来完成Window的添加过程，这个过程是一个IPC调用，因为最终是通过`WindowManagerService`来完成window的添加的。

#### 8.3 Window的创建过程
(1)Activity的window创建过程
1.Activity的启动过程很复杂，最终会由`ActivityThread`中的`performLaunchActivity`来完成整个启动过程，在这个方法内部会通过类加载器创建Activity的实例对象，并调用它的`attach`方法为其关联运行过程中所依赖的一系列上下文环境变量；
2.Activity实现了Window的`Callback`接口，当window接收到外界的状态变化时就会回调Activity的方法，例如`onAttachedToWindow`、`onDetachedFromWindow`、`dispatchTouchEvent`等；
3.Activity的Window是由`PolicyManager`来创建的，它的真正实现是`Policy`类，它会新建一个`PhoneWindow`对象，Activity的`setContentView`的实现是由`PhoneWindow`来实现的；
4.Activity的顶级View是`DecorView`，它本质上是一个`FrameLayout`。如果没有DecorView，那么PhoneWindow会先创建一个DecorView，然后加载具体的布局文件并将view添加到DecorView的`mContentParent`中，最后就是回调Activity的`onContentChanged`通知Activity视图已经发生了变化；
5.还有一个步骤是让WindowManager能够识别DecorView，在`ActivityThread`调用`handleResumeActivity`方法时，首先会调用Activity的onResume方法，然后会调用`makeVisible`方法，这个方法中DecorView真正地完成了添加和显示过程。
```
ViewManager vm = getWindowManager();
vm.addView(mDecor, getWindow().getAttributes());
mWindowAdded = true;
```
(2)Dialog的Window创建过程
1.过程与Activity的Window创建过程类似，普通的Dialog的有一个特别之处，即它必须采用Activity的Context，如果采用Application的Context会报错。原因是Application没有`应用token`，应用token一般是Activity拥有的。[service貌似也有token?]

(3)Toast的Window创建过程
1.Toast属于系统Window，它内部的视图由两种方式指定：一种是系统默认的演示；另一种是通过`setView`方法来指定一个自定义的View。
2.Toast具有定时取消功能，所以系统采用了`Handler`。Toast的显示和隐藏是IPC过程，都需要`NotificationManagerService`来实现。在Toast和NMS进行IPC过程时，NMS会跨进程回调Toast中的`TN`类中的方法，TN类是一个Binder类，运行在Binder线程池中，所以需要通过Handler将其切换到当前发送Toast请求所在的线程，所以**Toast无法在没有Looper的线程中弹出**。
3.对于非系统应用来说，`mToastQueue`最多能同时存在`50`个`ToastRecord`，这样做是为了防止`DOS`(Denial of Service，拒绝服务)。因为如果某个应用弹出太多的Toast会导致其他应用没有机会弹出Toast。

**其他学习资料**
1.[Android应用开发之（WindowManager类使用）](http://blog.csdn.net/wang_shaner/article/details/8596380)

OK，本章结束，谢谢阅读。
