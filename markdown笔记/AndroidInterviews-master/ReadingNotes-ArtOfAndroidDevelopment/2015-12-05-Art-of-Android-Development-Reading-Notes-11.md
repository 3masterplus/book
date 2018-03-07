---
layout: post
title: Art of Android Development Reading Notes 11
categories: android
date: 2015-12-03 11:50:54
---
《Android开发艺术探索》读书笔记 (11) 第11章 Android的线程和线程池 <!--more-->

### 第11章 Android的线程和线程池
#### 11.1 主线程和子线程
(1)在Java中默认情况下一个进程只有一个线程，也就是主线程，其他线程都是子线程，也叫工作线程。Android中的主线程主要处理和界面相关的事情，而子线程则往往用于执行耗时操作。线程的创建和销毁的开销较大，所以如果一个进程要频繁地创建和销毁线程的话，都会采用线程池的方式。
(2)在Android中除了Thread，还有`HandlerThread`、`AsyncTask`以及`IntentService`等也都扮演着线程的角色，只是它们具有不同的特性和使用场景。**AsyncTask封装了线程池和Handler，它主要是为了方便开发者在子线程中更新UI。HandlerThread是一种具有消息循环的线程，在它的内部可以使用Handler。IntentService是一个服务，它内部采用HandlerThread来执行任务，当任务执行完毕后就会自动退出。因为它是服务的缘故，所以和后台线程相比，它比较不容易被系统杀死。**
(3)从Android 3.0开始，系统要求网络访问必须在子线程中进行，否则网络访问将会失败并抛出`NetworkOnMainThreadException`这个异常，这样做是为了避免主线程由于被耗时操作所阻塞从而出现ANR现象。
(4)AsyncTask是一个抽象泛型类，它提供了`Params`、`Progress`、`Result`三个泛型参数，如果task确实不需要传递具体的参数，那么都可以设置为`Void`。下面是它的四个核心方法，其中`doInBackground`不是在主线程执行的。
`onPreExecute`、`doInBackground`、`onProgressUpdate`、`onPostResult`

#### 11.2 Android中的线程形态
(1)`AsyncTask`的使用过程中的条件限制：
1.AsyncTask的类必须在主线程中加载，这个过程在Android 4.1及以上版本中已经被系统自动完成。
2.AsyncTask对象必须在主线程中创建，`execute`方法必须在UI线程中调用。
3.一个AsyncTask对象只能执行一次，即只能调用一次`execute`方法，否则会报运行时异常。
4.**在Android 1.6之前，AsyncTask是串行执行任务的，Android 1.6的时候AsyncTask开始采用线程池并行处理任务，但是从Android 3.0开始，为了避免AsyncTask带来的并发错误，AsyncTask又采用一个线程来串行执行任务。尽管如此，在Android 3.0以及后续版本中，我们可以使用AsyncTask的`executeOnExecutor`方法来并行执行任务。但是这个方法是Android 3.0新添加的方法，并不能在低版本上使用。**
(2)AsyncTask的原理
1.AsyncTask中有两个线程池：`SerialExecutor`和`THREAD_POOL_EXECUTOR`。前者是用于任务的排队，默认是串行的线程池；后者用于真正执行任务。AsyncTask中还有一个Handler，即`InternalHandler`，用于将执行环境从线程池切换到主线程。AsyncTask内部就是通过InternalHandler来发送任务执行的进度以及执行结束等消息。
2.AsyncTask排队执行过程：系统先把参数`Params`封装为`FutureTask`对象，它相当于Runnable；接着将FutureTask交给SerialExecutor的`execute`方法，它先把FutureTask插入到任务队列tasks中，如果这个时候没有正在活动的AsyncTask任务，那么就会执行下一个AsyncTask任务，同时当一个AsyncTask任务执行完毕之后，AsyncTask会继续执行其他任务直到所有任务都被执行为止。
(3)`HandlerThread`就是一种可以使用Handler的Thread，它的实现就是在run方法中通过`Looper.prepare()`来创建消息队列，并通过`Looper.loop()`来开启消息循环，这样在实际的使用中就允许在HandlerThread中创建Handler了，外界可以通过Handler的消息方式通知HandlerThread执行一个具体的任务。HandlerThread的run方法是一个无限循环，因此当明确不需要再使用HandlerThread的时候，可以通过它的`quit`或者`quitSafely`方法来终止线程的执行。HandlerThread的最主要的应用场景就是用在IntentService中。
(4)`IntentService`是一个继承自Service的抽象类，要使用它就要创建它的子类。IntentService适合执行一些高优先级的后台任务，这样不容易被系统杀死。IntentService的`onCreate`方法中会创建HandlerThread，并使用HandlerThread的Looper来构造一个Handler对象ServiceHandler，这样通过ServiceHandler对象发送的消息最终都会在HandlerThread中执行。IntentService会将Intent封装到Message中，通过ServiceHandler发送出去，在ServiceHandler的`handleMessage`方法中会调用IntentService的抽象方法`onHandleIntent`，所以IntentService的子类都要是实现这个方法。

