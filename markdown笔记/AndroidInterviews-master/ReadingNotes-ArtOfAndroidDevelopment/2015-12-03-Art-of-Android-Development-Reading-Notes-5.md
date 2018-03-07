---
layout: post
title: Art of Android Development Reading Notes 5
categories: android
date: 2015-12-01 18:50:54
---
《Android开发艺术探索》读书笔记 (5) 第5章 理解RemoteViews <!--more-->

### 第5章 理解RemoteViews
#### 5.1 `RemoteViews`的应用
(1)RemoteViews表示的是一个view结构，它可以在其他进程中显示。由于它在其他进程中显示，为了能够更新它的界面，RemoteViews提供了一组基础的操作用于跨进程更新它的界面。
(2)RemoteViews主要用于通知栏通知和桌面小部件的开发，通知栏通知是通过`NotificationManager`的`notify`方法来实现的；桌面小部件是通过`AppWidgetProvider`来实现的，它本质上是一个广播(BroadcastReceiver)。这两者的界面都是运行在`SystemServer`进程中。
(3)RemoteViews在Notification中的应用示例
```
Notification notification = new Notification();
notification.icon = R.drawable.ic_launcher;
notification.tickerText = "hello world";
notification.when = System.currentTimeMillis();
notification.flags = Notification.FLAG_AUTO_CANCEL;
Intent intent = new Intent(this, DemoActivity_1.class);
intent.putExtra("sid", "" + sId);
PendingIntent pendingIntent = PendingIntent.getActivity(this, 0, intent, PendingIntent.FLAG_UPDATE_CURRENT);

RemoteViews remoteViews = new RemoteViews(getPackageName(), R.layout.layout_notification);
remoteViews.setTextViewText(R.id.msg, "chapter_5: " + sId);//设置textview的显示文本
remoteViews.setImageViewResource(R.id.icon, R.drawable.icon1);
PendingIntent openActivity2PendingIntent = PendingIntent.getActivity(this, 0, new Intent(this, DemoActivity_2.class), PendingIntent.FLAG_UPDATE_CURRENT);
remoteViews.setOnClickPendingIntent(R.id.open_activity2, openActivity2PendingIntent);//给图片添加点击事件
notification.contentView = remoteViews;
notification.contentIntent = pendingIntent;
NotificationManager manager = (NotificationManager)getSystemService(Context.NOTIFICATION_SERVICE);
manager.notify(sId, notification);
```
(4)RemoteViews在桌面小部件中的应用
1.定义小部件界面；
2.定义小部件配置信息：其中`updatePeriodMillis`定义小工具的自动更新周期，单位为ms。
```
<?xml version="1.0" encoding="utf-8"?>
<appwidget-provider xmlns:android="http://schemas.android.com/apk/res/android"
    android:initialLayout="@layout/widget"
    android:minHeight="84dp"
    android:minWidth="84dp"
    android:updatePeriodMillis="86400000" >
</appwidget-provider>
```
3.定义小部件的实现类：书中的示例实现了一个显示一张图片的小部件，每次点击小部件的时候图片就会旋转一周；
```
public class MyAppWidgetProvider extends AppWidgetProvider {

    public static final String TAG = "MyAppWidgetProvider";
    public static final String CLICK_ACTION = "com.ryg.chapter_5.action.CLICK";

    public MyAppWidgetProvider() {
        super();
    }

    @Override
    public void onReceive(final Context context, Intent intent) {
        super.onReceive(context, intent);
        Log.i(TAG, "onReceive : action = " + intent.getAction());

        // 这里判断是自己的action，做自己的事情，比如小工具被点击了要干啥，这里是做一个动画效果
        if (intent.getAction().equals(CLICK_ACTION)) {
            Toast.makeText(context, "clicked it", Toast.LENGTH_SHORT).show();

            new Thread(new Runnable() {
                @Override
                public void run() {
                    Bitmap srcbBitmap = BitmapFactory.decodeResource(context.getResources(), R.drawable.icon1);
                    AppWidgetManager appWidgetManager = AppWidgetManager.getInstance(context);
                    for (int i = 0; i < 37; i++) {
                        float degree = (i * 10) % 360;
                        RemoteViews remoteViews = new RemoteViews(context.getPackageName(), R.layout.widget);
                        remoteViews.setImageViewBitmap(R.id.imageView1, rotateBitmap(context, srcbBitmap, degree));
                        Intent intentClick = new Intent();
                        intentClick.setAction(CLICK_ACTION);
                        PendingIntent pendingIntent = PendingIntent.getBroadcast(context, 0, intentClick, 0);
                        remoteViews.setOnClickPendingIntent(R.id.imageView1, pendingIntent);
                        appWidgetManager.updateAppWidget(new ComponentName(context, MyAppWidgetProvider.class),remoteViews);
                        SystemClock.sleep(30);
                    }
                }
            }).start();
        }
    }

    /**
     * 每次窗口小部件被点击更新都调用一次该方法
     */
    @Override
    public void onUpdate(Context context, AppWidgetManager appWidgetManager, int[] appWidgetIds) {
        super.onUpdate(context, appWidgetManager, appWidgetIds);
        Log.i(TAG, "onUpdate");

        final int counter = appWidgetIds.length;
        Log.i(TAG, "counter = " + counter);
        for (int i = 0; i < counter; i++) {
            int appWidgetId = appWidgetIds[i];
            onWidgetUpdate(context, appWidgetManager, appWidgetId);
        }
    }

    /**
     * 窗口小部件更新
     */
    private void onWidgetUpdate(Context context, AppWidgetManager appWidgeManger, int appWidgetId) {
        Log.i(TAG, "appWidgetId = " + appWidgetId);
        RemoteViews remoteViews = new RemoteViews(context.getPackageName(), R.layout.widget);

        // "窗口小部件"点击事件发送的Intent广播
        Intent intentClick = new Intent();
        intentClick.setAction(CLICK_ACTION);
        PendingIntent pendingIntent = PendingIntent.getBroadcast(context, 0, intentClick, 0);
        remoteViews.setOnClickPendingIntent(R.id.imageView1, pendingIntent);
        appWidgeManger.updateAppWidget(appWidgetId, remoteViews);
    }

    private Bitmap rotateBitmap(Context context, Bitmap srcbBitmap, float degree) {
        Matrix matrix = new Matrix();
        matrix.reset();
        matrix.setRotate(degree);
        return Bitmap.createBitmap(srcbBitmap, 0, 0, srcbBitmap.getWidth(), srcbBitmap.getHeight(), matrix, true);
    }
}
```
4.在AndroidManifest.xml文件中声明小部件
下面的示例中包含了两个action，第一个action用于识别小部件的单击行为，而第二个action是作为小部件必须存在的action `android.appwidget.action.APPWIDGET_UPDATE`，如果不加那么就无法显示小部件。
```
<receiver android:name=".MyAppWidgetProvider" >
    <meta-data
        android:name="android.appwidget.provider"
        android:resource="@xml/appwidget_provider_info" >
    </meta-data>

    <intent-filter>
        <action android:name="com.ryg.chapter_5.action.CLICK" />
        <action android:name="android.appwidget.action.APPWIDGET_UPDATE" />
    </intent-filter>
</receiver>
```

