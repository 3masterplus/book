---
layout: post
title: Art of Android Development Reading Notes 2
categories: android
date: 2015-12-05 11:50:54
---
《Android开发艺术探索》读书笔记 (2) 第2章 IPC机制 <!--more-->

### 第2章 IPC机制
#### 2.1 Android IPC简介
(1)任何一个操作系统都需要有相应的IPC机制，Linux上可以通过命名通道、共享内存、信号量等来进行进程间通信。Android系统不仅可以使用Binder机制来实现IPC，还可以使用Socket实现任意两个终端之间的通信。

#### 2.2 Android中的多进程模式
(1)通过给四大组件指定`android:process`属性就可以开启多进程模式，默认进程的进程名是包名packageName，进程名以`:`开头的进程属于当前应用的私有进程，其他应用的组件不可以和它跑在同一个进程中，而进程名不以`:`开头的进程属于全局进程，其他应用通过`ShareUID`方法可以和它跑在同一个进程中。
```
android:process=":xyz" //进程名是 packageName:xyz
android:process="aaa.bbb.ccc" //进程名是 aaa.bbb.ccc
```
(2)Android系统会为每个应用分配一个唯一的UID，具有相同UID的应用才能共享数据。**两个应用通过ShareUID跑在同一个进程中是有要求的，需要这两个应用有相同的ShareUID并且签名相同才可以。** 在这种情况下，它们可以相互访问对方的私有数据，比如data目录、组件信息等，不管它们是否跑在同一个进程中。如果它们跑在同一个进程中，还可以共享内存数据，它们看起来就像是一个应用的两个部分。
(3)android系统会为每个进程分配一个独立的虚拟机，不同的虚拟机在内存分配上有不同的地址空间，所以不同的虚拟机中访问同一个类的对象会产生多个副本。
(4)使用多进程容易造成以下几个问题：
1.静态成员和单例模式完全失效；
2.线程同步机制完全失效：无论锁对象还是锁全局对象都无法保证线程同步；
3.`SharedPreferences`的可靠性下降：SharedPreferences不支持并发读写；
4.Application会多次创建：当一个组件跑在一个新的进程的时候，系统要在创建新的进程的同时分配独立的虚拟机，应用会重新启动一次，也就会创建新的Application。**运行在同一个进程中的组件是属于同一个虚拟机和同一个Application。**
**同一个应用的不同组件，如果它们运行在不同进程中，那么和它们分别属于两个应用没有本质区别。**