#### 11.3 Android中的线程池
(1)使用线程池的好处：
1.重用线程，避免线程的创建和销毁带来的性能开销；
2.能有效控制线程池的最大并发数，避免大量的线程之间因互相抢占系统资源而导致的阻塞现象；
3.能够对线程进行简单的管理，并提供定时执行以及指定间隔循环执行等功能。
(2)`Executor`只是一个接口，真正的线程池是`ThreadPoolExecutor`。ThreadPoolExecutor提供了一系列参数来配置线程池，通过不同的参数可以创建不同的线程池，Android的线程池都是通过`Executors`提供的工厂方法得到的。
(3)ThreadPoolExecutor的构造参数
1.`corePoolSize`：核心线程数，默认情况下，核心线程会在线程中一直存活；
2.`maximumPoolSize`：最大线程数，当活动线程数达到这个数值后，后续的任务将会被阻塞；
3.`keepAliveTime`：非核心线程闲置时的超时时长，超过这个时长，闲置的非核心线程就会被回收；
4.`unit`：用于指定keepAliveTime参数的时间单位，有`TimeUnit.MILLISECONDS`、`TimeUnit.SECONDS`、`TimeUnit.MINUTES`等；
5.`workQueue`：任务队列，通过线程池的execute方法提交的Runnable对象会存储在这个参数中；
6.`threadFactory`：线程工厂，为线程池提供创建新线程的功能。它是一个接口，它只有一个方法`Thread newThread(Runnable r)`；
7.`RejectedExecutionHandler`：当线程池无法执行新任务时，可能是由于任务队列已满或者是无法成功执行任务，这个时候就会调用这个Handler的`rejectedExecution`方法来通知调用者，默认情况下，`rejectedExecution`会直接抛出一个`rejectedExecutionException`。
(4)ThreadPoolExecutor执行任务的规则：
1.如果线程池中的线程数未达到核心线程的数量，那么会直接启动一个核心线程来执行任务；
2.如果线程池中的线程数量已经达到或者超过核心线程的数量，那么任务会被插入到任务队列中排队等待执行；
3.如果在步骤2中无法将任务插入到的任务队列中，可能是任务队列已满，这个时候如果线程数量没有达到规定的最大值，那么会立刻启动非核心线程来执行这个任务；
4.如果步骤3中线程数量已经达到线程池规定的最大值，那么就拒绝执行此任务，ThreadPoolExecutor会调用`RejectedExecutionHandler`的`rejectedExecution`方法来通知调用者。
(5)AsyncTask的THREAD_POOL_EXECUTOR线程池的配置：
1.`corePoolSize`=CPU核心数+1；
2.`maximumPoolSize`=2倍的CPU核心数+1；
3.核心线程无超时机制，非核心线程在闲置时间的超时时间为`1s`；
4.任务队列的容量为`128`。
(6)Android中常见的4类具有不同功能特性的线程池：
1.`FixedThreadPool`：线程数量固定的线程池，它只有核心线程；
2.`CachedThreadPool`：线程数量不固定的线程池，它只有非核心线程；
3.`ScheduledThreadPool`：核心线程数量固定，非核心线程数量没有限制的线程池，主要用于执行定时任务和具有固定周期的任务；
4.`SingleThreadPool`：只有一个核心线程的线程池，确保了所有的任务都在同一个线程中按顺序执行。

OK，本章结束，谢谢阅读。
