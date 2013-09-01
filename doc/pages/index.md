---
title: Carew
subtitle: The tiny website generator
layout: doc2
---

What is it?
-----------

**Carew** is another static site / blog generator.
Write some blog post in [markdown](http://daringfireball.net/projects/markdown/),
carew will render them in html.

Installation
------------

The best way to start with carew, it's with the [carew boilerplate](https://github.com/carew/boilerplate):
You will need [composer](http://getcomposer.org).

    $ php composer.phar create-project carew/boilerplate my_website
    $ cd my_website
    $ vendor/bin/carew carew:build

That's all.

Demo
----

Do you want to see it in action? This doc uses carew ;)
Have a look to the [codebase](https://github.com/carew/carew/tree/master/doc).

By the way, if you use carew, let me know.

Why another one?
----------------

I used to used [jekyll](https://github.com/mojombo/jekyll), but I was very
unhappy with the templating engine. And then, I discovered
[balrog](https://github.com/igorw/balrog/tree/8ed377d4eb1759926d8cfceb1796ed4234dceaef).
It was very cool but [igor](https://github.com/igorw/balrog/) took
another direction. So I fork it, and carew was born.

### Why this name?

Like to all other tatic site / blog generator ([jekyll](https://github.com/mojombo/jekyll),
[hyde](https://github.com/hyde/hyde), [pool](https://github.com/obensonne/poole),
[lanyon](https://github.com/spjwebster/lanyon)), its name come from the
*[Strange Case of Dr Jekyll and Mr Hyde](http://en.wikipedia.org/wiki/Strange_Case_of_Dr_Jekyll_and_Mr_Hyde)*
story.