#### 2.3 IPC基础概念介绍
(1)`Serializable`接口是Java中为对象提供标准的序列化和反序列化操作的接口，而`Parcelable`接口是Android提供的序列化方式的接口。
(2)`serialVersionUId`是一串long型数字，主要是用来辅助序列化和反序列化的，原则上序列化后的数据中的serialVersionUId只有和当前类的serialVersionUId相同才能够正常地被反序列化。
**serialVersionUId的详细工作机制**：序列化的时候系统会把当前类的serialVersionUId写入`序列化的文件`中，当反序列化的时候系统会去检测文件中的serialVersionUId，看它是否和当前类的serialVersionUId一致，如果一致就说明序列化的类的版本和当前类的版本是相同的，这个时候可以成功反序列化；否则说明版本不一致无法正常反序列化。**一般来说，我们应该手动指定serialVersionUId的值。**
1.静态成员变量属于类不属于对象，所以不参与序列化过程；
2.声明为`transient`的成员变量不参与序列化过程。
(3)`Parcelable`接口内部包装了可序列化的数据，可以在Binder中自由传输，`Parcelable`主要用在`内存序列化`上，可以直接序列化的有Intent、Bundle、Bitmap以及List和Map等等，下面是一个实现了`Parcelable`接口的示例
```
public class Book implements Parcelable {
    public int bookId;
    public String bookName;
    public Book() {
    }

    public Book(int bookId, String bookName) {
        this.bookId = bookId;
        this.bookName = bookName;
    }

    //“内容描述”，如果含有文件描述符返回1，否则返回0，几乎所有情况下都是返回0
    public int describeContents() {
        return 0;
    }

    //实现序列化操作，flags标识只有0和1，1表示标识当前对象需要作为返回值返回，不能立即释放资源，几乎所有情况都为0
    public void writeToParcel(Parcel out, int flags) {
        out.writeInt(bookId);
        out.writeString(bookName);
    }

    //实现反序列化操作
    public static final Parcelable.Creator<Book> CREATOR = new Parcelable.Creator<Book>() {
        //从序列化后的对象中创建原始对象
        public Book createFromParcel(Parcel in) {
            return new Book(in);
        }
        public Book[] newArray(int size) {//创建指定长度的原始对象数组
            return new Book[size];
        }
    };

    private Book(Parcel in) {
        bookId = in.readInt();
        bookName = in.readString();
    }

}
```
(4)`Binder`是Android中的一个类，它实现了`IBinder`接口。从IPC角度看，Binder是Android中一种跨进程通信的方式；Binder还可以理解为虚拟的物理设备，它的设备驱动是`/dev/binder`；从Framework层角度看，Binder是ServiceManager连接各种`Manager`和相应的`ManagerService`的桥梁；从Android应用层来说，Binder是客户端和服务端进行通信的媒介，当`bindService`的时候，服务端会返回一个包含了服务端业务调用的Binder对象，通过这个Binder对象，客户端就可以获取服务端提供的服务或者数据，这里的服务包括普通服务和基于AIDL的服务。
在Android开发中，Binder主要用在Service中，包括AIDL和Messenger，其中普通Service中的Binder不涉及进程间通信，较为简单；而Messenger的底层其实是AIDL，正是Binder的核心工作机制。
(5)aidl工具根据aidl文件自动生成的java接口的解析：首先，它声明了几个接口方法，同时还声明了几个整型的id用于标识这些方法，id用于标识在`transact`过程中客户端所请求的到底是哪个方法；接着，它声明了一个内部类`Stub`，这个Stub就是一个`Binder`类，当客户端和服务端都位于同一个进程时，方法调用不会走跨进程的`transact`过程，而当两者位于不同进程时，方法调用需要走transact过程，这个逻辑由Stub内部的代理类`Proxy`来完成。
**所以，这个接口的核心就是它的内部类Stub和Stub内部的代理类Proxy。** 下面分析其中的方法：
1.`asInterface(android.os.IBinder obj)`：用于将服务端的Binder对象转换成客户端所需的AIDL接口类型的对象，这种转换过程是区分进程的，**如果客户端和服务端是在同一个进程中，那么这个方法返回的是服务端的`Stub`对象本身，否则返回的是系统封装的`Stub.Proxy`对象。**
2.`asBinder`：返回当前Binder对象。
3.`onTransact`：这个方法运行在**服务端中的Binder线程池**中，当客户端发起跨进程请求时，远程请求会通过系统底层封装后交由此方法来处理。
这个方法的原型是`public Boolean onTransact(int code, Parcelable data, Parcelable reply, int flags)`
服务端通过`code`可以知道客户端请求的目标方法，接着从`data`中取出所需的参数，然后执行目标方法，执行完毕之后，将结果写入到`reply`中。如果此方法返回false，说明客户端的请求失败，利用这个特性可以做权限验证(即验证是否有权限调用该服务)。
4.`Proxy#[Method]`：代理类中的接口方法，这些方法运行在客户端，当客户端远程调用此方法时，它的内部实现是：首先创建该方法所需要的参数，然后把方法的参数信息写入到`_data`中，接着调用`transact`方法来发起RPC请求，同时当前线程挂起；然后服务端的`onTransact`方法会被调用，直到RPC过程返回后，当前线程继续执行，并从`_reply`中取出RPC过程的返回结果，最后返回`_reply`中的数据。

