---
layout: post
title: Android Heroes Reading Notes 3
categories: android
date: 2015-11-27 10:21:56
---
《Android群英传》读书笔记 (3) 第六章 Android绘图机制与处理技巧 + 第七章 Android动画机制与使用技巧 <!--more-->

### **第六章 Android绘图机制与处理技巧**
1.屏幕尺寸信息
屏幕大小：屏幕对角线长度，单位“寸”；
分辨率：手机屏幕像素点个数，例如720x1280分辨率；
PPI(Pixels Per Inch)：即DPI(Dots Per Inch)，它是对角线的像素点数除以屏幕大小得到的；
系统屏幕密度：android系统定义了几个标准的DPI值作为手机的固定DPI。
**注：下图中有两处笔误，hdpi应该是480x800，xxhdpi应该是1080x1920**
![img](https://hujiaweibujidao.github.io/images/androidheros_dpi.png)
独立像素密度(DP)：android系统使用mdpi屏幕作为标准，在这个屏幕上1dp=1px，其他屏幕可以通过比例进行换算。在hdpi中，1dp=1.5px。在xhdpi中，1dp=2px。在xxhdpi中，1dp=3px。
单位转换：常用的单位转换的辅助类DisplayUtil
```
/**
 * 常用单位转换的辅助类
 */
public class DisplayUtil {

    /**
     * dp转px
     */
    public static int dp2px(Context context, float dpVal) {
        return (int) TypedValue.applyDimension(TypedValue.COMPLEX_UNIT_DIP,
                dpVal, context.getResources().getDisplayMetrics());
    }

    /**
     * sp转px
     */
    public static int sp2px(Context context, float spVal) {
        return (int) TypedValue.applyDimension(TypedValue.COMPLEX_UNIT_SP,
                spVal, context.getResources().getDisplayMetrics());
    }

    /**
     * px转dp
     */
    public static float px2dp(Context context, float pxVal) {
        final float scale = context.getResources().getDisplayMetrics().density;
        return (pxVal / scale);
    }

    /**
     * px转sp
     */
    public static float px2sp(Context context, float pxVal) {
        return (pxVal / context.getResources().getDisplayMetrics().scaledDensity);
    }
}
```

2.2D绘图基础
(1)Canvas对象
`drawPoint`，`drawLine`，`drawRect`，`drawRoundRect`，`drawCircle`，`drawArc`，`drawOval`，`drawText`，`drawPosText`(在指定位置绘制文本)，`drawPath`(绘制路径)

(2)Paint对象
`setAntiAlias`：设置画笔的锯齿效果
`setColor`：设置画笔的颜色
`setARGB`：设置画笔的A、R、G、B值
`setAlpha`：设置画笔的透明度值
`setTextSize`：设置字体大小
`setStyle`：设置画笔的效果（空心STROKE或者实心FILL）
`setStrokeWidth`：设置空心边框的宽度

3.Android XML绘图
(1)Bitmap
在XML中定义Bitmap的语法
```
<?xml version="1.0" encoding="utf-8"?>
<bitmap
    xmlns:android="http://schemas.android.com/apk/res/android"
    android:src="@[package:]drawable/drawable_resource"
    android:antialias=["true" | "false"]
    android:dither=["true" | "false"]
    android:filter=["true" | "false"]
    android:gravity=["top" | "bottom" | "left" | "right" | "center_vertical" |
                      "fill_vertical" | "center_horizontal" | "fill_horizontal" |
                      "center" | "fill" | "clip_vertical" | "clip_horizontal"]
    android:tileMode=["disabled" | "clamp" | "repeat" | "mirror"] />
```

(2)Shape
在XML中定义Shape的语法
```
<?xml version="1.0" encoding="utf-8"?>
<shape    
    xmlns:android="http://schemas.android.com/apk/res/android"    
    android:shape=["rectangle" | "oval" | "line" | "ring"] >    
    <corners        //当shape为rectangle时使用
        android:radius="integer"        //半径值会被后面的单个半径属性覆盖，默认为1dp
        android:topLeftRadius="integer"        
        android:topRightRadius="integer"        
        android:bottomLeftRadius="integer"        
        android:bottomRightRadius="integer" />    
    <gradient       //渐变
        android:angle="integer"        
        android:centerX="integer"        
        android:centerY="integer"        
        android:centerColor="integer"        
        android:endColor="color"        
        android:gradientRadius="integer"        
        android:startColor="color"        
        android:type=["linear" | "radial" | "sweep"]        
        android:useLevel=["true" | "false"] />    
    <padding        //内边距
        android:left="integer"        
        android:top="integer"        
        android:right="integer"        
        android:bottom="integer" />    
    <size           //指定大小，一般用在imageview配合scaleType属性使用
        android:width="integer"        
        android:height="integer" />    
    <solid          //填充颜色
        android:color="color" />    
   	<stroke         //边框
      	android:width="integer"        
        android:color="color"        
        android:dashWidth="integer"        
        android:dashGap="integer" />
</shape>
```

(3)Layer
在XML中定义Layer的语法，layer类似PS中图层的概念，语法如下
```
<layer-list xmlns:android="http://schemas.android.com/apk/res/android">
    <item android:drawable="@[package:]drawable/drawable_resource" />
    <item android:drawable="@[package:]drawable/drawable_resource" />
    ......
</layer-list>
```

(4)Selector
selector的用法很多，一般是定义控件在不同状态下的显示形态，可以是图片drawable，也可以是形状shape，还可以只是颜色color！
```
<?xml version="1.0" encoding="utf-8" ?>
<selector xmlns:android="http://schemas.android.com/apk/res/android">
    <!-- 默认时的背景图片-->
    <item android:drawable="@drawable/pic1"/>
    <!-- 没有焦点时的背景图片 -->
    <item android:drawable="@drawable/pic1" android:state_window_focused="false"/>
    <!-- 非触摸模式下获得焦点并单击时的背景图片 -->
    <item android:drawable="@drawable/pic2" android:state_focused="true" android:state_pressed="true"/>
    <!-- 触摸模式下单击时的背景图片-->
    <item android:drawable="@drawable/pic3" android:state_focused="false" android:state_pressed="true"/>
    <!--选中时的图片背景-->
    <item android:drawable="@drawable/pic4" android:state_selected="true"/>
    <!--获得焦点时的图片背景-->
    <item android:drawable="@drawable/pic5" android:state_focused="true"/>
</selector>
```

selector与shape结合的例子
```
<?xml version="1.0" encoding="utf-8"?>
<selector xmlns:android="http://schemas.android.com/apk/res/android">
    <!-- 定义当button处于pressed状态时的状态-->
    <item android:state_pressed="true">
        <shape>
            <gradient android:startColor="#8600ff"/>
            <stroke android:width="2dp" android:color="#000000"/>
            <corners android:radius="5dp"/>
            <padding android:bottom="10dp" android:left="10dp"
                     android:right="10dp" android:top="10dp"/>
        </shape>
    </item>
    <!-- 定义当button获得焦点时的状态-->
    <item android:state_focused="true">
        <shape>
            <gradient android:startColor="#eac100"/>
            <stroke android:width="2dp" android:color="#333333"/>
            <corners android:radius="8dp"/>
            <padding android:bottom="10dp" android:left="10dp"
                     android:right="10dp" android:top="10dp"/>
        </shape>
    </item>
</selector>
```

selector可以用来指定不同状态下文本的颜色，例如按钮上的文本的颜色
```
<?xml version="1.0" encoding="utf-8"?>
<selector xmlns:android="http://schemas.android.com/apk/res/android">
    <item android:color="#999" android:state_selected="true"/>
    <item android:color="#666" android:state_focused="true"/>
    <item android:color="#333" android:state_pressed="true"/>
    <item android:color="#000"/>
</selector>
```

结合这篇博文[Android开发：shape和selector和layer-list](http://blog.csdn.net/brokge/article/details/9713041)以及博主的实现的[圆角镂空按钮](http://blog.csdn.net/brokge/article/details/41318117)例子(综合使用了Shape、Layer和Selector实现了圆角镂空按钮)一起看还是挺不错的。

4.Android绘图技巧
(1)Canvas 画布
四个主要方法：
`save`：保存画布，将之前绘制的内容保存起来；
`restore`：合并画布，将save方法之后绘制的内容与之前绘制的内容合并起来；
`translate`：移动画布，其实是画布所在的坐标系的移动；
`rotate`：旋转画布，其实是画布所在的坐标系的旋转。
后面两个方法主要是用来方便在某些特殊情况下的绘制，例如书中介绍的仪表盘的绘制
```
@Override
protected void onDraw(Canvas canvas) {
    // 获取宽高参数
    mWidth = getMeasuredWidth();
    mHeight = getMeasuredHeight();
    // 画外圆
    Paint paintCircle = new Paint();
    paintCircle.setStyle(Paint.Style.STROKE);
    paintCircle.setAntiAlias(true);
    paintCircle.setStrokeWidth(5);
    canvas.drawCircle(mWidth / 2, mHeight / 2, mWidth / 2, paintCircle);
    // 画刻度
    Paint painDegree = new Paint();
    paintCircle.setStrokeWidth(3);
    for (int i = 0; i < 24; i++) {
        // 区分整点与非整点
        if (i == 0 || i == 6 || i == 12 || i == 18) {
            painDegree.setStrokeWidth(5);
            painDegree.setTextSize(30);
            canvas.drawLine(mWidth / 2, mHeight / 2 - mWidth / 2,
                    mWidth / 2, mHeight / 2 - mWidth / 2 + 60,
                    painDegree);
            String degree = String.valueOf(i);
            canvas.drawText(degree,
                    mWidth / 2 - painDegree.measureText(degree) / 2,
                    mHeight / 2 - mWidth / 2 + 90,
                    painDegree);
        } else {
            painDegree.setStrokeWidth(3);
            painDegree.setTextSize(15);
            canvas.drawLine(mWidth / 2, mHeight / 2 - mWidth / 2,
                    mWidth / 2, mHeight / 2 - mWidth / 2 + 30,
                    painDegree);
            String degree = String.valueOf(i);
            canvas.drawText(degree,
                    mWidth / 2 - painDegree.measureText(degree) / 2,
                    mHeight / 2 - mWidth / 2 + 60,
                    painDegree);
        }
        // 通过旋转画布简化坐标运算
        canvas.rotate(15, mWidth / 2, mHeight / 2);
    }
    // 画圆心
    Paint paintPointer = new Paint();
    paintPointer.setStrokeWidth(30);
    canvas.drawPoint(mWidth / 2, mHeight / 2, paintPointer);
    // 画指针
    Paint paintHour = new Paint();
    paintHour.setStrokeWidth(20);
    Paint paintMinute = new Paint();
    paintMinute.setStrokeWidth(10);
    canvas.save();
    canvas.translate(mWidth / 2, mHeight / 2);
    canvas.drawLine(0, 0, 100, 100, paintHour);
    canvas.drawLine(0, 0, 100, 200, paintMinute);
    canvas.restore();
}
```

(2)Layer 图层
在Android中图层是基于栈的结构来管理的，通过调用`saveLayer`、`saveLayerAlpha`方法来创建图层，使用`restore`、`restoreToCount`方法将一个图层入栈。入栈的时候，后面所有的操作都发生在这个图层上，而出栈的时候则会把图像绘制在上层Canvas上。
```
@Override
protected void onDraw(Canvas canvas) {
    canvas.drawColor(Color.WHITE);
    mPaint.setColor(Color.BLUE);
    canvas.drawCircle(150, 150, 100, mPaint);

    canvas.saveLayerAlpha(0, 0, 400, 400, 127, LAYER_FLAGS);
    mPaint.setColor(Color.RED);
    canvas.drawCircle(200, 200, 100, mPaint);
    canvas.restore();
}
```

仪表盘和Layer图层效果如下：
{% img https://hujiaweibujidao.github.io/images/androidheros_canvas.png 200 360 %} &nbsp;&nbsp; {% img https://hujiaweibujidao.github.io/images/androidheros_layer.png 200 360 %}

5.Android图像处理 **[TODO：该部分略过了，自己暂时用的比较少，等需要用的时候学习下再补充]**
色彩特效处理、图形特效处理、画笔特效处理

6.SurfaceView
**SurfaceView与View的区别**
(1)View主要适用于主动更新的情况下，而SurfaceView主要适用于被动更新，例如频繁地刷新；
(2)View在主线程中对画面进行刷新，而SurfaceView通常会通过一个子线程来进行页面刷新；
(3)View在绘图时没有使用双缓冲机制，而SurfaceView在底层实现机制中就已经实现了双缓冲机制。

SurfaceView的使用
(1)创建SurfaceView，一般继承自SurfaceView，并实现接口SurfaceHolderCallback。
SurfaceHolderCallback接口的三个回调方法
```
@Override
public void surfaceCreated(SurfaceHolder holder) {
  //做一些初始化操作，例如开启子线程通过循环来实现不停地绘制
}

@Override
public void surfaceChanged(SurfaceHolder holder, int format, int width, int height) {
}

@Override
public void surfaceDestroyed(SurfaceHolder holder) {
}
```

(2)初始化SurfaceView
初始化SurfaceHolder对象，并设置Callback
```
private void initView() {
    mHolder = getHolder();
    mHolder.addCallback(this);
    ......
}
```

(3)使用SurfaceView
通过lockCanvas方法获取Canvas对象进行绘制，并通过unlockCanvasAndPost方法对画布内容进行提交
**需要注意的是每次调用lockCanvas拿到的Canvas都是同一个Canvas对象，所以之前的操作都会保留，如果需要擦除，可以在绘制之前调用`drawColor`方法来进行清屏。**
```
private void draw() {
    try {
        mCanvas = mHolder.lockCanvas();
        //mCanvas draw something
    } catch (Exception e) {
    } finally {
        if (mCanvas != null)
            mHolder.unlockCanvasAndPost(mCanvas);
    }
}
```

书中使用SurfaceView实现了简易画板
```
public class SimpleDraw extends SurfaceView implements SurfaceHolder.Callback, Runnable {

    private SurfaceHolder mHolder;
    private Canvas mCanvas;
    private boolean mIsDrawing;
    private Path mPath;
    private Paint mPaint;

    public SimpleDraw(Context context) {
        super(context);
        initView();
    }

    public SimpleDraw(Context context, AttributeSet attrs) {
        super(context, attrs);
        initView();
    }

    public SimpleDraw(Context context, AttributeSet attrs,
                      int defStyle) {
        super(context, attrs, defStyle);
        initView();
    }

    private void initView() {
        mHolder = getHolder();
        mHolder.addCallback(this);
        setFocusable(true);
        setFocusableInTouchMode(true);
        this.setKeepScreenOn(true);
        mPath = new Path();
        mPaint = new Paint();
        mPaint.setColor(Color.RED);
        mPaint.setStyle(Paint.Style.STROKE);
        mPaint.setStrokeWidth(40);
    }

    @Override
    public void surfaceCreated(SurfaceHolder holder) {
        mIsDrawing = true;
        new Thread(this).start();
    }

    @Override
    public void surfaceChanged(SurfaceHolder holder, int format, int width, int height) {
    }

    @Override
    public void surfaceDestroyed(SurfaceHolder holder) {
        mIsDrawing = false;
    }

    @Override
    public void run() {
        long start = System.currentTimeMillis();
        while (mIsDrawing) {
            draw();
        }
        long end = System.currentTimeMillis();
        // 50 - 100ms，经验值
        if (end - start < 100) {
            try {
                Thread.sleep(100 - (end - start));
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
        }
    }

    private void draw() {
        try {
            mCanvas = mHolder.lockCanvas();
            mCanvas.drawColor(Color.WHITE);
            mCanvas.drawPath(mPath, mPaint);
        } catch (Exception e) {
        } finally {
            if (mCanvas != null)
                mHolder.unlockCanvasAndPost(mCanvas);
        }
    }

    @Override
    public boolean onTouchEvent(MotionEvent event) {
        int x = (int) event.getX();
        int y = (int) event.getY();
        switch (event.getAction()) {
            case MotionEvent.ACTION_DOWN:
                mPath.moveTo(x, y);
                break;
            case MotionEvent.ACTION_MOVE:
                mPath.lineTo(x, y);
                break;
            case MotionEvent.ACTION_UP:
                break;
        }
        return true;
    }
}
```

<br/>
### **第七章 Android动画机制与使用技巧**
1.View动画 （视图动画）
视图动画(Animation)框架定义了透明度(AlphaAnimation)、旋转(RotateAnimation)、缩放(ScaleAnimation)和位移(TranslateAnimation)几种常见的动画，控制的是整个View，所以视图动画的缺陷就在于当某个元素发生视图动画后，其响应事件的位置还依然停留在原来的地方！
**实现原理是每次绘制视图时View所在的ViewGroup中的drawChild方法获取该View的Animation的Transformation值，然后调用canvas.concat(transformationToApply.getMatrix())，通过矩阵运算完成动画帧。如果动画没有完成，就继续调用invalidate方法，启动下次绘制来驱动动画，从而完成整个动画的绘制。**
动画集合(AnimationSet)：将多个视图动画组合起来
动画监听器(AnimationListener)：提供动画的监听回调方法

2.属性动画
Android 3.0之后添加了属性动画(Animator)框架，其中核心类ObjectAnimator能够自动驱动，在不影响动画效果的情况下减少CPU资源消耗。

**ObjectAnimator**
创建ObjectAnimator只需通过它的静态工厂方法直接返回一个ObjectAnimator对象，参数包括view对象，以及view的属性名字，这个属性必须要有get/set方法，因为ObjectAnimator内部会通过反射机制来修改属性值。常用的可以直接使用属性动画的属性包括：
(1)`translationX`和`translationY`：控制view从它布局容器左上角坐标偏移的位置；
(2)`rotation`、`rotationX`和`rotationY`：控制view围绕支点进行2D和3D旋转；
(3)`scaleX`和`scaleY`：控制view围绕着它的支点进行2D缩放；
(4)`pivotX`和`pivotY`：控制支点位置，围绕这个支点进行旋转和缩放处理。默认情况下，支点是view的中心点；
(5)`x`和`y`：控制view在它的容器中的最终位置，它是最初的左上角坐标和translationX、translationY的累计和；
(6)`alpha`：控制透明度，默认是1（不透明）。

ObjectAnimator的常见使用方式如下：
```
ObjectAnimator animator = ObjectAnimator.ofFloat(view, "translationX", 300);
animator.setDuration(1000);
animator.start();
```

属性动画集合AnimatorSet：控制多个动画的协同工作方式，常用方法`animatorSet.play().with().before().after()`、`playTogether`、`playSequentially`等方法来精确控制动画播放顺序。使用`PropertyValueHolder`也可以实现简单的动画集合效果。

动画监听器：监听动画事件可以使用`AnimatorListener`或者简易的适配器`AnimatorListenerAdapter`

**如果一个属性没有get/set方法怎么办？**
(1)自定义包装类，间接地给属性提供get/set方法，下面就是一个包装类的例子，为width属性提供了get/set方法
```
public class WrapperView {

    private View mView;

    public WrapperView(View mView){
        this.mView = mView;
    }

    public int getWidth(){
        return mView.getLayoutParams().width;
    }

    public void setWidth(int width){
        mView.getLayoutParams().width = width;
        mView.requestLayout();
    }
}
```

(2)使用`ValueAnimator`
ObjectAnimator就是继承自ValueAnimator的，它是属性动画的核心，ValueAnimator不提供任何动画效果，它就是一个数值产生器，用来产生具有一定规律的数字，从而让调用者来控制动画的实现过程，控制的方式是使用`AnimatorUpdateListener`来监听数值的变换。
```
ValueAnimator animator = ValueAnimator.ofFloat(0,100);
animator.setTarget(view);
animator.setDuration(1000);
animator.start();
animator.addUpdateListener(new ValueAnimator.AnimatorUpdateListener() {
    @Override
    public void onAnimationUpdate(ValueAnimator animation) {
        Float value = (Float) animation.getAnimatedValue();
        //do the animation!
    }
});
```

**在XML中使用属性动画**
下面是一个简单例子：
```
<objectAnimator xmlns:android="http://schemas.android.com/apk/res/android"
    android:duration="4000"
    android:propertyName="rotation"
    android:valueFrom="0"
    android:valueTo="360" />
```
在代码中使用方式如下： **[注：测试该代码的时候，上面的xml定义应该放在res的animator目录下，放在anim目录下不行]**
```
Animator animator = AnimatorInflater.loadAnimator(this, R.animator.animator_rotation);
animator.setTarget(view);
animator.start();
```

**View的animate方法**
Android 3.0之后View新增了animate方法直接驱动属性动画，它其实是属性动画的一种简写方式
```
imageView.animate().alpha(0).y(100).setDuration(1000)
        .setListener(new Animator.AnimatorListener() {
            @Override
            public void onAnimationStart(Animator animation) {
            }

            @Override
            public void onAnimationEnd(Animator animation) {
            }

            @Override
            public void onAnimationCancel(Animator animation) {
            }

            @Override
            public void onAnimationRepeat(Animator animation) {
            }
        });
```

3.布局动画
布局动画是作用在ViewGroup上的，给ViewGroup添加view时添加动画过渡效果。
(1)简易方式（但是没有什么效果）：在xml中添加如下属性 `android:animateLayoutChanges="true`
(2)通过`LayoutAnimationController`来自定义子view的过渡效果，下面是一个常见的使用例子：
```
LinearLayout linearLayout = (LinearLayout) findViewById(R.id.ll);
ScaleAnimation scaleAnimation = new ScaleAnimation(0,1,0,1);
scaleAnimation.setDuration(2000);
LayoutAnimationController controller = new LayoutAnimationController(scaleAnimation, 0.5f);
controller.setOrder(LayoutAnimationController.ORDER_NORMAL);//NORMAL 顺序 RANDOM 随机 REVERSE 反序
linearLayout.setLayoutAnimation(controller);
```

4.自定义动画
创建自定义动画就是要实现它的`applyTransformation`的逻辑，不过通常还需要覆盖父类的`initialize`方法来实现初始化工作。
下面是一个模拟电视机关闭的动画，
```
public class CustomTV extends Animation {

    private int mCenterWidth;
    private int mCenterHeight;

    @Override
    public void initialize(int width, int height, int parentWidth, int parentHeight) {
        super.initialize(width, height, parentWidth, parentHeight);
        setDuration(1000);// 设置默认时长
        setFillAfter(true);// 动画结束后保留状态
        setInterpolator(new AccelerateInterpolator());// 设置默认插值器
        mCenterWidth = width / 2;
        mCenterHeight = height / 2;
    }

    @Override
    protected void applyTransformation(float interpolatedTime, Transformation t) {
        final Matrix matrix = t.getMatrix();
        matrix.preScale(1, 1 - interpolatedTime, mCenterWidth, mCenterHeight);
    }
}
```
applyTransformation方法的第一个参数interpolatedTime是插值器的时间因子，取值在0到1之间；第二个参数Transformation是矩阵的封装类，一般使用它来获得当前的矩阵Matrix对象，然后对矩阵进行操作，就可以实现动画效果了。

**如何实现3D动画效果呢？**
使用`android.graphics.Camera`中的Camera类，它封装了OpenGL的3D动画。可以把Camera想象成一个真实的摄像机，当物体固定在某处时，只要移动摄像机就能拍摄到具有立体感的图像，因此通过它可以实现各种3D效果。
下面是一个3D动画效果的例子
```
public class CustomAnim extends Animation {

    private int mCenterWidth;
    private int mCenterHeight;
    private Camera mCamera = new Camera();
    private float mRotateY = 0.0f;

    @Override
    public void initialize(int width, int height, int parentWidth, int parentHeight) {
        super.initialize(width, height, parentWidth, parentHeight);
        setDuration(2000);// 设置默认时长
        setFillAfter(true);// 动画结束后保留状态
        setInterpolator(new BounceInterpolator());// 设置默认插值器
        mCenterWidth = width / 2;
        mCenterHeight = height / 2;
    }

    // 暴露接口-设置旋转角度
    public void setRotateY(float rotateY) {
        mRotateY = rotateY;
    }

    @Override
    protected void applyTransformation( float interpolatedTime, Transformation t) {
        final Matrix matrix = t.getMatrix();
        mCamera.save();
        mCamera.rotateY(mRotateY * interpolatedTime);// 使用Camera设置旋转的角度
        mCamera.getMatrix(matrix);// 将旋转变换作用到matrix上
        mCamera.restore();
        // 通过pre方法设置矩阵作用前的偏移量来改变旋转中心
        matrix.preTranslate(mCenterWidth, mCenterHeight);
        matrix.postTranslate(-mCenterWidth, -mCenterHeight);
    }
}
```

5.Android 5.X SVG矢量动画机制 **[TODO：该部分略过了，自己暂时用的比较少，等需要用的时候学习下再补充]**
本章最后还有几个很常用的动画实例，感兴趣可以看下。

OK，本节结束，谢谢阅读。


