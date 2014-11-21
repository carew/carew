---
title: Carew
subtitle: The tiny website generator
layout: doc2
---

What is it?
-----------

Carew is another static site / blog generator. Write some blog posts or pages in
[markdown](http://daringfireball.net/projects/markdown/), Carew will render them
in html.

Features
--------

* Simple but extensible
* Auto syntax highlighting
* Auto generated navigation
* Auto generated pagination
* One theme base on Bootstrap
* Shareable/Linkable SEO Friendly URLs
* No need for php, ruby, python on the production server.
* Git friendly

Installation
------------

The best way to start with carew is to use the [carew
boilerplate](https://github.com/carew/boilerplate): You will need
[composer](http://getcomposer.org).

    $ php composer.phar create-project carew/boilerplate my_website
    $ cd my_website
    $ bin/carew build

That's all, you can browse the `web/` directory. If you are using php 5.4+, you
can start the build-in webserver:

    $ php bin/carew serve

Demo
----

Do you want to see it in action? This doc uses Carew ;) Have a look to the
[codebase](https://github.com/carew/carew/tree/master/doc).

By the way, if you use Carew, let me know.

Why another one?
----------------

I used to use [jekyll](https://github.com/mojombo/jekyll), but I was very
unhappy with the templating engine. And then, I discovered
[balrog](https://github.com/igorw/balrog/tree/8ed377d4eb1759926d8cfceb1796ed4234dceaef).
It was very cool but [igor](https://github.com/igorw/balrog/) took
another direction. So I forked it, and carew was born.

### Why this name?

Just like every other static site / blog generator ([jekyll](https://github.com/mojombo/jekyll),
[hyde](https://github.com/hyde/hyde), [poole](https://github.com/obensonne/poole),
[lanyon](https://github.com/spjwebster/lanyon)), its name comes from the
*[Strange Case of Dr Jekyll and Mr Hyde](http://en.wikipedia.org/wiki/Strange_Case_of_Dr_Jekyll_and_Mr_Hyde)*
story.
