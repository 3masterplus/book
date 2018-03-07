---
layout: post
title: Android Heroes Reading Notes 4
categories: android
date: 2015-11-28 13:48:34
---
《Android群英传》读书笔记 (4) 第八章 Activity和Activity调用栈分析 + 第九章 系统信息与安全机制 + 第十章 性能优化 <!--more-->

### **第八章 Activity和Activity调用栈分析**
1.Activity生命周期
理解生命周期就是两张图：第一张图是回字型的生命周期图
{% img https://hujiaweibujidao.github.io/images/androidheros_activitylife1.png 280 340 %}
第二张图是金字塔型的生命周期图
{% img https://hujiaweibujidao.github.io/images/androidheros_activitylife2.png 350 150%}

**注意点**
(1)从stopped状态重新回到前台状态的时候会先调用`onRestart`方法，然后再调用后续的`onStart`等方法；
(2)启动另一个Activity然后finish，先调用旧Activity的onPause方法，然后调用新的Activity的onCreate->onStart->onResume方法，然后调用旧Activity的onStop->onDestory方法。
如果没有调用finish那么旧Activity会调用onPause->onSaveInstanceState->onStop方法，onDestory方法不会被调用。
(3)如果应用长时间处于stopped状态并且此时系统内存极为紧张的时候，系统就会回收Activity，此时系统在回收之前会回调`onSaveInstanceState`方法来保存应用的数据Bundle。当该Activity重新创建的时候，保存的Bundle数据就会传递到`onRestoreSaveInstanceState`方法和`onCreate`方法中，这就是`onCreate`方法中`Bundle savedInstanceState`参数的来源（onRestoreInstanceState的bundle参数也会传递到onCreate方法中，你也可以选择在onCreate方法中做数据还原）。
**onSaveInstanceState方法和onRestoreInstanceState方法“不一定”是成对的被调用的。**
onSaveInstanceState的调用遵循一个重要原则，即当系统“未经你许可”时销毁了你的activity，则onSaveInstanceState会被系统调用，这是系统的责任，因为它必须要提供一个机会让你保存你的数据。
onRestoreInstanceState被调用的前提是，activity“确实”被系统销毁了，而如果仅仅是停留在有这种可能性的情况下，则该方法不会被调用，例如，当正在显示activity的时候，用户按下HOME键回到主界面，然后用户紧接着又返回到activity，这种情况下activity一般不会因为内存的原因被系统销毁，故activity的onRestoreInstanceState方法不会被执行。

2.Activity任务栈
应用内的Activity是被任务栈Task来管理的，一个Task中的Activity可以来自不同的应用，同一个应用的Activity也可能不在同一个Task中。默认情况下，任务栈依据栈的后进先出原则管理Activity，但是Activity可以设置一些“特权”打破默认的规则，主要是通过在AndroidManifest文件中的属性`android:launchMode`或者通过Intent的flag来设置。

**standard**：默认的启动模式，该模式下会生成一个新的Activity，同时将该Activity实例压入到栈中（不管该Activity是否已经存在在Task栈中，都是采用new操作）。例如： 栈中顺序是A B C D ，此时D通过Intent跳转到A，那么栈中结构就变成 A B C D A，点击返回按钮的 显示顺序是 D C B A，依次摧毁。

**singleTop**：在singleTop模式下，如果当前Activity D位于栈顶，此时通过Intent跳转到它本身的Activity（即D），那么不会重新创建一个新的D实例（走onNewIntent()），所以栈中的结构依旧为A B C D，如果跳转到B，那么由于B不处于栈顶，所以会新建一个B实例并压入到栈中，结构就变成了A B C D B。应用实例：三条推送，点进去都是一个activity。

**singleTask**：在singleTask模式下，Task栈中只能有一个对应Activity的实例。例如：现在栈的结构为A B C D，此时D通过Intent跳转到B（走onNewIntent()），则栈的结构变成了：A B。其中的C和D被栈弹出销毁了，也就是说位于B之上的实例都被销毁了。如果系统已经存在一个实例，系统就会将请求发送到这个实例上，但这个时候，系统就不会再调用通常情况下我们处理请求数据的onCreate方法，而是调用onNewIntent方法。通常应用于首页，首页肯定得在栈底部，也只能在栈底部。

**singleInstance**：singleInstance模式下会将打开的Activity压入一个新建的任务栈中。例如：Task栈1中结构为：A B C，C通过Intent跳转到了D（D的启动模式为singleInstance），那么则会新建一个Task 栈2，栈1中结构依旧为A B C，栈2中结构为D，此时屏幕中显示D，之后D通过Intent跳转到D，栈2中不会压入新的D，所以2个栈中的情况没发生改变。如果D跳转到了C，那么就会根据C对应的启动模式在栈1中进行对应的操作，C如果为standard，那么D跳转到C，栈1的结构为A B C C，此时点击返回按钮，还是在C，栈1的结构变为A B C，而不会回到D。

3.Intent Flag启动模式
(1)`Intent.FLAG_ACTIVITY_NEW_TASK`：使用一个新的task来启动Activity，一般用在service中启动Activity的场景，因为service中并不存在Activity栈。
(2)`Intent.FLAG_ACTIVITY_SINGLE_TOP`：类似`andoid:launchMode="singleTop"`
(3)`Intent.FLAG_ACTIVITY_CLEAR_TOP`：类似`andoid:launchMode="singleTask"`
(4)`Intent.FLAG_ACTIVITY_NO_HISTORY`：使用这种模式启动Activity，当该Activity启动其他Activity后，该Activity就消失了，不会保留在task栈中。例如A B，在B中以这种模式启动C，C再启动D，则当前的task栈变成A B D。

4.清空任务栈
(1)`clearTaskOnLaunch`：每次返回该Activity时，都将该Activity之上的所有Activity都清除。通过这个属性可以让task每次在初始化的时候都只有这一个Activity。
(2)`finishOnTaskLaunch`：clearTaskOnLaunch作用在别的Activity身上，而finishOnTaskLaunch作用在自己身上。通过这个属性，当离开这个Activity所在的task，那么当用户再返回时，该Activity就会被finish掉。 **[暂时还不明白这个有什么作用]**
(3)`alwaysRetainTaskState`：如果将Activity的这个属性设置为true，那么该Activity所在的task将不接受任何清理命令，一直保持当前task状态，相当于给了task一道”免死金牌”。

<br/>
### **第九章 Android系统信息与安全机制**
1.获取系统信息：`android.os.Build`和`SystemProperty`
```
String board = Build.BOARD;
String brand = Build.BRAND;
String supported_abis = Build.SUPPORTED_ABIS[0];
String device = Build.DEVICE;
String display = Build.DISPLAY;
String fingerprint = Build.FINGERPRINT;
String serial = Build.SERIAL;
String id = Build.ID;
String manufacturer = Build.MANUFACTURER;
String model = Build.MODEL;
String hardware = Build.HARDWARE;
String product = Build.PRODUCT;
String tags = Build.TAGS;
String type = Build.TYPE;
String codename = Build.VERSION.CODENAME;
String incremental = Build.VERSION.INCREMENTAL;
String release = Build.VERSION.RELEASE;
String sdk_int = "" + Build.VERSION.SDK_INT;
String host = Build.HOST;
String user = Build.USER;
String time = "" + Build.TIME;

String os_version = System.getProperty("os.version");
String os_name = System.getProperty("os.name");
String os_arch = System.getProperty("os.arch");
String user_home = System.getProperty("user.home");
String user_name = System.getProperty("user.name");
String user_dir = System.getProperty("user.dir");
String user_timezone = System.getProperty("user.timezone");
String path_separator = System.getProperty("path.separator");
String line_separator = System.getProperty("line.separator");
String file_separator = System.getProperty("file.separator");
String java_vendor_url = System.getProperty("java.vendor.url");
String java_class_path = System.getProperty("java.class.path");
String java_class_version = System.getProperty("java.class.version");
String java_vendor = System.getProperty("java.vendor");
String java_version = System.getProperty("java.version");
String java_home = System.getProperty("java_home");
```

2.Apk应用信息：`PackageManager`和`ActivityManager`
在AndroidManifest文件中，Activity的信息是通过`ActivityInfo`类来封装的；整个Manifest文件中节点的信息是通过`PackageInfo`类来进行封装的；此外还有`ServiceInfo`、`ApplicationInfo`、`ResolveInfo`等。
其中`ResolveInfo`封装的是包含<intent>信息的上一级信息，所以它可以返回ActivityInfo、ServiceInfo等包含<intent>的信息，它经常用来帮助我们找到那些包含特定Intent条件的信息，如带分享功能、播放功能的应用。

PackageManager侧重于获取应用的包信息，而ActivityManager侧重于获取运行的应用程序的信息。
PackageManager常用的方法：
`getPackageManger`、`getApplicationInfo`、`getApplicationIcon`、`getInstalledApplications`、`getInstalledPackages`、`queryIntentActivities`、`queryIntentServices`、`resolveActivity`、`resolveService`等
ActivityManager封装了不少对象，每个对象都保存着一些重要信息。
`ActivityManager.MemoryInfo`：关于系统内存的信息，例如`availMem`(系统可用内存)、`totalMem`(总内存)等；
`Debug.MemoryInfo`：该MemoryInfo主要用于统计进程下的内存信息；
`RunningAppProceeInfo`：运行进程的信息，存储的是与进程相关的信息，例如`processName`、`pid`、`uid`等；
`RunningServiceInfo`：运行服务的信息，存储的是服务进程的信息，例如`activeSince`(第一次被激活时间)等。

3.packages.xml文件(位于`/data/system`目录下)
在系统初始化的时候，PackageManager的底层实现类PackageManagerService会去扫描系统中的一些特定的目录，并解析其中的apk文件，最后把它获得的应用信息保存到packages.xml文件中，当系统中的应用安装、删除或者升级时，它也会被更新。

4.Android安全机制
五道防线：
(1)代码安全机制——代码混淆proguard
(2)应用接入权限机制——AndroidManifest文件权限声明、权限检查机制
系统检查操作者权限的顺序：首先，判断permission名称，如果为空则直接返回PERMISSION_DENIED;其次，判断Uid，如果uid为0或者为System Service的uid，不做权限控制，如果uid与参数中的请求uid不同，那么返回PERMISSION_DENIED；最后，通过调用PackageManagerService.checkUidPermission方法判断该uid是否具有相应的权限，该方法会去xml的权限列表和系统级的platform.xml中进行查找。
(3)应用签名机制——数字证书：系统不会安装没有签名的app，只有拥有相同数字签名的app才会在升级时被认为是同一个app
(4)Linux内核层安全机制——Uid、访问权限控制
(5)Android虚拟机沙箱机制——沙箱隔离：每个app运行在单独的虚拟机中，与其他应用完全隔离

**apk反编译**
使用apktool、dex2jar、jd-gui三个工具反编译查看应用源码

**apk加密**
proguard不仅可以用来混淆代码（用无意义的字母来重命名类、方法和属性等），还可以删除无用的类、字段、方法和属性，以及删除无用的注释，最大限度地优化字节码文件。
下面是常见的proguard配置，其中`minifyEnabled`属性控制是否启动proguard；`proguardFiles`属性用于配置混淆文件，它分为两部分，一个是系统默认的混淆文件，它位于`<sdk>/tools/proguard/proguard-android.txt`；另一个是自定义的混淆文件，可以在项目的app文件夹下找到该文件，在该文件中定义引入的第三方依赖包的混淆规则。
```
buildTypes {
    release {
        minifyEnabled false
        proguardFiles getDefaultProguardFile('proguard-android.txt'), 'proguard-rules.pro'
    }
}
```

<br/>
### **第十章 Android性能优化**
1.布局优化
**人眼感觉的流畅需要画面的帧数达到每秒40帧到60帧，那么差不多每16ms系统就要对UI进行渲染和重绘。**
(1)android系统提供了检测UI渲染时间的工具，“开发者选项”-“Profile GPU rendering”-“On screen as bars”，这个时候屏幕上将显示一些条形图，如下左图所示，每条柱状线都包含三部分，蓝色代表测量绘制Display List的时间，红色代表OpenGL渲染Display List所需要的时间，黄色代表CPU等待GPU处理的时间。中间的绿色横线代表VSYNC时间16ms，需要尽量将所有条形图都控制在这条绿线之下。
(2)过度绘制（Overdraw）也是很浪费CPU/GPU资源的，系统也提供了检测工具`Debug GPU Overdraw`来查看界面overdraw的情况。该工具会使用不同的颜色绘制屏幕，来指示overdraw发生在哪里以及程度如何，其中：
没有颜色： 意味着没有overdraw。像素只画了一次。
蓝色： 意味着overdraw 1倍。像素绘制了两次。大片的蓝色还是可以接受的（若整个窗口是蓝色的，可以摆脱一层）。
绿色： 意味着overdraw 2倍。像素绘制了三次。中等大小的绿色区域是可以接受的但你应该尝试优化、减少它们。
浅红： 意味着overdraw 3倍。像素绘制了四次，小范围可以接受。
暗红： 意味着overdraw 4倍。像素绘制了五次或者更多。这是错误的，要修复它们。

{%img /images/androidheros_gpu.png 200 360%} &nbsp;&nbsp; {%img /images/androidheros_overdraw.png 200 360%}
(3)优化布局层级，Google在文档中建议View树的高度不宜超过10层
避免嵌套过多无用布局：
①使用<include>标签重用layout
**如果需要在<include>标签中覆盖类似原布局中的android:layout_xxx的属性，就必须在<include>标签中同时指定android:layout_width和android:layout_height属性。**
②使用<ViewStub>实现view的延迟加载
ViewStub是一个非常轻量级的组件，它不仅不可见，而且大小为0。
**ViewStub和View.GONE有啥区别？**
它们的共同点是初始时都不会显示，但是前者只会在显示时才去渲染整个布局，而后者在初始化布局树的时候就已经添加到布局树上了，相比之下前者的布局具有更高的效率。
(4)Hierarchy Viewer：查看视图树的工具

2.内存优化
通常情况下我们所说的内存是指手机的RAM，它包括以下几部分：
(1)寄存器：寄存器处于CPU内部，在程序中无法控制；
(2)栈：存放基本数据类型和对象的引用；
(3)堆：存放对象和数组，由虚拟机GC来管理；
(4)静态存储区域(static field)：在固定的位置存放应用程序运行时一直存在的数据，Java在内存中专门划分了一个静态存储区域来管理一些特殊的数据变量，如静态的数据变量；
(5)常量池(constant pool)：虚拟机必须为每个被装在的类维护一个常量池，常量池就是这个类所用的常量的一个有序集合，包括直接常量（基本类型、string）和对其他类型、字段和方法的符号引用。

**内存优化实例**
(1)Bitmap优化
使用适当分辨率和大小的图片；
及时回收内存：从Android 3.0开始，Bitmap被放置到了堆中，其内存由GC管理，所以不用手动调用bitmap.recycle()方法进行释放了；
使用图片缓存：设计内存缓存和磁盘缓存可以更好地利用Bitmap。

(2)代码优化
使用静态方法，它比普通方法会提高15%左右的访问速度；
尽量不要使用枚举，少用迭代器；**[我还不知道为什么]**
对Cursor、Receiver、Sensor、File等对象，要非常注意对它们的创建、回收与注册、解注册；
使用SurfaceView来替代view进行大量的、频繁的绘图操作；
尽量使用视图缓存，而不是每次都执行inflate方法解析视图。

3.其他的辅助工具
(1)Lint工具：代码提示工具，可以用来发现代码中隐藏的一些问题
(2)Memory Monitor工具：内存监视工具
(3)TraceView工具：可视化性能调查工具，它用来分析TraceView日志
(4)MAT工具：内存分析工具
(5)dumpsys命令：该命令可以列出android系统相关的信息和服务状态，可使用的配置参数很多，常见的有：
`activity`：显示所有Activity栈的信息；
`meminfo`：显示内存信息；
`battery`：显示电池信息；
`package`：显示包信息；
`wifi`：显示wifi信息；
`alarm`：显示alarm信息；
`procstats`：显示进程和内存状态。

OK，本节结束，谢谢阅读。


