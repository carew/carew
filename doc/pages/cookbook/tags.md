---
title: How tags works?
layout: doc2
navigations: cookbook
---

You can tag all of your document with the following syntax:

    ---
    title: How to create a new page?
    tags:
        - documentation
        - page
    ---

    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Iste, eveniet
    consectetur aspernatur dolor voluptatem laboriosam impedit officiis
    consequatur dignissimos nesciunt temporibus aliquam earum porro sapiente
    blanditiis dolorem non. Minus, quae!

When a document is taggued, it going to be added to the global
`carew.tags.<tag name>`.

More over, few pages will be generated:

* A first one which list all tags.
* A page per tag which list all document.

{% verbatim %}
You can create a link to the first page with this syntax `{{ path('tags') }}`
and to a specific tag: `{{ path('tags/<tag name>') }}`.
{% endverbatim %}

For the moment, tags pages a not customizable. Open [an
issue](https://github.com/carew/carew/issues/new) if you want to be able to
customize them.
