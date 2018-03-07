---
layout: post
title: Art of Android Development Reading Notes 13
categories: android
date: 2015-12-04 18:50:54
---
《Android开发艺术探索》读书笔记 (13) 第13章 综合技术、第14章 JNI和NDK编程、第15章 Android性能优化 <!--more-->

### 第13章 综合技术
#### 13.1 使用CrashHandler来获取应用的Crash信息
(1)应用发生Crash在所难免，但是如何采集crash信息以供后续开发处理这类问题呢？利用Thread类的`setDefaultUncaughtExceptionHandler`方法！`defaultUncaughtHandler`是Thread类的静态成员变量，所以如果我们将自定义的`UncaughtExceptionHandler`设置给Thread的话，那么当前进程内的所有线程都能使用这个UncaughtExceptionHandler来处理异常了。
```
public static void setDefaultUncaughtExceptionHandler(UncaughtExceptionHandler handler) {
    Thread.defaultUncaughtHandler = handler;
}
```
(2)作者实现了一个简易版本的UncaughtExceptionHandler类的子类`CrashHandler`，[源码传送门](https://github.com/singwhatiwanna/android-art-res/blob/master/Chapter_13/CrashTest/src/com/ryg/crashtest/CrashHandler.java)
CrashHandler的使用方式就是在Application的`onCreate`方法中设置一下即可
```
//在这里为应用设置异常处理程序，然后我们的程序才能捕获未处理的异常
CrashHandler crashHandler = CrashHandler.getInstance();
crashHandler.init(this);
```

#### 13.2 使用multidex来解决方法数越界
(1)在Android中单个dex文件所能够包含的最大方法数是`65536`，这包含Android Framework、依赖的jar以及应用本身的代码中的所有方法。如果方法数超过了最大值，那么编译会报错`DexIndexOverflowException`。
有时方法数没有超过最大值，但是安装在低版本手机上时应用异常终止了，报错`Optimization failed`。这是因为应用在安装的时候，系统会通过`dexopt`程序来优化dex文件，在优化的过程中dexopt采用一个固定大小的缓冲区来存储应用中所有方法的信息，这个缓冲区就是`LinearAlloc`。LinearAlloc缓冲区在新版本的Android系统中大小是8MB或者16MB，但是在Android 2.2和2.3中却只有5MB，当待安装的应用的方法数比较多的时候，尽管它还没有达到最大方法数，但是它的存储空间仍然有可能超过5MB，这种情况下dexopt就会报错导致安装失败。
(2)**如何解决方法数越界的问题呢？** Google在2014年提出了简单方便的`multidex`的解决方案。
在Android 5.0之前使用multidex需要引入`android-support-multidex.jar`包，从Android 5.0开始，系统默认支持了multidex，它可以从apk中加载多个dex。Multidex方案主要针对AndroidStudio和Gradle编译环境。
使用Multidex的步骤：
1.在`build.gradle`文件中添加`multiDexEnabled true`
```
android {
    ...

    defaultConfig {
        ...

        multiDexEnabled true // [添加的配置] enable multidex support
    }
    ...
}
```
2.添加对multidex的依赖
```
compile 'com.android.support:multidex:1.0.0'
```
3.在代码中添加对multidex的支持，这里有三种方案：
① 在AndroidManifest文件中指定Application为`MultiDexApplication`
```
<application android:name="android.support.multidex.MultiDexApplication"
...
</application>
```
② 让应用的Application继承自`MultiDexApplication`
③ 重写Application的`attachBaseContext`方法，这个方法要先于`onCreate`方法执行
```
public class TestApplication extends Application {

    @Override
    protected void attachBaseContext(Context base) {
        super.attachBaseContext(base);
        MultiDex.install(this); //
    }
}
```

采用上面的配置之后，如果应用的方法数没有越界，那么Gradle并不会生成多个dex文件；如果方法数越界后，Gradle就会在apk中打包2个或者多个dex文件，具体会打包多少个dex文件要看当前项目的代码规模。在有些情况下，可能需要指定主dex文件中所要包含的类，这个可以通过`--main-dex-list`选项来实现这个功能。
```
afterEvaluate {
    println "afterEvaluate"
    tasks.matching {
        it.name.startsWith('dex')
    }.each { dx ->
        def listFile = project.rootDir.absolutePath + '/app/maindexlist.txt'
        println "root dir:" + project.rootDir.absolutePath
        println "dex task found: " + dx.name
        if (dx.additionalParameters == null) {
            dx.additionalParameters = []
        }
        dx.additionalParameters += '--multi-dex'
        dx.additionalParameters += '--main-dex-list=' + listFile
        dx.additionalParameters += '--minimal-main-dex'
    }
}
```

`--multi-dex`表明当方法数越界时生成多个dex文件，`--main-dex-list`指定了要在主dex中打包的类的列表，`--minimal-main-dex`表明只有`--main-dex-list`所指定的类才能打包到主dex中。multidex的jar包中的9个类必须要打包到主dex中，其次不能在Application中成员以及代码块中访问其他dex中的类，否个程序会因为无法加载对应的类而中止执行。
(3)Multidex方案可能带来的问题：
1.应用启动速度会降低，因为应用启动的时候会加载额外的dex文件，所以要避免生成较大的dex文件；
2.需要做大量的兼容性测试，因为Dalvik LinearAlloc的bug，可能导致使用multidex的应用无法在Android 4.0以前的手机上运行。

#### 13.3 Android的动态加载技术
(1)动态加载技术又称插件化技术，将应用插件化可以减轻应用的内存和CPU占用，还可以在不发布新版本的情况下更新某些模块。不同的插件化方案各有特色，但是都需要解决**三个基础性问题：资源访问，Activity生命周期管理和插件ClassLoader的管理。**
(2)宿主和插件：宿主是指普通的apk，插件是经过处理的dex或者apk。在主流的插件化框架中多采用特殊处理的apk作为插件，处理方式往往和编译以及打包环节有关，另外很多插件化框架都需要用到代理Activity的概念，插件Activity的启动大多数是借助一个代理Activity来实现的。
(3)资源访问：宿主程序调起未安装的插件apk，插件中凡是R开头的资源都不能访问了，因为宿主程序中并没有插件的资源，通过R来访问插件的资源是行不通的。
Activity的资源访问是通过`ContextImpl`来完成的，它有两个方法`getAssets()`和`getResources()`方法是用来加载资源的。
具体实现方式是通过反射，调用`AssetManager`的`addAssetPath`方法添加插件的路径，然后将插件apk中的资源加载到`Resources`对象中即可。
(4)Activity生命周期管理：有两种常见的方式，反射方式和接口方式。反射方式就是通过反射去获取Activity的各个生命周期方法，然后在代理Activity中去调用插件Activity对应的生命周期方法即可。
反射方式代码繁琐，性能开销大。接口方式将Activity的生命周期方法提取出来作为一个接口，然后通过代理Activity去调用插件Activity的生命周期方法，这样就完成了插件Activity的生命周期管理。
(5)插件ClassLoader的管理：为了更好地对多插件进行支持，需要合理地去管理各个插件的`DexClassLoader`，这样同一个插件就可以采用同一个ClassLoader去加载类，从而避免了多个ClassLoader加载同一个类时所引起的类型转换错误。

**其他详细信息看作者插件化框架[singwhatiwanna/dynamic-load-apk](https://github.com/singwhatiwanna/dynamic-load-apk)**

#### 13.4 反编译初步
1.主要介绍使用`dex2jar`和`jd-gui`反编译apk和使用`apktool`对apk进行二次打包，比较简单，略过不总结。

<be/>
### 第14章 JNI和NDK编程

本章主要是介绍JNI和NDK编程入门知识，比较简答，略过不总结。
如果感兴趣NDK开发可以阅读我之前总结的[Android NDK和OpenCV整合开发系列文章](https://hujiaweibujidao.github.io/blog/2013/11/18/android-ndk-and-opencv-developement/)。

<be/>
### 第15章 Android性能优化

(1)2015年Google关于Android性能优化典范的专题视频 [Youtube视频地址](https://www.youtube.com/playlist?list=PLWz5rJ2EKKc9CBxr3BVjPTPoDPLdPIFCE)

(2)布局优化
1.删除布局中无用的组件和层级，有选择地使用性能较低的ViewGroup；
2.使用`<include>`、`<merge>`、`<viewstub>`等标签：`<include>`标签主要用于布局重用，`<merge>`标签一般和`<include>`配合使用，它可以减少布局中的层级；`<viewstub>`标签则提供了按需加载的功能，当需要的时候才会将ViewStub中的布局加载到内存，提供了程序的初始化效率。
3.`<include>`标签只支持`android:layout_`开头的属性，`android:id`属性例外。
4.`ViewStub`继承自View，它非常轻量级且宽高都为0，它本身不参与任何的布局和绘制过程。实际开发中，很多布局文件在正常情况下不会显示，例如网络异常时的界面，这个时候就没有必要在整个界面初始化的时候加载进行，通过ViewStub可以做到在需要的时候再加载。
如下面示例，`android:id`是ViewStub的id，而`android:inflatedId`是布局的根元素的id。
```
<ViewStub android:id="@+id/xxx"
  android:inflatedId="@+id/yyy"
  android:layout="@layout/zzz"
  ...
</ViewStub>
```

(3)绘制优化
1.在`onDraw`中不要创建新的布局对象，因为`onDraw`会被频繁调用；
2.`onDraw`方法中不要指定耗时任务，也不能执行成千上万次的循环操作。

(4)内存泄露优化
1.可能导致内存泄露的场景很多，例如静态变量、单例模式、属性动画、AsyncTask、Handler等等

(5)响应速度优化和ANR日志分析
1.ANR出现的情况：Activity如果`5s`内没有响应屏幕触摸事件或者键盘输入事件就会ANR，而BroadcastReceiver如果`10s`内没有执行完操作也会出现ANR。
2.当一个进程发生了ANR之后，系统会在`/data/anr`目录下创建一个文件`traces.txt`，通过分析这个文件就能定位ANR的原因。

(6)ListView和Bitmap优化
1.ListView优化：采用`ViewHolder`并避免在`getView`方法中执行耗时操作；根据列表的滑动状态来绘制任务的执行频率；可以尝试开启硬件加速来使ListView的滑动更加流畅。
2.Bitmap优化：根据需要对图片进行采样，详情看[Android开发艺术探索》读书笔记 (12) 第12章 Bitmap的加载和Cache](https://hujiaweibujidao.github.io/blog/2015/11/30/Art-of-Android-Development-Reading-Notes-12/)。

(7)线程优化
1.采用线程池，详情看[《Android开发艺术探索》读书笔记 (11) 第11章 Android的线程和线程池](https://hujiaweibujidao.github.io/blog/2015/12/03/Art-of-Android-Development-Reading-Notes-11/)。

(8)其他优化建议
1.不要过多使用枚举，枚举占用的内存空间要比整型大；
2.常量请使用`static final`来修饰；
3.使用一些Android特有的数据结构，比如`SparseArray`和`Pair`等，他们都具有更好的性能；
4.适当使用软引用和弱引用；
5.采用内存缓存和磁盘缓存；
6.尽量采用静态内部类，这样可以避免潜在的由于内部类而导致的内存泄露。

(9)MAT是功能强大的内存分析工具，主要有`Histograms`和`Dominator Tree`等功能

OK，本章结束，谢谢阅读。


