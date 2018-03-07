---
layout: post
title: "Python Algorithms - C1 Introduction"
date: 2014-07-01 10:10
categories: algorithm
---
Python算法设计篇(1) Chapter 1: Introduction <!--more-->

> 1. Write down the problem.
2. Think real hard.
3. Write down the solution.    
     —— “The Feynman Algorithm” as described by Murray Gell-Mann

本节主要是对原书中的内容做些简单介绍，说明算法的重要性以及各章节的内容概要。

#### 1.关于这本书的目的

算法导论是一本经典的大而全的算法书籍，而本书Python Algorithms不是来取代而是来补充算法导论的，因为算法导论提供的是简易的伪代码和详细的证明，而本书主要从作者的教学过程中从更高地层次来讲解算法，并使用Python代码来实现。

**[实际阅读之后，我个人感觉这本书虽然貌似名声不大，但是绝对可以和算法导论平分秋色]**

#### 2.这本书关于什么？

算法分析，算法设计的基本原则，如何使用Python实现基本的数据结构和算法 (以下内容不难理解就不做翻译了，原文原滋原味更加萌萌哒 ~\(^o^)/~)

What the book is about:    
• Algorithm analysis, with a focus on asymptotic running time   
• Basic principles of algorithm design    
• How to represent well-known data structures in Python    
• How to implement well-known algorithms in Python   

What the book covers only briefly or partially:    
• Algorithms that are directly available in Python, either as part of the language or via the standard library    
• Thorough and deep formalism (although the book has its share of proofs and proof-like explanations)    

#### 3.为什么我们需要学习算法呢？

学习了算法之后可以帮助我们更加高效地解决问题！

下面是一个简单的线性时间和平方时间的对比例子，后者的运行速度远远慢于后者，为什么呢？这与Python中内置的list的实现机制有关，在前面的数据结构篇中介绍过了，list是类似数组一样的动态表，而不是标准的数组形式，所以对于append操作是常数时间，而对于insert操作是线性时间的！感兴趣的话移步阅读[Python数据结构篇3-数据结构](https://hujiaweibujidao.github.io/blog/2014/05/08/python-algorithms-datastructures/)

```python
from time import *
t0=time()
count=10**5
nums=[]
for i in range(count):
    nums.append(i)

nums.reverse()
t1 = time() - t0
print t1 #0.0240848064423
t0=time()
nums=[]
for i in range(count):
    nums.insert(0, i)

t2 = time() - t0
print t2 #3.68582415581
```

#### 4.这本书完整的章节内容

除去平摊分析外，内容差不多和我本学期的算法课的内容一样

Chapter 1: Introduction. You’ve already gotten through most of this. It gives an overview of the book.

Chapter 2: The Basics. This covers the basic concepts and terminology, as well as some fundamental math. Among other things, you learn how to be sloppier with your formulas than ever before, and still get the right results, with asymptotic notation.

Chapter 3: Counting 101. More math—but it’s really fun math, I promise! There’s some basic combinatorics for analyzing the running time of algorithms, as well as a gentle introduction to recursion and recurrence relations.

Chapter 4: Induction and Recursion ... and Reduction. The three terms in the title are crucial, and they are closely related. Here we work with induction and recursion, which are virtually mirror images of each other, both for designing new algorithms and for proving correctness. We also have a somewhat briefer look at the idea of reduction, which runs as a common thread through almost all algorithmic work.

Chapter 5: Traversal: A Skeleton Key to Algorithmics. Traversal can be understood using the ideas of induction and recursion, but it is in many ways a more concrete and specific technique. Several of the algorithms in this book are simply augmented traversals, so mastering traversal will give you a real jump start.

Chapter 6: Divide, Combine, and Conquer. When problems can be decomposed into independent subproblems, you can recursively solve these subproblems and usually get efficient, correct algorithms as a result. This principle has several applications, not all of which are entirely obvious, and it is a mental tool well worth acquiring.

Chapter 7: Greed is Good? Prove It! Greedy algorithms are usually easy to construct. One can even formulate a general scheme that most, if not all, greedy algorithms follow, yielding a plug-and-play solution. Not only are they easy to construct, but they are usually very efficient. The problem is, it can be hard to show that they are correct (and often they aren’t). This chapter deals with some well-known examples and some more general methods for constructing correctness proofs.

Chapter 8: Tangled Dependencies and Memoization. This chapter is about the design method (or, historically, the problem) called, somewhat confusingly, dynamic programming. It is an advanced technique that can be hard to master but that also yields some of the most enduring insights and elegant solutions in the field.


----------

#### 问题1-2：(比较两个字符串是否满足回文构词法)

Find a way of checking whether two strings are anagrams of each other (such as "debit card" and "bad credit"). How well do you think your solution scales? Can you think of a naïve solution that will scale very poorly?

A simple and quite scalable solution would be to sort the characters in each string and compare the results. (In theory, counting the character frequencies, possibly using collections.Counter, would scale even better.) A really poor solution would be to compare all possible orderings of one string with the other. I can’t overstate how poor this solution is; in fact, algorithms don’t get much worse than this. Feel free to code it up, and see how large anagrams you can check. I bet you won’t get far.

返回[Python数据结构与算法设计篇目录](https://hujiaweibujidao.github.io/python/)


