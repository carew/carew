---
title: How to host a website build with carew?
layout: doc2
navigations: cookbook
---

Behind a webserver
------------------

If you have a webserver, just copy the content of the `web` folder to the
document root of your web server.

Github
------

How to host carew on github ?

[Github](https://github.com) can host [static pages](http://pages.github.com/).
It's very easy thanks to carew.

Start by creating a new project with [composer](http://getcomposer.org):

    $ mkdir my_site
    $ cd my_site
    $ git init
    $ git co -b gh-pages
    $ php composer.phar create-project carew/boilerplate _carew

To make it more simple to build your website, you can setup a `build.sh` script
in `_carew` folder:

    #!/bin/bash

    BASE=`dirname $0`
    $BASE/vendor/bin/carew build --base-dir=$BASE --web-dir=$BASE/..

Then you can build the website with `_carew/build.sh` command.
Now, you can commit and push everything to your github repository:

    $ git add .
    $ git commit -m "Inital commit"
    $ git remote add origin git@github.com:YOUR_NAME/YOUR_REPO.git
    $ git push origin gh-pages -u

Now, you can browse `http://YOUR_NAME.github.com/YOU_REPO`
