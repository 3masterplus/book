---
layout: post
title: Art of Android Development Reading Notes 7
categories: android
date: 2015-11-30 21:50:54
---
《Android开发艺术探索》读书笔记 (7) 第7章 Android动画深入分析 <!--more-->

本节和《Android群英传》中的**第七章Android动画机制与使用技巧**有关系，[建议先阅读该章的总结](https://hujiaweibujidao.github.io/blog/2015/11/27/Android-Heros-Reading-Notes-3/)

### 第7章 Android动画深入分析
#### 7.1 View动画
(1)android动画分为view动画、帧动画和属性动画，属性动画是API 11(Android 3.0)的新特性，帧动画一般也认为是view动画。
(2)`AnimationSet`的属性`android:shareInterpolator`表示集合中的动画是否共享同一个插值器，如果集合不指定插值器，那么子动画需要单独指定所需的插值器或者使用默认值。
(3)自定义动画需要继承`Animation`抽象类，并重新它的`initialize`和`applyTransformation`方法，在initialize方法中做一些初始化工作，在applyTransformation方法中进行相应的矩阵变换，很多时候需要采用`Camera`类来简化矩阵变换的过程。
(4)帧动画使用比较简单，但是容易引起OOM，所以在使用的时候应尽量避免使用过多尺寸较大的图片。

#### 7.2 view动画的特殊使用场景
(1)布局动画(`LayoutAnimation`)属性分析
```
<layoutAnimation
    xmlns:android="http://schemas.android.com/apk/res/android"
    android:delay="0.5"
    android:animationOrder="reverse"
    android:animation="@anim/anim_item"/>
```
`android:delay`：表示子元素开始动画的时间延迟，比如子元素入场动画的时间周期是300ms，那么0.5表示每个子元素都需要延迟150ms才能播放入场动画。

给ViewGroup指定LayoutAnimation的两种方式
```
//xml
android:layoutAnimation="xxx"
//java
Animation animation = AnimationUtils.loadAnimation(this, R.anim.anim_item);
LayoutAnimationController controller = new LayoutAnimationController(animation);
controller.setDelay(0.5f);
controller.setOrder(LayoutAnimationController.ORDER_NORMAL);
listView.setLayoutAnimation(controller);
```

(2)Activity切换效果
在startActivity方法后或者finish方法之后调用`overridePendingTransition(int inAnim, int outAnim)`方法设置进入或者退出的动画效果。
还有其他方式可以给Activity添加切换动画效果，但是往往有兼容性限制，参见[《Android群英传》第七章Android动画机制与使用技巧](https://hujiaweibujidao.github.io/blog/2015/11/27/Android-Heros-Reading-Notes-3/)。

#### 7.3 属性动画
(1)属性动画可以对任意对象的属性进行动画而不仅仅是view，动画默认的时间间隔是`300ms`，默认帧率是`10ms/帧`。
(2)属性动画几乎是无所不能，但是它是从API 11才有的，所以存在兼容性问题，可以考虑使用开源动画库[nineoldandroids](http://nineoldandroids.com)。它的功能和系统原生的`android.animations.*`中的类的功能完全一致，使用方法也是完全一样，只要我们用nineoldandroids编写动画，那么就能运行在所有的android系统上。
(3)属性`android:repeatMode`表示动画的重复模式，`repeat`表示连续重复播放，`reverse`表示逆向重复播放，也就是第一次播放完后第二次倒着播放动画，第三次还是重头开始播放动画，第四次再倒着播放，以此类推。
(4)插值器和估值器：属性动画实现非匀速动画的重要手段
时间插值器(`TimeInterpolator`)的作用是根据时间流逝的百分比计算出当前属性值改变的百分比，系统内置的插值器有线性插值器(`LinearInterpolator`)、加速减速插值器(`AccelerateDecelerateInterpolator`)和减速插值器(`DecelerateInterpolator`)。
类型估值器(`TypeEvaluator`)的作用是根据当前属性改变的百分比计算出改变后的属性值，系统内置的估值器有`IntEvaluator`、`FloatEvaluator`和`ArgbEvaluator`。
(5)动画监听器
`AnimatorListener`：监听动画的开始、结束、取消以及重复播放；
`AnimatorUpdateListener`：监听动画的整个过程，动画每播放一帧的时候`onAnimationUpdate`方法就会被回调一次。
(6)对任意属性做动画的方法：封装原始对象或者`ValueAnimator`
(7)属性动画的工作原理：属性动画需要运行在有Looper的线程中，反射调用get/set方法

#### 7.4 使用动画的注意事项
(1)OOM：尽量避免使用帧动画，使用的话应尽量避免使用过多尺寸较大的图片；
(2)内存泄露：属性动画中的无限循环动画需要在Activity退出的时候及时停止，否则将导致Activity无法释放而造成内存泄露。view动画不存在这个问题；
(3)兼容性问题：某些动画在3.0以下系统上有兼容性问题；
(4)view动画的问题：view动画是对view的影像做动画，并不是真正的改变view的状态，因此有时候动画完成之后view无法隐藏，即`setVisibility(View.GONE)`失效了，此时需要调用`view.clearAnimation()`清除view动画才行。
(5)不要使用px；
(6)动画元素的交互：**在android3.0以前的系统上，view动画和属性动画，新位置均无法触发点击事件，同时，老位置仍然可以触发单击事件。从3.0开始，属性动画的单击事件触发位置为移动后的位置，view动画仍然在原位置**；
(7)硬件加速：使用动画的过程中，建议开启硬件加速，这样会提高动画的流畅性。

**其他学习资料**
0.[代码家的重要的开源项目AndroidViewAnimation](https://github.com/daimajia/AndroidViewAnimations)
1.[Android样式的开发:View Animation篇](http://keeganlee.me/post/android/20151003)
2.[Android样式的开发:Property Animation篇](http://keeganlee.me/post/android/20151026)

OK，本章结束，谢谢阅读。