(5)AppWidgetProvider会自动根据广播的action通过`onReceive`方法来自动分发广播，也就是调用下面不同的方法：
`onEnable`：当小部件**第一次**添加到桌面时调用，小部件可以添加多次但是只在第一次添加的时候调用；
`onUpdate`：小部件被添加时或者每次小部件更新时都会调用一次该方法，每个周期小部件都会自动更新一次；
`onDeleted`：每删除一次小部件就调用一次；
`onDisabled`：当**最后一个**该类型的小部件被删除时调用该方法；
`onReceive`：这是广播内置的方法，用于分发具体的事件给其他方法，所以该方法一般要调用`super.onReceive(context, intent);` 如果自定义了其他action的广播，就可以在调用了父类方法之后进行判断，如上面代码所示。
(6)`PendingIntent`表示一种处于Pending状态的Intent，pending表示的是即将发生的意思，它是在将来的某个不确定的时刻放生，而Intent是立刻发生。
(7)PendingIntent支持三种待定意图：启动Activity(getActivity)，启动Service(getService)，发送广播(getBroadcast)。
`PendingIntent.getActivity(Context context, in requestCode, Intent intent, int flags)`
获得一个PendingIntent，当待定意图发生时，效果相当于Context.startActivity(intent)。
第二个参数`requestCode`是PendingIntent发送方的请求码，多数情况下设为0即可，另外requestCode会影响到flags的效果。
**PendingIntent的匹配规则：如果两个PendingIntent内部的Intent相同，并且requestCode也相同，那么这两个PendingIntent就是相同的。**
**Intent的匹配规则：如果两个Intent的ComponentName和intent-filter都相同，那么这两个Intent就是相同的，Extras不参与Intent的匹配过程。**
第四个参数flags常见的类型有：`FLAG_ONE_SHOT`、`FLAG_NO_CREATE`、`FLAG_CANCEL_CURRENT`、`FLAG_UPDATE_CURRENT`。
`FLAG_ONE_SHOT`：当前描述的PendingIntent只能被调用一次，然后它就会被自动cancel。如果后续还有相同的PendingIntent，那么它们的send方法就会调用失败。对于通知栏消息来说，如果采用这个flag，那么同类的通知只能使用一次，后续的通知单击后将无法打开。
`FLAG_NO_CREATE`：当前描述的PendingIntent不会主动创建，如果当前PendingIntent之前不存在，那么getActivity、getService和getBroadcast方法会直接返回null，即获取PendingIntent失败。这个标志位使用很少。
`FLAG_CANCEL_CURRENT`：当前描述的PendingIntent如果已经存在，那么它们都会被cancel，然后系统会创建一个新的PendingIntent。对于通知栏消息来说，那些被cancel的通知单击后将无法打开。
`FLAG_UPDATE_CURRENT`：当前描述的PendingIntent如果已经存在，那么它们都会被更新，即它们的Intent中的Extras会被替换成最新的。

