---
title: Usefull twig function
layout: doc2
navigations: cookbook
---

<div class="pull-right">
    {{ render_document_toc() }}
</div>

Inside your templates (`.html.twig` files) and / or your documents (`.md` files)
you can use several twig globals / functions.

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

* `relativeRoot`: Points to the (web) root directory. Can be something like `../..`.

* `currentPath`: Points to the current path from the `relativeRoot`. Can be
something like `2012/01/01/hello.html`.

* `document`: Represents the current document. This is an instance of
`Carew/Document` class.

Some examples:

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

## Functions

**Note:** You can tweak generated content for almost all functions by overriding the
`blocks.html.twig` template. See the {{ link('pages/cookbook/themes.md', 'theme
chapter') }} for more information.

### path

`path`: Render a path to a document

    {% verbatim -%}
    {{ path('pages/index.md') }}
    {%- endverbatim %}

Render something like: `index.html`

Arguments:

1. `filePath`: the full path to the document. i.e.: where the file is
located on your disk.

If you want to change the generated content, override the `document_path`
block.

### link

`link`: Render a link to a document

    {% verbatim -%}
    {{ link('pages/index.md', 'a link') }}
    {%- endverbatim %}

Render something like: `<a href="index.html">a link</a>`

Arguments:

1. `filePath`: the full path to the document. i.e.: where the file is
located on your disk.

1. `title` (optional): The link title. Default value is the document title.

1. `attrs` (optional): An array of HTML attributes.

If you want to change the generated content, override the `document_link`
block.

### render_document_toc

`render_document_toc`: Render the Table Of Content of a document

    {% verbatim -%}
    {{ render_document_toc() }}
    {%- endverbatim %}

Render something like:

    <ul>
        <li><a href="#section-1">Section 1</a></li>
        <li><a href="#section-2">Section 2</a></li>
        <li><a href="#section-3">Section 3</a></li>
    </ul>

Arguments:

1. `toc` (optional): A document instance or a TOC. The default value is
the current document.

If you want to change the generated content, override the `document_toc`
block.

### render_document_*

`render_document_*`: This is a wild-card function. It will take the `*` and
render the  `document_*` block.

Arguments:

1. `document` (optional): A document instance. The default value is the current
document.

If you want to change the generated content, override the `document_*`
block.

**Waning**: The block `document_*` must be defined.

### render_document

`render_document`: Renders a document.

    {% verbatim -%}
    {{ render_document(carew.document) }}
    {%- endverbatim %}

Generates something like:

    <h2>Document title</h2>

    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Reprehenderit,
    harum nam facilis modi eos est ipsa nostrum recusandae assumenda
    molestiae nemo omnis animi? Quia maiores fuga quam necessitatibus
    quaerat cum.

Arguments:

1. `document`: A document instance.

This function will call one of the following block depending of the document
type: `post`, `page`, `api`, `unknown`.

If you want to change the generated content, override one of theses blocks.

### render_documents

`render_documents`: Renders a collection of document.

    {% verbatim -%}
    {{ render_document(carew.document) }}
    {%- endverbatim %}

Generates something like:

    <ul>
        <li><a href="/post1.html">Post 1</a></li>
        <li><a href="/post2.html">Post 2</a></li>
        <li><a href="/post3.html">Post 3</a></li>
    </ul>

Arguments:

1. `documents`: A collection (array) of document instances.

If you want to change the generated content, override the `documents` block.

### paginate

`paginate`: Adds pagination support to a collection of documents.

**warning:** This function can only be applied in a `file.md`, not in a template.

**warning:** This function returns an array. It should be used with the
`render_documents` function.

    {% verbatim -%}
    {{ render_documents(paginate(carew.posts)) }}
    {%- endverbatim %}

This function will add a pagination on the collection. So it will create as
many page as needed.


Arguments:

1. `documents`: A collection (array) of document instances.

1. `maxPerPage`: Number of items per page.

If you want to change the generated content, override the `pagination`
block.
