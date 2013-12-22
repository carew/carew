---
title: Documentation
subtitle: For the end user
layout: doc2
navigations: main
---

<div class="pull-right">
    {{ render_document_toc(carew.document) }}
</div>

## Quick start

With carew, you can write simple pages and / or blog posts. Create a new file in
`pages/` folder to create a new page and in `posts/` to create a new blog post.

Each one must begin with a YAML front matter. See [Front matter chapter](#front-matter)
to see all options. Here is a sample page (`pages/index.md`):

    ---
    title: How to create a new page?
    ---

    So, how to create a new page?

    # Step 1

    1. step 1.1
    1. step 1.2
    1. step 1.3

Then run `./carew build` and look at `index.html` in the `web/` directory.

### Write a blog post

The file must be located in the `posts/` directory and must have a name with
the following format: `YYYY-mm-dd-slug.md` (for e.g.
`posts/2012-09-20-like-a-hacker.md`). Here is a sample blog post:

    ---
    title: Blogging Like a Hacker
    layout: post
    ---

    # Blogging Like a Hacker

    * I’m bloggin yo!
    * ORLY?
    * YARLY!

Then run `./carew build` and look at `2012/09/20/like-a-hacker.html` in the
`web/` dir.

### Write a page

The file must be located in the `pages/` directory (or in one of its
subdirectory).
Here is a sample page (`pages/doc/quick-start.md`):

    ---
    title: Quick Start
    layout: page
    ---

    Lorem ipsum dolor sit amet, consectetur adipisicing elit. Est, debitis quasi
    laborum veniam voluptates praesentium corporis! Soluta, facere, voluptatum
    veritatis odio pariatur voluptatem consectetur numquam veniam adipisci
    placeat blanditiis magnam!

Then run `./carew build` and look at `doc/quick-start.html` in the `web/` dir.

### Front Matter

In order to be converted from markdown to HTML, a file must start with a
"front matter" which describes the document's meta-information.
Files which don't start with a front matter are simply copied to the `web/`
directory as is.

Here's a sample of front matter:

    ---
    layout: doc
    title: React
    desciption: how to use react-php
    permalink: react.html
    tag: [php, react]
    navigation: [main]
    author: John Do
    ---

* **layout:** the name of the layout (Twig) to use from the `layouts/` directory in
order to render the page. If not mentionned, defaults to `default`, which means
`layouts/default.html.twig`.
{{ link('pages/cookbook/themes.md', 'See theme documentation for more information')}}

* **title:** will be used in the index page and by `<title></title>` and
`<h1></h1>` HTML tags

* **permalink:** forces the target path of the current document

* **tags:** adds the current document to the given tags, to make it easier to
find

        ---
        tags: [nginx, varnish]
        # or
        tags:
            - nginx
            - varnish
        ---

* **navigations:** adds the current document to a menu:

        ---
        navigation: [main]
        ---

All others keys (for example `description`) will be used in a
`<meta name="" content="" />` HTML tag.

### Directory structure

Now you can use the following directories:

* **assets:** all of these files will be copied to the web directory. You can
add JavaScript, CSS and images in here

* **layouts:** the layouts are used to render the pages. They have a
`.html.twig` suffix. You can create base templates and have more specific ones
that extend them using the `extends` tag. The layouts are rendered with
[Twig](http://twig.sensiolabs.com). {{ link('pages/cookbook/themes.md', 'See theme
documentation for more information') }}

* **pages:** markdown files representing pages. Each one must begin with a YAML
front matter. Here is a sample page:

    ---
    title: A sample page
    navigations: main
    ---

    This is a page.

* **posts:** markdown files representing blog posts. Must be named with the
following format: `YYYY-mm-dd-slug.md` (for e.g. `2012-09-20-like-a-hacker.md`)

* **api**: HTML or markdown files. Each one will be rendered in
`/api/filename.html`

* **config.yml:** configuration file. The `site` section defines variables which
will be used in the Twig templates. Here is a sample:

        site:
          author: Grégoire Pineau
          title:  Foo
          description: Bar

    And in the template:

        <html>
          <head>
            <title>{% verbatim %}{{ site.title }}{% endverbatim %}</title>

{{ link('pages/cookbook/configuration.md', 'See configuration documention for more
information') }}.

## Usage

### Build the site

In order to build the site, you can use the `build` command:

    $ bin/carew build

This will populate the `web` directory with a set of files that can be deployed
onto any static web server.

You can change input / ouput directory. Run for more information:

    $ bin/carew help build

### Create a new blog post

Just run:

    $ bin/carew carew:generate:post [--date="..."] "title"

## What next?

Why not read the whole {{ link('pages/cookbook.md') }}?

{{ render_documents(carew.navigations.cookbook) }}
