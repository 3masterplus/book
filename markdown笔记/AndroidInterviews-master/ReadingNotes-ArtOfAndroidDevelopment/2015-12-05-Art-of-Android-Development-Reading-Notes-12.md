---
layout: post
title: Art of Android Development Reading Notes 12
categories: android
date: 2015-11-30 22:50:54
---
《Android开发艺术探索》读书笔记 (12) 第12章 Bitmap的加载和Cache <!--more-->

### 第12章 Bitmap的加载和Cache
#### 12.1 Bitmap的高速加载
(1)**Bitmap是如何加载的？**
`BitmapFactory`类提供了四类方法：`decodeFile`、`decodeResource`、`decodeStream`和`decodeByteArray`从不同来源加载出一个Bitmap对象，最终的实现是在底层实现的。
**如何高效加载Bitmap？**
采用`BitmapFactory.Options`按照一定的采样率来加载所需尺寸的图片，因为imageview所需的图片大小往往小于图片的原始尺寸。
(2)BitmapFactory.Options的`inSampleSize`参数，即采样率
官方文档指出采样率的取值应该是2的指数，例如k，那么采样后的图片宽高均为原图片大小的 1/k。
**如何获取采样率？**
下面是常用的获取采样率的代码片段：
```
public Bitmap decodeSampledBitmapFromResource(Resources res, int resId, int reqWidth, int reqHeight) {
    // First decode with inJustDecodeBounds=true to check dimensions
    final BitmapFactory.Options options = new BitmapFactory.Options();
    options.inJustDecodeBounds = true;
    BitmapFactory.decodeResource(res, resId, options);

    // Calculate inSampleSize
    options.inSampleSize = calculateInSampleSize(options, reqWidth, reqHeight);

    // Decode bitmap with inSampleSize set
    options.inJustDecodeBounds = false;
    return BitmapFactory.decodeResource(res, resId, options);
}

public int calculateInSampleSize(BitmapFactory.Options options, int reqWidth, int reqHeight) {
    if (reqWidth == 0 || reqHeight == 0) {
        return 1;
    }

    // Raw height and width of image
    final int height = options.outHeight;
    final int width = options.outWidth;
    Log.d(TAG, "origin, w= " + width + " h=" + height);
    int inSampleSize = 1;

    if (height > reqHeight || width > reqWidth) {
        final int halfHeight = height / 2;
        final int halfWidth = width / 2;

        // Calculate the largest inSampleSize value that is a power of 2 and
        // keeps both height and width larger than the requested height and width.
        while ((halfHeight / inSampleSize) >= reqHeight && (halfWidth / inSampleSize) >= reqWidth) {
            inSampleSize *= 2;
        }
    }

    Log.d(TAG, "sampleSize:" + inSampleSize);
    return inSampleSize;
}
```

将`inJustDecodeBounds`设置为true的时候，BitmapFactory只会解析图片的原始宽高信息，并不会真正的加载图片，所以这个操作是轻量级的。**需要注意的是，这个时候BitmapFactory获取的图片宽高信息和图片的位置以及程序运行的设备有关，这都会导致BitmapFactory获取到不同的结果。**

#### 12.2 Android中的缓存策略
(1)最常用的缓存算法是LRU，核心是当缓存满时，会优先淘汰那些近期最少使用的缓存对象，系统中采用LRU算法的缓存有两种：`LruCache`(内存缓存)和`DiskLruCache`(磁盘缓存)。
(2)LruCache是Android 3.1才有的，通过support-v4兼容包可以兼容到早期的Android版本。LruCache类是一个线程安全的泛型类，它内部采用一个`LinkedHashMap`以强引用的方式存储外界的缓存对象，其提供了get和put方法来完成缓存的获取和添加操作，当缓存满时，LruCache会移除较早使用的缓存对象，然后再添加新的缓存对象。
(3)DiskLruCache磁盘缓存，它不属于Android sdk的一部分，[它的源码可以在这里下载](https://android.googlesource.com/platform/libcore/+/android-4.1.1_r1/luni/src/main/java/libcore/io/DiskLruCache.java)
DiskLruCache的创建、缓存查找和缓存添加操作
(4)ImageLoader的实现 [具体内容看源码](https://github.com/singwhatiwanna/android-art-res/blob/master/Chapter_12/src/com/ryg/chapter_12/loader/ImageLoader.java)
功能：图片的同步/异步加载，图片压缩，内存缓存，磁盘缓存，网络拉取

#### 12.3 ImageLoader的使用
避免发生列表item错位的解决方法：给显示图片的imageview添加`tag`属性，值为要加载的图片的目标url，显示的时候判断一下url是否匹配。
**优化列表的卡顿现象**
(1)不要在getView中执行耗时操作，不要在getView中直接加载图片，否则肯定会导致卡顿；
(2)控制异步任务的执行频率：在列表滑动的时候停止加载图片，等列表停下来以后再加载图片；
(3)使用硬件加速来解决莫名的卡顿问题，给Activity添加配置`android:hardwareAccelerated="true"`。

本章的精华就是作者写的[ImageLoader类](https://github.com/singwhatiwanna/android-art-res/blob/master/Chapter_12/src/com/ryg/chapter_12/loader/ImageLoader.java)，建议阅读源码感受下。

OK，本章结束，谢谢阅读。
