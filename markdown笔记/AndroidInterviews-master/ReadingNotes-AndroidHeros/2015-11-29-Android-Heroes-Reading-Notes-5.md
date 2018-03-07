---
layout: post
title: Android Heroes Reading Notes 5
categories: android
date: 2015-11-29 10:15:23
---
《Android群英传》读书笔记 (5) 第十一章 搭建云端服务器 + 第十二章 Android 5.X新特性详解 + 第十三章 Android实例提高 <!--more-->

### **第十一章 搭建云端服务器**
该章主要介绍了移动后端服务的概念以及Bmob的使用，比较简单，所以略过不总结。

### **第十三章 Android实例提高**
该章主要介绍了拼图游戏和2048的小项目实例，主要是代码，所以略过不总结。

### **第十二章 Android 5.X新特性详解**
1.Material Design
(1)MD主题：“拟物扁平化”
```
@android:style/Theme.Material
@android:style/Theme.Material.Light
@android:style/Theme.Material.Light.DarkActionBar
```

(2)Color Palette 和 Palette
Color Palette颜色主题，可以通过自定义style的方式自定义颜色风格，对应的name值如下面左图所示
```
<style name="AppTheme" parent="Theme.AppCompat.Light.DarkActionBar">
    <item name="colorPrimary">@color/colorPrimary</item>
    <item name="colorPrimaryDark">@color/colorPrimaryDark</item>
    <item name="colorAccent">@color/colorAccent</item>
</style>
```
使用Palette来提取颜色，从而让主题能够动态适应当前页面的色调，做到整个app颜色基调和谐统一，使用的时候要引入依赖`com.android.support:palette-v7:x.y.z`引用。提取颜色的种类：Vibrant(充满活力的),Vibrant dark, Vibrant light, Muted(柔和的), Muted dark, Muted light。
```
Bitmap bitmap = BitmapFactory.decodeResource(getResources(), R.drawable.test);
// 创建Palette对象
Palette.generateAsync(bitmap,
        new Palette.PaletteAsyncListener() {
            @Override
            public void onGenerated(Palette palette) {
                // 通过Palette来获取对应的色调 //getDarkMutedSwatch,getDarkVibrantSwatch,getLightVibrantSwatch,getMutedSwatch
                Palette.Swatch vibrant = palette.getDarkVibrantSwatch();
                // 将颜色设置给相应的组件
                getActionBar().setBackgroundDrawable(new ColorDrawable(vibrant.getRgb()));
                getWindow().setStatusBarColor(vibrant.getRgb());
            }
        });
```
显示效果如下面右图所示
{% img /images/androidheros_colorpalette.png 200 360 %} {% img /images/androidheros_palette.png 200 360 %}

(3)阴影效果
View增加了Z属性，对应垂直方向上的高度变化，Z由elevation和translationZ两部分组成(Z=elevation+translationZ)，它们都是5.X引入的新属性。elevation是静态的成员，translationZ可以在代码中用来实现动画效果。
布局属性：`android:elevation="xxxdp"`

(4)Tinting(着色)和Clipping(裁剪)
tinting的使用就是配置tint和tintMode就可以了，tint通过修改图像的alpha遮罩来修改图像的颜色，从而达到重新着色的目的。
clipping可以改变一个view的外形，要使用它，首先需要使用ViewOutlineProvider来修改outline，然后再通过setOutlineProvider将outline作用给view。

