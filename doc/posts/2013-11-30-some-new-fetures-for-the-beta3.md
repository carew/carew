---
layout: post
title:  Some new fetures for the beta3
published: false
---

I'm very happy to release the third BETA of Carew2.

Changelog:

* Added a `published` flag to all documents;
* {# {{ path('pages/cookbook/configuration#blog-post-url-format', 'Added support for custom blog post url format') }}; #}
* Added a `TERMINATE` event. [fixed #11](https://github.com/carew/carew/issues/11);
* Added feed for tag page (eg: `blog.com/tags/tag-name/feed/atom.xml`);
* Added more tests;
* Fixed bug with url and tag pages;
