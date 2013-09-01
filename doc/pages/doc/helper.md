---
title: Helper
layout: doc2
navigations: sub
---

Inside your templates and / or your `.md` file you can use several twig
functions / globals.

## Globals

You have access to the `carew` global. This is an instance of
`Carew/Twig/Globals` class. This one contains few things:

* `site`: Contains all value under the `site` section of the `config.yml`

* `documents`: Contains the list of all documents. Keys are the full path of a
document. This is very usefull to create a link to another document.

* `posts`: Contains the list of all posts. Keys are the full path of a document.
This is very usefull to create a list of last blog post.

* `pages`: Contains the list of all pages. Keys are the full path of a document.

* `tags`: Contains the list of all tags. Keys are the tag name. Values are
collections of document.

* `navigations`: Contains the list of all tags. Keys are the navigation name.
Values are collections of document.

* `relativeRoot`: Point the (web) root directory. Can be something like `../..`.

* `currentPath`: Point the current path from the `relativeRoot`. Can be something
like `2012/01/01/hello.html`.

* `document`: Represent the current document. This is an instance of
`Carew/Document` class.

Some samples:

    {% verbatim -%}
    {{ carew.site.decription }}

    {# read the next chapter, there is a much better way to do that #}
    <ul>
        {% for post in carew.posts %}
            <li>
                <a href="{{ relativeRoot }}/{{ document.path }}">
                    {{ document.title|title }}
                </a>
            </li>
        {% endfor %}
    </ul>

    {# read the next chapter, there is a much better way to do that #}
    <img src="{{ carew.relativeRoot }}/image/logo.png" alt="Logo">

    {%- endverbatim %}