(5)列表和卡片
RecyclerView和CardView是support-v7包中新添加的组件，使用它们需要引用依赖`com.android.support:recyclerview-v7:x.y.z`和`com.android.support:cardview-v7:x.y.z`。
RecyclerView也具有ListView一样的item复用机制，还可以直接把ViewHolder的实现封装起来，开发者只要是实现ViewHolder就行了，RecyclerView会自动回收复用每一个item。RecyclerView还引入了LayoutManager来帮助开发者方便地创建不同的布局，例如LinearLayoutManager、GridLayoutManager等，此外，为RecyclerView编写Adapter的代码也更加方便了。
```
public class RecyclerAdapter extends RecyclerView.Adapter<RecyclerAdapter.ViewHolder> {

    private List<String> mData;
    public RecyclerAdapter(List<String> data) {
        mData = data;
    }
    public OnItemClickListener itemClickListener;

    public void setOnItemClickListener(OnItemClickListener itemClickListener) {
        this.itemClickListener = itemClickListener;
    }

    public interface OnItemClickListener {
        void onItemClick(View view, int position);
    }

    public class ViewHolder extends RecyclerView.ViewHolder implements View.OnClickListener {

        public TextView textView;

        public ViewHolder(View itemView) {
            super(itemView);
            textView = (TextView) itemView;
            textView.setOnClickListener(this);
        }

        // 通过接口回调来实现RecyclerView的点击事件
        @Override
        public void onClick(View v) {
            if (itemClickListener != null) {
                itemClickListener.onItemClick(v, getPosition());
            }
        }
    }

    @Override
    public ViewHolder onCreateViewHolder(ViewGroup viewGroup, int i) {
        // 将布局转化为View并传递给RecyclerView封装好的ViewHolder
        View v = LayoutInflater.from(viewGroup.getContext()).inflate(R.layout.rc_item, viewGroup, false);
        return new ViewHolder(v);
    }

    @Override
    public void onBindViewHolder(ViewHolder viewHolder, int i) {
        // 建立起ViewHolder中视图与数据的关联
        viewHolder.textView.setText(mData.get(i));
    }

    @Override
    public int getItemCount() {
        return mData.size();
    }
}
```

CardView也是一种容器内布局，只是它提供了卡片样的形式。在XML布局文件中使用CardView的时候还需要引入其命名空间`xmlns:cardview=http://schemas.android.com/apk/res-auto`。

(6)Activity过渡动画
以前Activity过渡动画是通过`overridePendingTransition(int inAnim, int outAnim)`来实现的，效果差强人意。
现在Android 5.X提供了三种Transition类型：
**进入和退出动画**：两者又包括了`explode`(分解)、`slide`(滑动)和`fade`(淡出)三种效果；
使用方式：假设Activity从A跳转到B，那么将A中原来的`startActivity`改为如下代码：
```
startActivity(intent, ActivityOptions.makeSceneTransitionAnimation(this).toBundle());
```
然后在B的onCreate方法中添加如下代码：
```
//首先声明需要开启Activity过渡动画
getWindow().requestFeature(Window.FEATURE_CONTENT_TRANSITIONS);
//然后设置当前Activity的进入和退出动画
getWindow().setEnterTransition(new Fade());
getWindow().setExitTransition(new Fade());
```

**共享元素过渡动画**：一个共享元素过渡动画决定两个Activity之间的过渡怎么共享它们的视图，包括了
`changeBounds`：改变目标视图的布局边界；
`changeClipBounds`：裁剪目标视图的边界；
`changeTransform`：改变目标视图的缩放比例和旋转角度；
`changeImageTransform`：改变目标图片的大小和缩放比例。
使用方式：假设Activity从A跳转到B，那么将A中原来的`startActivity`改为如下代码：
```
//单个共享元素的调用方式
startActivity(intent,ActivityOptions.makeSceneTransitionAnimation(this, view, "share").toBundle());
//多个共享元素的调用方式
startActivity(intent,ActivityOptions.makeSceneTransitionAnimation(this,
                Pair.create(view, "share"),
                Pair.create(fab, "fab")).toBundle());
```
然后在B的onCreate方法中添加如下代码：
```
//声明需要开启Activity过渡动画
getWindow().requestFeature(Window.FEATURE_CONTENT_TRANSITIONS);
```
其次还要在Activity A和B的布局文件中为共享元素组件添加`android:transitionName="xxx"`属性。

(7)MD动画效果
**Ripple效果**
水波纹效果有两种：波纹有边界和波纹无边界。前者是指波纹被限制在控件的边界，后者指波纹不会限制在控件边界中，会呈圆形发放出去。
除了使用xml文件自定义ripple效果之外，还可以通过下面的代码来快速实现ripple效果
```
//波纹有边界
android:background="?android:attr/selectableItemBackground"
//波纹无边界
android:background="?android:attr/selectableItemBackgroundBorderless"
```