(8)分析`NotificationManager.nofify(id, notification)` [未测试，看着有点晕]
1.如果参数id是常量，那么多次调用notify只能弹出一个通知，后续的通知会把前面的通知完全替代掉；
2.如果参数id每次都不同，那么当PendingIntent不匹配的时候，不管采用何种标志位，这些通知之间不会相互干扰；
3.如果参数id每次都不同，且PendingIntent匹配的时候，那就要看标志位：
如果标志位是FLAG_ONE_SHOT，那么后续的通知中的PendingIntent会和第一条通知保持完全一致，包括其中的Extras，单击任何一条通知后，剩下的通知均无法再打开，当所有的通知都被清除后，会再次重复这个过程；
如果标志位是FLAG_CANCEL_CURRENT，那么只有最新的通知可以打开，之前弹出的所有通知都无法打开；
如果标志位是FLAG_UPDATE_CURRENT，那么之前弹出的通知中的PendingIntent会被更新，最终它们和最新的一条通知保持完全一致，包括其中的Extras，并且这些通知都是可以打开的。

#### 5.2 RemoteViews的内部机制
(1)RemoteViews的构造方法 `public RemoteViews(String packageName, int layoutId)`，第一个参数是当前应用的包名，第二个参数是待加载的布局文件。
(2)RemoteViews只支持部分布局和View组件，下面列举的组件的子类是不支持的
布局：`FrameLayout、LinearLayout、RelativeLayout、GridLayout`
组件：`Button、ImageButton、ImageView、TextView、ListView、GridView、ViewStub`等
(3)RemoteViews提供了一系列的set方法完成view的设置，这是通过反射完成的调用的。
例如方法`setInt(int viewId, String methodName, int value)`就是反射调用view对象的名称为methodName的方法，传入参数value，同样的还有`setBoolean`、`setLong`等。
方法`setOnClickPendingIntent(int viewId, PendingIntent pi)`用来为view添加单击事件，事件类型只能为PendingIntent。
(4)通知和小部件分别由`NotificationManager`和`AppWidgetManager`管理，而它们通过Binder分别和SystemServer进程中的`NotificationManagerService`和`AppWidgetManagerService`进行通信。所以，布局文件实际上是两个Service加载的，运行在SystemServer进程中。
(5)RemoteViews实现了`Parcelable`接口，它会通过Binder传递到SystemServer进程，系统会根据RemoteViews中的包名信息获取到应用中的资源，从而完成布局文件的加载。
(6)系统将view操作封装成`Action`对象，Action同样实现了Parcelable接口，通过Binder传递到SystemServer进程。远程进程通过RemoteViews的`apply`方法来进行view的更新操作，RemoteViews的apply方法内部则会去遍历所有的action对象并调用它们的apply方法来进行view的更新操作。
这样做的好处是不需要定义大量的Binder接口，其次批量执行RemoteViews中的更新操作提高了程序性能。
(7)RemoteViews的`apply`和`reapply`方法的区别：`apply`方法会加载布局并更新界面，而`reapply`方法则只会更新界面。
(8)`setOnClickPendingIntent`、`setPendingIntentTemplate`和`setOnClickFillIntent`的区别
`setOnClickPendingIntent`用于给普通的view添加点击事件，但是不能给集合(ListView和StackView)中的view设置点击事件，因为开销太大了。如果需要给ListView和StackView中的item添加点击事件，需要结合`setPendingIntentTemplate`和`setOnClickFillIntent`一起使用。[并没有尝试(⊙o⊙)]

#### 5.3 RemoteViews的意义
RemoteViews的最大的意义是实现了跨进程的UI更新，这节作者实现了一个模拟通知栏效果的应用来演示跨进程的UI更新，[源码传送门](https://github.com/singwhatiwanna/android-art-res/blob/master/Chapter_5/src/com/ryg/chapter_5/MainActivity.java)。

OK，本章结束，谢谢阅读。
