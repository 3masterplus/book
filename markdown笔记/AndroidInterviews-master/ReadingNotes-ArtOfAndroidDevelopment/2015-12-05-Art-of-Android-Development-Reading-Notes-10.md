---
layout: post
title: Art of Android Development Reading Notes 10
categories: android
date: 2015-12-04 11:50:54
---
《Android开发艺术探索》读书笔记 (10) 第10章 Android的消息机制 <!--more-->

### 第10章 Android的消息机制
#### 10.1 Android消息机制概述
(1)Android的消息机制主要是指Handler的运行机制，其底层需要`MessageQueue`和`Looper`的支撑。MessageQueue是以单链表的数据结构存储消息列表但是以队列的形式对外提供插入和删除消息操作的消息队列。MessageQueue只是消息的存储单元，而Looper则是以无限循环的形式去查找是否有新消息，如果有的话就去处理消息，否则就一直等待着。
(2)Handler的主要作用是将一个任务切换到某个指定的线程中去执行。
**为什么要提供这个功能呢？**
Android规定UI操作只能在主线程中进行，`ViewRootImpl`的`checkThread`方法会验证当前线程是否可以进行UI操作。
**为什么不允许子线程访问UI呢？**
这是因为UI组件不是线程安全的，如果在多线程中并发访问可能会导致UI组件处于不可预期的状态。另外，如果对UI组件的访问进行加锁机制的话又会降低UI访问的效率，所以还是采用单线程模型来处理UI事件。
(3)Handler的创建会采用当前线程的Looper来构建内部的消息循环系统，如果当前线程中不存在Looper的话就会报错。Handler可以用`post`方法将一个Runnable投递到消息队列中，也可以用`send`方法发送一个消息投递到消息队列中，其实`post`最终还是调用了`send`方法。

#### 10.2 Android的消息机制分析
(1)`ThreadLocal`的工作原理
1.ThreadLocal是一个线程内部的数据存储类，通过它可以在指定的线程中存储数据，数据存储以后，只有在指定线程中可以获取到存储的数据，对于其他线程来说则无法获取到数据。**一般来说，当某些数据是以线程为作用域并且不同线程具有不同的数据副本的时候，可以考虑使用ThreadLocal。** 对于Handler来说，它需要获取当前线程的Looper，而Looper的作用域就是线程并且不同线程具有不同的Looper，这个时候通过ThreadLocal就可以实现Looper在线程中的存取了。
2.ThreadLocal的原理：不同线程访问同一个ThreadLocal的`get`方法时，ThreadLocal内部会从各自的线程中取出一个数组，然后再从数组中根据当前ThreadLocal的索引去查找出对应的value值，不同线程中的数组是不同的，这就是为什么通过ThreadLocal可以在不同线程中维护一套数据的副本并且彼此互不干扰。
3.ThreadLocal是一个泛型类`public class ThreadLocal<T> `，下面是它的`set`方法
```
public void set(T value) {
    Thread currentThread = Thread.currentThread();
    Values values = values(currentThread);
    if (values == null) {
        values = initializeValues(currentThread);
    }
    values.put(this, value);
}
```
`Values`是Thread类内部专门用来存储线程的ThreadLocal数据的，它内部有一个数组`private Object[] table`，ThreadLocal的值就存在这个table数组中。如果values的值为null，那么就需要对其进行初始化然后再将ThreadLocal的值进行存储。
**ThreadLocal数据的存储规则：ThreadLocal的值在table数组中的存储位置总是ThreadLocal的索引+1的位置。**

(2)`MessageQueue`的工作原理
1.MessageQueue其实是通过单链表来维护消息列表的，它包含两个主要操作`enqueueMessage`和`next`，前者是插入消息，后者是取出一条消息并移除。
2.next方法是一个无限循环的方法，如果消息队列中没有消息，那么next方法会一直阻塞在这里。当有新消息到来时，next方法会返回这条消息并将它从链表中移除。

(3)`Looper`的工作原理
1.为一个线程创建Looper的方法，代码如下所示
```
new Thread("test"){
    @Override
    public void run() {
        Looper.prepare();//创建looper
        Handler handler = new Handler();//可以创建handler了
        Looper.loop();//开始looper循环
    }
}.start();
```
2.Looper的`prepareMainLooper`方法主要是给主线程也就是`ActivityThread`创建Looper使用的，本质也是调用了`prepare`方法。
3.Looper的`quit`和`quitSafely`方法的区别是：前者会直接退出Looper，后者只是设定一个退出标记，然后把消息队列中的已有消息处理完毕后才安全地退出。Looper退出之后，通过Handler发送的消息就会失败，这个时候Handler的send方法会返回false。
**在子线程中，如果手动为其创建了Looper，那么在所有的事情完成以后应该调用quit方法来终止消息循环，否则这个子线程就会一直处于等待的状态，而如果退出Looper以后，这个线程就会立刻终止，因此建议不需要的时候终止Looper。**
4.Looper的`loop`方法会调用`MessageQueue`的`next`方法来获取新消息，而next是一个阻塞操作，当没有消息时，next方法会一直阻塞着在那里，这也导致了loop方法一直阻塞在那里。如果MessageQueue的next方法返回了新消息，Looper就会处理这条消息：`msg.target.dispatchMessage(msg)`，其中的`msg.target`就是发送这条消息的Handler对象。

(4)Handler的工作原理
1.Handler就是处理消息的发送和接收之后的处理；
2.Handler处理消息的过程
```
public void dispatchMessage(Message msg) {
    if (msg.callback != null) {
        handleCallback(msg);//当message是runnable的情况，也就是Handler的post方法传递的参数，这种情况下直接执行runnable的run方法
    } else {
        if (mCallback != null) {//如果创建Handler的时候是给Handler设置了Callback接口的实现，那么此时调用该实现的handleMessage方法
            if (mCallback.handleMessage(msg)) {
                return;
            }
        }
        handleMessage(msg);//如果是派生Handler的子类，就要重写handleMessage方法，那么此时就是调用子类实现的handleMessage方法
    }
}

private static void handleCallback(Message message) {
        message.callback.run();
}

/**
 * Subclasses must implement this to receive messages.
 */
public void handleMessage(Message msg) {
}
```
3.Handler还有一个特殊的构造方法，它可以通过特定的Looper来创建Handler。
```
public Handler(Looper looper){
  this(looper, null, false);
}
```
4.Android的主线程就是`ActivityThread`，主线程的入口方法就是main，其中调用了`Looper.prepareMainLooper()`来创建主线程的Looper以及MessageQueue，并通过`Looper.loop()`方法来开启主线程的消息循环。主线程内有一个Handler，即`ActivityThread.H`，它定义了一组消息类型，主要包含了四大组件的启动和停止等过程，例如`LAUNCH_ACTIVITY`等。
`ActivityThread`通过`ApplicationThread`和`AMS`进行进程间通信，AMS以进程间通信的方法完成ActivityThread的请求后会回调ApplicationThread中的`Binder`方法，然后ApplicationThread会向`H`发送消息，`H`收到消息后会将ApplicationThread中的逻辑切换到ActivityThread中去执行，即切换到主线程中去执行，这个过程就是主线程的消息循环模型。

OK，本章结束，谢谢阅读。
