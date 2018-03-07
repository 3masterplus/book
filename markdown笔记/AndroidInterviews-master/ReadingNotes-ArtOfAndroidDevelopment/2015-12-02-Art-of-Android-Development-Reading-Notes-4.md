---
layout: post
title: Art of Android Development Reading Notes 4
categories: android
date: 2015-12-01 14:50:54
---
《Android开发艺术探索》读书笔记 (4) 第4章 View的工作原理 <!--more-->

本节和《Android群英传》中的**第3章Android控件架构与自定义控件详解**有关系，[建议先阅读该章的总结](https://hujiaweibujidao.github.io/blog/2015/11/26/Android-Heros-Reading-Notes-2/)

### 第4章 View的工作原理
#### 4.1 初始ViewRoot和DecorView
(1)`ViewRoot`对应`ViewRootImpl`类，它是连接`WindowManager`和`DecorView`的纽带，View的三大流程均通过ViewRoot来完成。
(2)`ActivityThread`中，Activity创建完成后，会将DecorView添加到Window中，同时创建ViewRootImpl对象，并建立两者的关联。
(3)View的绘制流程从ViewRoot的`performTraversals`方法开始，经过`measure`、`layout`和`draw`三大流程。
(4)`performMeasure`方法中会调用`measure`方法，在`measure`方法中又会调用`onMeasure`方法，在onMeasure方法中会对所有的子元素进行measure过程，这个时候measure流程就从父容器传递到子元素了，这样就完成了一次measure过程，layout和draw的过程类似。 (书中175页画出详细的图示)
(5)measure过程决定了view的宽高，在几乎所有的情况下这个宽高都等同于view最终的宽高。layout过程决定了view的四个顶点的坐标和view实际的宽高，通过`getWidth`和`getHeight`方法可以得到最终的宽高。draw过程决定了view的显示。
(6)DecorView其实是一个FrameLayout，其中包含了一个竖直方向的LinearLayout，上面是标题栏，下面是内容区域(id为`android.R.id.content`)。

4.2 理解MeasureSpec
(1)`MeasureSpec`和`LayoutParams`的对应关系
在view测量的时候，系统会将LayoutParams在父容器的约束下转换成对应的MeasureSpec，然后再根据这个MeasureSpec来确定View测量后的宽高。
**MeasureSpec不是唯一由LayoutParams决定的，LayoutParams需要和父容器一起才能决定view的MeasureSpec，从而进一步确定view的宽高。对于DecorView，它的MeasureSpec由窗口的尺寸和其自身的LayoutParams来决定；对于普通view，它的MeasureSpec由父容器的MeasureSpec和自身的LayoutParams来共同决定。**
(2)普通view的MeasureSpec的创建规则 (书中182页列出详细的表格)
当view采用固定宽高时，不管父容器的MeasureSpec是什么，view的MeasureSpec都是精确模式，并且大小是LayoutParams中的大小。
当view的宽高是`match_parent`时，如果父容器的模式是精确模式，那么view也是精确模式，并且大小是父容器的剩余空间；如果父容器是最大模式，那么view也是最大模式，并且大小是不会超过父容器的剩余空间。
当view的宽高是`wrap_content`时，不管父容器的模式是精确模式还是最大模式，view的模式总是最大模式，并且大小不超过父容器的剩余空间。

4.3 view的工作流程
(1)view的measure过程和Activity的生命周期方法不是同步执行的，因此无法保证Activity执行了`onCreate`、`onStart`、`onResume`时某个view已经测量完毕了。如果view还没有测量完毕，那么获得的宽高就都是0。下面是四种解决该问题的方法：
1.`Activity/View # onWindowFocusChanged`方法
`onWindowFocusChanged`方法表示view已经初始化完毕了，宽高已经准备好了，这个时候去获取宽高是没问题的。**这个方法会被调用多次，当Activity继续执行或者暂停执行的时候，这个方法都会被调用。**
2.`view.post(runnable)`
通过post将一个runnable投递到消息队列的尾部，然后等待Looper调用此runnable的时候，view也已经初始化好了。
3.`ViewTreeObserver`
使用`ViewTreeObserver`的众多回调方法可以完成这个功能，比如使用`onGlobalLayoutListener`接口，当view树的状态发生改变或者view树内部的view的可见性发生改变时，`onGlobalLayout`方法将被回调。**伴随着view树的状态改变，这个方法也会被多次调用。**
4.`view.measure(int widthMeasureSpec, int heightMeasureSpec)`
通过手动对view进行measure来得到view的宽高，这个要根据view的LayoutParams来处理：
`match_parent`：无法measure出具体的宽高；
`wrap_content`：如下measure，设置最大值
```
int widthMeasureSpec = MeasureSpec.makeMeasureSpec((1 << 30) - 1, MeasureSpec.AT_MOST);
int heightMeasureSpec = MeasureSpec.makeMeasureSpec((1 << 30) - 1, MeasureSpec.AT_MOST);
view.measure(widthMeasureSpec, heightMeasureSpec);
```
精确值：例如100px
```
int widthMeasureSpec = MeasureSpec.makeMeasureSpec(100, MeasureSpec.EXACTLY);
int heightMeasureSpec = MeasureSpec.makeMeasureSpec(100, MeasureSpec.EXACTLY);
view.measure(widthMeasureSpec, heightMeasureSpec);
```
(2)**在view的默认实现中，view的测量宽高和最终宽高是相等的，只不过测量宽高形成于measure过程，而最终宽高形成于layout过程。**
(3)draw过程大概有下面几步：
1.绘制背景：`background.draw(canvas)`；
2.绘制自己：`onDraw()`；
3.绘制children：`dispatchDraw`；
4.绘制装饰：`onDrawScrollBars`。

4.4 自定义view
(1)继承view重写onDraw方法需要自己支持`wrap_content`，并且`padding`也要自己处理。继承特定的View例如TextView不需要考虑。
(2)尽量不要在View中使用Handler，因为view内部本身已经提供了`post`系列的方法，完全可以替代Handler的作用。
(3)view中如果有线程或者动画，需要在`onDetachedFromWindow`方法中及时停止。
(4)处理好view的滑动冲突情况。

接下来是原书中的自定义view的示例，推荐阅读[源码](https://github.com/singwhatiwanna/android-art-res/blob/master/Chapter_4/src/com/ryg/chapter_4/ui/CircleView.java)。

OK，本章结束，谢谢阅读。


