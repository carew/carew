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

Must be in the format of `YYYY-mm-dd-slug.md` and put in `posts` directory, e.g.
`posts/2012-09-20-like-a-hacker.md`. Here is a sample blog post:

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

Must be put in the `pages/` folder or any subdirectories. Here is a sample page
(`pages/doc/quick-start.md`):

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

All files must start with the front matter. It describes meta-information about
the current document. If the document doesn't contains a front matter, the
document will be copied to `web/` directory in state.

Here a sample of front matter:

    ---
    layout: doc
    title: React
    desciption: how to use react-php
    permalink: react.html
    tag: [php, react]
    navigation: [main]
    author: John Do
    ---

* **layout:** Twig layout to be used for rendering the page. Defaults to
`default`, which means `layouts/default.html.twig` is rendered unless specified
otherwise. {{ link('pages/cookbook/themes.md', 'See theme documentation for more
information')}}

* **title:** Title of the current document for display on the index, and for the
title page.

* **description:** Description of the current document for `meta` tag/

* **permalink:** Force the target path of the current document.

* **tags:** A collection of tag. Can be use to build collections of documents.

        ---
        tags: [nginx, varnish]
        # or
        tags:
            - nginx
            - varnish
        ---

* **navigations:** Add the current document to a collection of pages, organized
for building menu:

        ---
        navigation: [main]
        ---

All others keys will be stored in `metadatas` attribute of the current document.

### Directory structure

Now you can use the following directories:

* **assets:** All of these files will be copied to the web directory. You can
add JavaScript, CSS and images in here.

* **layouts:** The layouts are used to render the pages. They have a
`.html.twig` suffix. You can create base templates and have more specific ones
that extend them using the `extends` tag. The layouts are renderer with
[Twig](http://twig.sensiolabs.com). {{ link('pages/cookbook/themes.md', 'See theme
documentation for more information') }}.

* **pages:** Markdown files representing pages. Each one must begin with a YAML
front matter. Here is a sample page:

* **posts:** Markdown files representing blog posts. Must be in the format of
`YYYY-mm-dd-slug.md`, e.g. `2012-09-20-like-a-hacker.md`.

* **api**: Markdown, or not, files. Each one will be rendered in
`/api/filename.html`.

* **config.yml:** Yaml file with some configuration. All variables under `site`
will be sent to twig templates. Here is a sample:

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

    $ vendor/bin/carew build

This will populate the `web` directory with a set of files that can be deployed
onto any static web server.

You can change input / ouput directory. Run for more information:

    $ vendor/bin/carew help build

### Create a new blog post

Just run:

    $ vendor/bin/carew carew:generate:post [--date="..."] "title"

## What next?

Why not read all {{ link('pages/cookbook.md') }}?

{{ render_documents(carew.navigations.cookbook) }}