**Circular Reveal效果**
圆形显现效果：通过ViewAnimationUtils.createCircularReveal方法可以创建一个RevealAnimator动画，代码如下，其中`centerX/centerY`表示动画开始的位置，`startRadius`和`endRadius`分别表示动画的起始半径和结束半径。
```
public static Animator createCircularReveal(View view,
        int centerX,  int centerY, float startRadius, float endRadius) {
    return new RevealAnimator(view, centerX, centerY, startRadius, endRadius);
}
```
下面是一个例子，该例子会呈现出图片从一个点以圆形的方式放大到图片大小的动画效果：
```
final ImageView imageView = (ImageView) findViewById(R.id.imageview);
imageView.setOnClickListener(new View.OnClickListener() {
    @TargetApi(Build.VERSION_CODES.LOLLIPOP)
    @Override
    public void onClick(View v) {
        Animator animator = ViewAnimationUtils.createCircularReveal(imageView, imageView.getWidth() / 2, imageView.getHeight() / 2, 0, imageView.getWidth());
        animator.setDuration(2000);
        animator.setInterpolator(new AccelerateDecelerateInterpolator());
        animator.start();
    }
});
```

**view的状态切换动画**
在Android 5.X中，可以使用动画来作为视图改变的效果，有两种方式来实现该动画：StateListAnimator和animated-selector。
StateListAnimator是将动画效果(objectAnimator)配置到原来的selector的item中来实现的，看下面的例子：
```
//定义StateListAnimator
<?xml version="1.0" encoding="utf-8"?>
<selector xmlns:android="http://schemas.android.com/apk/res/android">
    <item android:state_checked="true">
        <set>
            <objectAnimator android:propertyName="rotationX"
                            android:duration="@android:integer/config_shortAnimTime"
                            android:valueTo="360"
                            android:valueType="floatType"/>
        </set>
    </item>
    <item android:state_checked="false">
        <set>
            <objectAnimator android:propertyName="rotationX"
                            android:duration="@android:integer/config_shortAnimTime"
                            android:valueTo="0"
                            android:valueType="floatType"/>
        </set>
    </item>
</selector>

//将StateListAnimator设置给checkbox
<CheckBox
    android:layout_width="wrap_content"
    android:layout_height="wrap_content"
    android:layout_centerInParent="true"
    android:text="state list animator"
    android:stateListAnimator="@drawable/anim_change"/>
```

animated-selector是一个状态改变的动画效果selector，MD中很多控件设计用到了animated-selector，例如check-box，下面便是一个类似check-box效果的例子：
```
<animated-selector xmlns:android="http://schemas.android.com/apk/res/android">
    <item
        android:id="@+id/state_on"
        android:state_checked="true">
        <bitmap android:src="@drawable/ic_done_anim_030" />
    </item>
    <item android:id="@+id/state_off">
        <bitmap android:src="@drawable/ic_plus_anim_030" />
    </item>
    <transition
        android:fromId="@+id/state_on"
        android:toId="@+id/state_off">
        <animation-list>
            <item android:duration="16">
                <bitmap android:src="@drawable/ic_plus_anim_000" />
            </item>
            ...
            <item android:duration="16">
                <bitmap android:src="@drawable/ic_plus_anim_030" />
            </item>
        </animation-list>
    </transition>
    <transition
        android:fromId="@+id/state_off"
        android:toId="@+id/state_on">
        <animation-list>
            <item android:duration="16">
                <bitmap android:src="@drawable/ic_done_anim_000" />
            </item>
            ...
            <item android:duration="16">
                <bitmap android:src="@drawable/ic_done_anim_030" />
            </item>
        </animation-list>
    </transition>
</animated-selector>

```