**如果搞清楚了自动生成的接口文件的结构和作用之后，其实是可以不用通过AIDL而直接实现Binder的，[主席写的示例代码](https://github.com/singwhatiwanna/android-art-res/blob/master/Chapter_2/src/com/ryg/chapter_2/manualbinder/BookManagerImpl.java)**

(6)Binder的两个重要方法`linkToDeath`和`unlinkToDeath`
Binder运行在服务端，如果由于某种原因服务端异常终止了的话会导致客户端的远程调用失败，所以Binder提供了两个配对的方法`linkToDeath`和`unlinkToDeath`，通过`linkToDeath`方法可以给Binder设置一个死亡代理，当Binder死亡的时候客户端就会收到通知，然后就可以重新发起连接请求从而恢复连接了。
**如何给Binder设置死亡代理呢？**
1.声明一个`DeathRecipient`对象，`DeathRecipient`是一个接口，其内部只有一个方法`bindeDied`，实现这个方法就可以在Binder死亡的时候收到通知了。
```
private IBinder.DeathRecipient mDeathRecipient = new IBinder.DeathRecipient() {
    @Override
    public void binderDied() {
        if (mRemoteBookManager == null) return;
        mRemoteBookManager.asBinder().unlinkToDeath(mDeathRecipient, 0);
        mRemoteBookManager = null;
        // TODO:这里重新绑定远程Service
    }
};
```
2.在客户端绑定远程服务成功之后，给binder设置死亡代理
```
mRemoteBookManager.asBinder().linkToDeath(mDeathRecipient, 0);
```

#### 2.4 Android中的IPC方式
(1)**使用Bundle**：Bundle实现了Parcelable接口，Activity、Service和Receiver都支持在Intent中传递Bundle数据。

(2)**使用文件共享**：这种方式简单，适合在对数据同步要求不高的进程之间进行通信，并且要妥善处理并发读写的问题。
`SharedPreferences`是一个特例，虽然它也是文件的一种，但是由于系统对它的读写有一定的缓存策略，即在内存中会有一份SharedPreferences文件的缓存，因此在多进程模式下，系统对它的读写就变得不可靠，当面对高并发读写访问的时候，有很大几率会丢失数据，因此，不建议在进程间通信中使用SharedPreferences。

(3)**使用Messenger**：`Messenger`是一种轻量级的IPC方案，它的底层实现就是AIDL。Messenger是以串行的方式处理请求的，即服务端只能一个个处理，不存在并发执行的情形，详细的示例见原书。

(4)**使用AIDL**
大致流程：首先建一个Service和一个AIDL接口，接着创建一个类继承自AIDL接口中的Stub类并实现Stub类中的抽象方法，在Service的onBind方法中返回这个类的对象，然后客户端就可以绑定服务端Service，建立连接后就可以访问远程服务端的方法了。
1.AIDL支持的数据类型：基本数据类型、`String`和`CharSequence`、`ArrayList`、`HashMap`、`Parcelable`以及`AIDL`；
2.某些类即使和AIDL文件在同一个包中也要显式import进来；
3.AIDL中除了基本数据类，其他类型的参数都要标上方向：`in`、`out`或者`inout`；
4.AIDL接口中支持方法，不支持声明静态变量；
5.为了方便AIDL的开发，建议把所有和AIDL相关的类和文件全部放入同一个包中，这样做的好处是，当客户端是另一个应用的时候，可以直接把整个包复制到客户端工程中。
6.`RemoteCallbackList`是系统专门提供的用于删除跨进程Listener的接口。RemoteCallbackList是一个泛型，支持管理任意的AIDL接口，因为所有的AIDL接口都继承自`IInterface`接口。

(5)**使用ContentProvider**
1.ContentProvider主要以表格的形式来组织数据，并且可以包含多个表；
2.ContentProvider还支持文件数据，比如图片、视频等，系统提供的`MediaStore`就是文件类型的ContentProvider；
3.ContentProvider对底层的数据存储方式没有任何要求，可以是SQLite、文件，甚至是内存中的一个对象都行；
4.要观察ContentProvider中的数据变化情况，可以通过`ContentResolver`的`registerContentObserver`方法来注册观察者；

(6)**使用Socket**
Socket是网络通信中“套接字”的概念，分为流式套接字和用户数据包套接字两种，分别对应网络的传输控制层的TCP和UDP协议。

#### 2.5 Binder连接池
(1)当项目规模很大的时候，创建很多个Service是不对的做法，因为service是系统资源，太多的service会使得应用看起来很重，所以最好是将所有的AIDL放在同一个Service中去管理。整个工作机制是：每个业务模块创建自己的AIDL接口并实现此接口，这个时候不同业务模块之间是不能有耦合的，所有实现细节我们要单独开来，然后向服务端提供自己的唯一标识和其对应的Binder对象；对于服务端来说，只需要一个Service，服务端提供一个`queryBinder`接口，这个接口能够根据业务模块的特征来返回相应的Binder对象给它们，不同的业务模块拿到所需的Binder对象后就可以进行远程方法调用了。
**Binder连接池的主要作用就是将每个业务模块的Binder请求统一转发到远程Service去执行，从而避免了重复创建Service的过程。**
(2)作者实现的Binder连接池[`BinderPool`的实现源码](https://github.com/singwhatiwanna/android-art-res/blob/master/Chapter_2/src/com/ryg/chapter_2/binderpool/BinderPool.java)，建议在AIDL开发工作中引入BinderPool机制。

#### 2.6 选用合适的IPC方式

![img](https://hujiaweibujidao.github.io/images/androidart_ipc.png)

OK，本章结束，谢谢阅读。


