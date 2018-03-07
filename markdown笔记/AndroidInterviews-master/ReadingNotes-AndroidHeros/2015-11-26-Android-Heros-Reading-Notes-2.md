---
layout: post
title: Android Heroes Reading Notes 2
categories: android
date: 2015-11-26 19:57:34
---
《Android群英传》读书笔记 (2) 第三章 控件架构与自定义控件详解 + 第四章 ListView使用技巧 + 第五章 Scroll分析 <!--more-->

### **第三章 Android控件架构与自定义控件详解**
1.Android控件架构
下图是UI界面架构图，每个Activity都有一个Window对象，通常是由PhoneWindow类来实现的。
PhoneWindow将DecorView作为整个应用窗口的根View，DecorView将屏幕分成两部分：TitleView和ContentView。
ContentView实际上是一个FrameLayout，里面容纳的就是我们在xml布局文件中定义的布局。
![img](https://hujiaweibujidao.github.io/images/androidheros_ui.png)

**为什么调用requestWindowFeature()方法一定要在setContentView()方法调用之前？**
当程序在onCreate()方法中调用setContentView()方法后，ActivityManagerService会回调onResume()方法，此时系统才会将整个DecorView添加到PhoneWindow中，并让其显示出来，从而完成界面的绘制。如果requestWindowFeature是在setContentView之后调用的话，设置就不会生效了。

2.View的测量：MeasureSpec和测量模式
MeasureSpec是一个32位的int值，其中高2位是测量的模式，低30位是测量的大小 （使用位运算是为了提高效率和节省空间）
测量模式有三种：
(1)`EXACTLY`：精确值模式，属性设置为精确数值或者`match_parent`时，系统使用的是`EXACTLY`模式
(2)`AT_MOST`：最大值模式，属性设置为`wrap_content`时，系统使用的是`AT_MOST`模式
(3)`UNSPECIFIED`：不指定大小测量模式，通常情况下在绘制自定义View时才会用到

**View类默认的onMeasure()方法只支持EXACTLY模式，所以如果在自定义View的时候不重写onMeasure方法的话，就只能使用EXACTLY模式。自定义View可以响应你指定的具体的宽高值或者是match_parent属性，但是，如果要让自定义View支持wrap_content属性的话，那么就必须要重写onMeasure方法来指定wrap_content时view的大小。**

重写onMeasure方法的最终工作就是把测量后的宽高值作为参数设置给setMeasuredDimension方法。
```java
@Override
protected void onMeasure(int widthMeasureSpec, int heightMeasureSpec) {
    super.onMeasure(widthMeasureSpec, heightMeasureSpec);
    //计算width和height
    setMeasuredDimension(width, height);
}
```

3.View和ViewGroup的绘制
View的onDraw()方法包含一个参数`Canvas`对象，使用这个Canvas对象就可以进行绘图了。

**通常情况下，Canvas对象的创建需要传入参数`Bitmap`，为什么呢？**
这是因为传进去的Bitmap与通过这个Bitmap创建的Canvas画布是紧紧联系在一起的，这个Bitmap用来存储所有绘制在Canvas上的像素信息，当使用Bitmap创建Canvas之后，后面调用所有的Canvas.drawXXX方法都发生在这个Bitmap上。

ViewGroup通常不需要绘制，因为它本身没有需要绘制的东西，如果不指定ViewGroup的背景颜色，那么ViewGroup的onDraw方法都不会被调用。但是，ViewGroup会调用dispatchDraw方法来绘制其子view，其过程同样是通过遍历所有子view并调用子view的绘制方法来完成绘制工作的。

4.自定义View(ViewGroup)
三种自定义View的方式：
(1)对现有控件进行扩展
对现有控件进行扩展的代码结构通常如下：
```
@Override
protected void onDraw(Canvas canvas) {
    //在回调父类方法之前实现自己的逻辑，对TextView来说就是在绘制文本之前
    super.onDraw(canvas);
    //在回调父类方法之后实现自己的逻辑，对TextView来说就是在绘制文本之后
}
```

例如，书中对TextView进行扩展代码节选
```
private void initView() {
    mPaint1 = new Paint();
    mPaint1.setColor(getResources().getColor(android.R.color.holo_blue_light));
    mPaint1.setStyle(Paint.Style.FILL);
    mPaint2 = new Paint();
    mPaint2.setColor(Color.YELLOW);
    mPaint2.setStyle(Paint.Style.FILL);
}

@Override
protected void onDraw(Canvas canvas) {
    // 绘制外层矩形
    canvas.drawRect(
            0,
            0,
            getMeasuredWidth(),
            getMeasuredHeight(),
            mPaint1);
    // 绘制内层矩形
    canvas.drawRect(
            10,
            10,
            getMeasuredWidth() - 10,
            getMeasuredHeight() - 10,
            mPaint2);
    canvas.save();
    // 绘制文字前平移10像素
    canvas.translate(10, 0);
    // 父类完成的方法，即绘制文本
    super.onDraw(canvas);
    canvas.restore();
}
```

(2)通过组合来实现新的控件
这种方式通常需要继承一个合适的ViewGroup，再给它添加指定功能的控件，从而组合成新的复合控件。
[项目中一般使用这种方式创建应用内统一的提示信息界面，可以是提示正在加载，也可以是提示数据出错了等]
例如，书中的TopBar例子：
```
public class TopBar extends RelativeLayout {

    // 包含topbar上的元素：左按钮、右按钮、标题
    private Button mLeftButton, mRightButton;
    private TextView mTitleView;

    // 布局属性，用来控制组件元素在ViewGroup中的位置
    private LayoutParams mLeftParams, mTitlepParams, mRightParams;

    // 左按钮的属性值，即我们在atts.xml文件中定义的属性
    private int mLeftTextColor;
    private Drawable mLeftBackground;
    private String mLeftText;
    // 右按钮的属性值，即我们在atts.xml文件中定义的属性
    private int mRightTextColor;
    private Drawable mRightBackground;
    private String mRightText;
    // 标题的属性值，即我们在atts.xml文件中定义的属性
    private float mTitleTextSize;
    private int mTitleTextColor;
    private String mTitle;

    // 映射传入的接口对象
    private TopbarClickListener mListener;
    ......
}
```

(3)重写View来实现全新的控件
创建自定义View的难点在于绘制控件和实现交互，通常需要继承View类，并重写onDraw、onMeasure等方法来实现绘制逻辑，同时通过重写onTouchEvent等触控事件方法来实现交互逻辑。
[这类自定义View是比较常用的，自己以前也写过几个简单的例子，参见[AnnotationView](https://github.com/hujiaweibujidao/AnnotationView)和[ProgressView](https://github.com/hujiaweibujidao/ProgressView)项目，或者参考之前的博文[Android Text View with Custom Font](http://hujiaweibujidao.github.io/blog/2015/07/04/android-text-view-with-custom-font/)，一个可以自定义字体的TextView]
例如，书中的弧线展示图例子
```
@Override
protected void onMeasure(int widthMeasureSpec, int heightMeasureSpec) {
    mMeasureWidth = MeasureSpec.getSize(widthMeasureSpec);
    mMeasureHeigth = MeasureSpec.getSize(heightMeasureSpec);
    setMeasuredDimension(mMeasureWidth, mMeasureHeigth);
}

@Override
protected void onDraw(Canvas canvas) {
    super.onDraw(canvas);
    // 绘制圆
    canvas.drawCircle(mCircleXY, mCircleXY, mRadius, mCirclePaint);
    // 绘制弧线
    canvas.drawArc(mArcRectF, 270, mSweepAngle, false, mArcPaint);
    // 绘制文字
    canvas.drawText(mShowText, 0, mShowText.length(), mCircleXY, mCircleXY + (mShowTextSize / 4), mTextPaint);
}
```

5.事件拦截机制分析  **[后面有专门对Android事件拦截机制分析的部分，此处略过]**

<br/>
### **第四章 ListView使用技巧**
1.使用ViewHolder模式提高效率
这种方式是必须要用的！下面的例子是一个常见的使用ViewHolder并且包含多个item type的Adapter例子：
```
public class ChatItemListViewAdapter extends BaseAdapter {

    private List<ChatItemListViewBean> mData;
    private LayoutInflater mInflater;

    public ChatItemListViewAdapter(Context context, List<ChatItemListViewBean> data) {
        this.mData = data;
        mInflater = LayoutInflater.from(context);
    }

    @Override
    public int getCount() {
        return mData.size();
    }

    @Override
    public Object getItem(int position) {
        return mData.get(position);
    }

    @Override
    public long getItemId(int position) {
        return position;
    }

    @Override
    public int getItemViewType(int position) {
        ChatItemListViewBean bean = mData.get(position);
        return bean.getType();
    }

    @Override
    public int getViewTypeCount() {
        return 2;
    }

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        ViewHolder holder;
        if (convertView == null) {
            if (getItemViewType(position) == 0) {
                holder = new ViewHolder();
                convertView = mInflater.inflate(R.layout.chat_item_itemin, null);
                holder.icon = (ImageView) convertView.findViewById(R.id.icon_in);
                holder.text = (TextView) convertView.findViewById(R.id.text_in);
            } else {
                holder = new ViewHolder();
                convertView = mInflater.inflate(R.layout.chat_item_itemout, null);
                holder.icon = (ImageView) convertView.findViewById(R.id.icon_out);
                holder.text = (TextView) convertView.findViewById(R.id.text_out);
            }
            convertView.setTag(holder);
        } else {
            holder = (ViewHolder) convertView.getTag();
        }
        holder.icon.setImageBitmap(mData.get(position).getIcon());
        holder.text.setText(mData.get(position).getText());
        return convertView;
    }

    public final class ViewHolder {
        public ImageView icon;
        public TextView text;
    }

}
```

2.listview的一些属性设置
(1)设置分隔线
`android:divider=""@android:color/white"`
`android:dividerHeight="10dp"`
`android:divider="@null"` （设置分隔线透明）
(2)隐藏滚动条
`android:scrollbars="none"`
(3)取消item的点击效果
`android:listSelector="@android:color/transparent"`

3.listview的一些方法设置
(1)设置listview显示在第几项
`listview.setSelection(n);` 这个方法类似scrollTo瞬间完成移动，平滑移动可以使用下面的方式
`listview.smoothScrollBy(distance, duration);`
`listview.smoothScrollByOffset(offset);`
`listview.smoothScrollToPosition(index);`
(2)处理空listview
`listview.setEmptyView(View)`

4.动态修改listview
**在使用adapter的notifyDataSetChanged方法时，必须保证传进adapter的数据list和发生数据变化的list是同一个对象，否则将无法看到效果。**

5.listview滑动监听
监听listview的滑动事件的方法有两种：一个是OnTouchListener来实现监听，另一个是使用OnScrollListener来实现监听。
例如，书中实现了一个监听listview上下滑动事件操纵toolbar显示和隐藏效果的例子：
```
public class ScrollHideListView extends Activity {

    private Toolbar mToolbar;
    private ListView mListView;
    private String[] mStr = new String[20];
    private int mTouchSlop;
    private float mFirstY;
    private float mCurrentY;
    private int direction;
    private ObjectAnimator mAnimator;
    private boolean mShow = true;

    View.OnTouchListener myTouchListener = new View.OnTouchListener() {
        @Override
        public boolean onTouch(View v, MotionEvent event) {
            switch (event.getAction()) {
                case MotionEvent.ACTION_DOWN:
                    mFirstY = event.getY();
                    break;
                case MotionEvent.ACTION_MOVE:
                    mCurrentY = event.getY();
                    if (mCurrentY - mFirstY > mTouchSlop) {
                        direction = 0;// down
                    } else if (mFirstY - mCurrentY > mTouchSlop) {
                        direction = 1;// up
                    }
                    if (direction == 1) {
                        if (mShow) {
                            toolbarAnim(1);//show
                            mShow = !mShow;
                        }
                    } else if (direction == 0) {
                        if (!mShow) {
                            toolbarAnim(0);//hide
                            mShow = !mShow;
                        }
                    }
                    break;
                case MotionEvent.ACTION_UP:
                    break;
            }
            return false;
        }
    };

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.scroll_hide);
        mTouchSlop = ViewConfiguration.get(this).getScaledTouchSlop();
        mToolbar = (Toolbar) findViewById(R.id.toolbar);
        mListView = (ListView) findViewById(R.id.listview);
        for (int i = 0; i < mStr.length; i++) {
            mStr[i] = "Item " + i;
        }
        View header = new View(this);
        header.setLayoutParams(new AbsListView.LayoutParams(
                AbsListView.LayoutParams.MATCH_PARENT,
                (int) getResources().getDimension( R.dimen.abc_action_bar_default_height_material)));
        mListView.addHeaderView(header);
        mListView.setAdapter(new ArrayAdapter<String>(
                ScrollHideListView.this,
                android.R.layout.simple_expandable_list_item_1,
                mStr));
        mListView.setOnTouchListener(myTouchListener);
    }

    private void toolbarAnim(int flag) {
        if (mAnimator != null && mAnimator.isRunning()) {
            mAnimator.cancel();
        }
        if (flag == 0) {
            mAnimator = ObjectAnimator.ofFloat(mToolbar, "translationY", mToolbar.getTranslationY(), 0);
        } else {
            mAnimator = ObjectAnimator.ofFloat(mToolbar, "translationY", mToolbar.getTranslationY(), -mToolbar.getHeight());
        }
        mAnimator.start();
    }
}
```

监听listview的OnScrollListener的一般实现
```
mListView.setOnScrollListener(new AbsListView.OnScrollListener() {
    @Override
    public void onScrollStateChanged(AbsListView view, int scrollState) {
        switch (scrollState){
            case SCROLL_STATE_IDLE://滑动停止时
                break;
            case SCROLL_STATE_TOUCH_SCROLL://正在滑动时
                break;
            case SCROLL_STATE_FLING://手指抛动之后listview由于惯性继续滑动
                break;
        }
    }

    @Override
    public void onScroll(AbsListView view, int firstVisibleItem, int visibleItemCount, int totalItemCount) {
        //firstVisibleItem 第一个可见的item的id
        //visibleItemCount 可见的item的总数
        //totalItemCount   所有item的总数
    }
});
```

获得当前可视的item的位置等信息的便捷方法
```
mListView.getLastVisiblePosition();//获取可视区域最后一个item的id
mListView.getFirstVisiblePosition();//获取可视区域第一个item的id
```

<br/>
### **第五章 Android Scroll分析**
1.获取坐标值的各种方法
图片来自[Android中的坐标系以及获取坐标的方法](http://www.linuxidc.com/Linux/2015-11/125391.htm)
![img](https://hujiaweibujidao.github.io/images/coordination.jpg)

2.实现滑动的基本思想
当触摸view时，系统记下当前触摸点坐标；当手指移动时，系统记下移动后的触摸点坐标，从而获取到相对于前一次坐标点的偏移量，并通过偏移量来修改view的坐标，这样不断重复，从而实现滑动过程。

3.常用的滑动实现方法
(1)修改view的left、top、right和bottom的值：调用`layout`方法或者`offsetLeftAndRight`等方法
绝对坐标系下
```
// 绝对坐标方式
@Override
public boolean onTouchEvent(MotionEvent event) {
    int rawX = (int) (event.getRawX());
    int rawY = (int) (event.getRawY());
    switch (event.getAction()) {
        case MotionEvent.ACTION_DOWN:
            // 记录触摸点坐标
            lastX = rawX;
            lastY = rawY;
            break;
        case MotionEvent.ACTION_MOVE:
            // 计算偏移量
            int offsetX = rawX - lastX;
            int offsetY = rawY - lastY;
            // 在当前left、top、right、bottom的基础上加上偏移量
            layout(getLeft() + offsetX,
                    getTop() + offsetY,
                    getRight() + offsetX,
                    getBottom() + offsetY);
            // 重新设置初始坐标
            lastX = rawX;
            lastY = rawY;
            break;
    }
    return true;
}
```
视图坐标系下
```
// 视图坐标方式
@Override
public boolean onTouchEvent(MotionEvent event) {
    int x = (int) event.getX();
    int y = (int) event.getY();
    switch (event.getAction()) {
        case MotionEvent.ACTION_DOWN:
            // 记录触摸点坐标
            lastX = x;
            lastY = y;
            break;
        case MotionEvent.ACTION_MOVE:
            // 计算偏移量
            int offsetX = x - lastX;
            int offsetY = y - lastY;
            offsetLeftAndRight(offsetX);
            offsetTopAndBottom(offsetY);
            break;
    }
    return true;
}
```

(2)修改布局参数LayoutParams：修改子view的getLayoutParams或者使用ViewGroup.MarginLayoutParams
**子view的getLayoutParams得到的LayoutParams需要和父ViewGroup的Layout类型一致，如果使用ViewGroup.MarginLayoutParams的话那就方便一些，不需要考虑父ViewGroup的具体类型。**
```
@Override
public boolean onTouchEvent(MotionEvent event) {
    int x = (int) event.getX();
    int y = (int) event.getY();
    switch (event.getAction()) {
        case MotionEvent.ACTION_DOWN:
            // 记录触摸点坐标
            lastX = (int) event.getX();
            lastY = (int) event.getY();
            break;
        case MotionEvent.ACTION_MOVE:
            // 计算偏移量
            int offsetX = x - lastX;
            int offsetY = y - lastY;
            //ViewGroup.MarginLayoutParams layoutParams = (ViewGroup.MarginLayoutParams) getLayoutParams();
            LinearLayout.LayoutParams layoutParams = (LinearLayout.LayoutParams) getLayoutParams();
            layoutParams.leftMargin = getLeft() + offsetX;
            layoutParams.topMargin = getTop() + offsetY;
            setLayoutParams(layoutParams);
            break;
    }
    return true;
}
```

(3)使用scrollTo和scrollBy方法
**scrollTo和scrollBy方法移动的是view的content，即让view的内容移动。如果在ViewGroup中使用scrollTo或者scrollBy方法，那么移动的是所有子view。但如果在view中使用，那么移动的将是view的内容，例如TextView，content就是它的文本；ImageView，content就是它的drawable对象。**
```
@Override
public boolean onTouchEvent(MotionEvent event) {
    int x = (int) event.getX();
    int y = (int) event.getY();
    switch (event.getAction()) {
        case MotionEvent.ACTION_DOWN:
            lastX = (int) event.getX();
            lastY = (int) event.getY();
            break;
        case MotionEvent.ACTION_MOVE:
            int offsetX = x - lastX;
            int offsetY = y - lastY;
            ((View) getParent()).scrollBy(-offsetX, -offsetY);//注意这里需要使用负号进行移动！
            break;
    }
    return true;
}
```

(4)使用Scroller实现平滑效果
前面的滑动都不是平滑的，而Scroller是可以实现平滑效果的，它的实现原理很简单，其实就是不断调用scrollTo和scrollBy方法来实现view的平滑移动，因为人眼的视觉暂留特性看起来就是平滑的。
使用Scroller主要有三个步骤：
1.初始化Scroller对象，一般在view初始化的时候同时初始化scroller；
2.重写view的`computeScroll`方法，`computeScroll`方法是不会自动调用的，只能通过`invalidate->draw->computeScroll`来间接调用，实现循环获取scrollX和scrollY的目的，当移动过程结束之后，`Scroller.computeScrollOffset`方法会返回false，从而中断循环；
3.调用`Scroller.startScroll`方法，将起始位置、偏移量以及移动时间(可选)作为参数传递给`startScroll`方法。

例如，书中给出的例子，子view在被拖动之后会自动平滑移动到原来的位置
```
private void ininView(Context context) {
    setBackgroundColor(Color.BLUE);
    // 初始化Scroller
    mScroller = new Scroller(context);
}

@Override
public void computeScroll() {
    super.computeScroll();
    // 判断Scroller是否执行完毕
    if (mScroller.computeScrollOffset()) {
        ((View) getParent()).scrollTo( mScroller.getCurrX(), mScroller.getCurrY());
        // 通过重绘来不断调用computeScroll
        invalidate();//很重要
    }
}

@Override
public boolean onTouchEvent(MotionEvent event) {
    int x = (int) event.getX();
    int y = (int) event.getY();
    switch (event.getAction()) {
        case MotionEvent.ACTION_DOWN:
            lastX = (int) event.getX();
            lastY = (int) event.getY();
            break;
        case MotionEvent.ACTION_MOVE:
            int offsetX = x - lastX;
            int offsetY = y - lastY;
            ((View) getParent()).scrollBy(-offsetX, -offsetY);
            break;
        case MotionEvent.ACTION_UP:
            // 手指离开时，执行滑动过程
            View viewGroup = ((View) getParent());
            mScroller.startScroll( viewGroup.getScrollX(), viewGroup.getScrollY(),
                    -viewGroup.getScrollX(), -viewGroup.getScrollY());
            invalidate();//很重要
            break;
    }
    return true;
}
```

(5)属性动画  **[后面有专门对Android动画分析的部分，此处略过]**

(6)使用ViewDragHelper
ViewDragHelper类使用较少，它是support库中DrawerLayout和SlidingPaneLayout内部实现的重要类！
之前读过类似侧边栏菜单的实现代码(SlidingMenu)，个人感觉ViewDragHelper其实是更高层次的封装，将这类效果所需的接口暴露出来以简化类似的开发工作，书中给了一个例子，介绍了ViewDragHelper的使用，实现了类似侧边栏菜单的效果
```
public class DragViewGroup extends FrameLayout {

    private ViewDragHelper mViewDragHelper;
    private View mMenuView, mMainView;
    private int mWidth;

    public DragViewGroup(Context context) {
        super(context);
        initView();
    }

    public DragViewGroup(Context context, AttributeSet attrs) {
        super(context, attrs);
        initView();
    }

    public DragViewGroup(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        initView();
    }

    @Override
    protected void onFinishInflate() {
        super.onFinishInflate();
        mMenuView = getChildAt(0);
        mMainView = getChildAt(1);
    }

    @Override
    protected void onSizeChanged(int w, int h, int oldw, int oldh) {
        super.onSizeChanged(w, h, oldw, oldh);
        mWidth = mMenuView.getMeasuredWidth();
    }

    @Override
    public boolean onInterceptTouchEvent(MotionEvent ev) {
        return mViewDragHelper.shouldInterceptTouchEvent(ev);
    }

    @Override
    public boolean onTouchEvent(MotionEvent event) {
        //将触摸事件传递给ViewDragHelper,此操作必不可少
        mViewDragHelper.processTouchEvent(event);
        return true;
    }

    private void initView() {
        mViewDragHelper = ViewDragHelper.create(this, callback);
    }

    private ViewDragHelper.Callback callback = new ViewDragHelper.Callback() {

                // 何时开始检测触摸事件
                @Override
                public boolean tryCaptureView(View child, int pointerId) {
                    //如果当前触摸的child是mMainView时开始检测
                    return mMainView == child;
                }

                // 触摸到View后回调
                @Override
                public void onViewCaptured(View capturedChild, int activePointerId) {
                    super.onViewCaptured(capturedChild, activePointerId);
                }

                // 当拖拽状态改变，比如idle，dragging
                @Override
                public void onViewDragStateChanged(int state) {
                    super.onViewDragStateChanged(state);
                }

                // 当位置改变的时候调用,常用与滑动时更改scale等
                @Override
                public void onViewPositionChanged(View changedView, int left, int top, int dx, int dy) {
                    super.onViewPositionChanged(changedView, left, top, dx, dy);
                }

                // 处理垂直滑动
                @Override
                public int clampViewPositionVertical(View child, int top, int dy) {
                    return 0;
                }

                // 处理水平滑动
                @Override
                public int clampViewPositionHorizontal(View child, int left, int dx) {
                    return left;
                }

                // 拖动结束后调用
                @Override
                public void onViewReleased(View releasedChild, float xvel, float yvel) {
                    super.onViewReleased(releasedChild, xvel, yvel);
                    //手指抬起后缓慢移动到指定位置
                    if (mMainView.getLeft() < 500) {
                        //关闭菜单，相当于Scroller的startScroll方法
                        mViewDragHelper.smoothSlideViewTo(mMainView, 0, 0);
                        ViewCompat.postInvalidateOnAnimation(DragViewGroup.this);
                    } else {
                        //打开菜单
                        mViewDragHelper.smoothSlideViewTo(mMainView, 300, 0);
                        ViewCompat.postInvalidateOnAnimation(DragViewGroup.this);
                    }
                }
            };

    @Override
    public void computeScroll() {
        if (mViewDragHelper.continueSettling(true)) {
            ViewCompat.postInvalidateOnAnimation(this);
        }
    }
}
```
OK，本节结束，谢谢阅读。


