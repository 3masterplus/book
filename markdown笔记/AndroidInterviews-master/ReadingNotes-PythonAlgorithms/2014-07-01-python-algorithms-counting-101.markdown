---
layout: post
title: "Python Algorithms - C3 Counting 101"
date: 2014-07-01 10:30
categories: algorithm
---
Python算法设计篇(3) Chapter 3: Counting 101 <!--more-->

> The greatest shortcoming of the human race is our inability to understand the exponential function.  
  —— Dr. Albert A. Bartlett, World Population Balance Board of Advisors


原书主要介绍了一些基础数学，例如排列组合以及递归循环等，但是本节只重点介绍计算算法的运行时间的三种方法

因为本节内容都很简单，所以我只是浏览了一下，重要的只有计算算法的运行时间的三种方法：1.代换法； 2.递归树法； 3.主定理法。

1.代换法

代换法一般是先猜测解的形式，然后用数学归纳法来证明它

下面是算法导论中的一个求解例子

![image](https://hujiaweibujidao.github.io/images/algos/sub1.png)

有意思的是，还有一类问题可以通过变量替换变成容易求解的形式

![image](https://hujiaweibujidao.github.io/images/algos/sub2.png)

下面是常用的一些递归式以及它们对应的结果还有实际算法实例

![image](https://hujiaweibujidao.github.io/images/algos/sub3.png)

2.递归树法

这种方法就是通过画递归树，然后对每层进行求和，最后将每层的结果相加得到对总的算法运行时间的估计

![image](https://hujiaweibujidao.github.io/images/algos/rectree.png)

3.主定理法

这种方法大家最喜欢，给出了一种就像是公式一样的结论，虽然它没有覆盖所有的情况，而且证明非常复杂，但是很多情况下都是可以直接使用的，还有，需要注意主定理的不同情况下的条件，尤其是多项式大于和多项式小于！

![image](https://hujiaweibujidao.github.io/images/algos/master.png)

不喜欢本节的可以跳过，不留练习了这次，嘿嘿，想练习的话刷算法导论的题目吧

返回[Python数据结构与算法设计篇目录](https://hujiaweibujidao.github.io/python/)