(8)Toolbar
Toolbar和ActionBar以前灰常详细地介绍过，此处略过不总结，[点击这里查看](https://hujiaweibujidao.github.io/blog/2015/06/02/android-ui-2-toolbar/)。

(9)Notification
Android 5.x改进了通知栏，优化了Notification，现在共有三种类型的Notification：
基本Notification：最基本的通知，只有icon，text，时间等信息
折叠式Notification：可以折叠的通知，有两种显示状态：一种普通状态，另一种是展开状态
悬挂式Notification：在屏幕上方显示通知，且不会打断用户操作

三种类型的notification的使用如下所示：
```
public void basicNotify(View view) {
    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse("http://www.baidu.com"));
    PendingIntent pendingIntent = PendingIntent.getActivity(this, 0, intent, 0);
    Notification.Builder builder = new Notification.Builder(this);
    builder.setSmallIcon(R.drawable.ic_launcher);// 设置Notification的各种属性
    builder.setContentIntent(pendingIntent);
    builder.setAutoCancel(true);
    builder.setLargeIcon(BitmapFactory.decodeResource(getResources(), R.drawable.ic_launcher));
    builder.setContentTitle("Basic Notifications");
    builder.setContentText("I am a basic notification");
    builder.setSubText("it is really basic");
    // 通过NotificationManager来发出Notification
    NotificationManager notificationManager = (NotificationManager) getSystemService(NOTIFICATION_SERVICE);
    notificationManager.notify(NOTIFICATION_ID_BASIC, builder.build());
}

public void collapsedNotify(View view) {
    Intent intent = new Intent(Intent.ACTION_VIEW, Uri.parse("http://www.sina.com"));
    PendingIntent pendingIntent = PendingIntent.getActivity(this, 0, intent, 0);
    Notification.Builder builder = new Notification.Builder(this);
    builder.setSmallIcon(R.drawable.ic_launcher);
    builder.setContentIntent(pendingIntent);
    builder.setAutoCancel(true);
    builder.setLargeIcon(BitmapFactory.decodeResource(getResources(), R.drawable.ic_launcher));
    // 通过RemoteViews来创建自定义的Notification视图
    RemoteViews contentView = new RemoteViews(getPackageName(), R.layout.notification);
    contentView.setTextViewText(R.id.textView, "show me when collapsed");
    Notification notification = builder.build();
    notification.contentView = contentView;
    // 通过RemoteViews来创建自定义的Notification视图
    RemoteViews expandedView = new RemoteViews(getPackageName(), R.layout.notification_expanded);
    notification.bigContentView = expandedView;

    NotificationManager nm = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
    nm.notify(NOTIFICATION_ID_COLLAPSE, notification);
}

public void headsupNotify(View view) {
    Notification.Builder builder = new Notification.Builder(this)
            .setSmallIcon(R.drawable.ic_launcher)
            .setPriority(Notification.PRIORITY_DEFAULT)
            .setCategory(Notification.CATEGORY_MESSAGE)
            .setContentTitle("Headsup Notification")
            .setContentText("I am a Headsup notification.");

    Intent push = new Intent();
    push.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK);
    push.setClass(this, MainActivity.class);
    PendingIntent pendingIntent = PendingIntent.getActivity(this, 0, push, PendingIntent.FLAG_CANCEL_CURRENT);
    builder.setContentText("Heads-Up Notification on Android 5.0").setFullScreenIntent(pendingIntent, true);

    NotificationManager nm = (NotificationManager)getSystemService(NOTIFICATION_SERVICE);
    nm.notify(NOTIFICATION_ID_HEADSUP, builder.build());
}
```
显示效果如下：

{% img https://hujiaweibujidao.github.io/images/androidheros_basicnotification.png 200 360 %} {% img https://hujiaweibujidao.github.io/images/androidheros_collapsenotification.png 200 360 %} {% img https://hujiaweibujidao.github.io/images/androidheros_headsupnotification.png 200 360 %}

**通知的显示等级**
Android 5.x将通知分为了三个等级：
`VISIBILITY_PRIVATE`：表明只有当没有锁屏的时候才会显示；
`VISIBILITY_PUBLIC`：表明任何情况下都会显示；
`VISIBILITY_SECRET`：表明在pin、password等安全锁和没有锁屏的情况下才会显示；
设置等级的方式是`builder.setVisibility(Notification.VISIBILITY_PRIVATE);`

**其他学习资料**
1.[使用 Android Design 支持库构建 Material Design Android 应用](https://www.code-labs.io/codelabs/Material-Design-Style/index.html)

OK，本节结束，谢谢阅读。


