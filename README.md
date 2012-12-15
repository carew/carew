Carew
=====

The tiny static site generator.

**Note**: This application is based on the awesome
[work](https://github.com/igorw/balrog/) of [igor](https://github.com/igorw/balrog/)

Installation
------------

To start a new site, just add the dependency via composer:

    $ composer init
    $ composer require lyrixx/carew:dev-master

Usage
-----

Now you can add the following directories:

* **assets:** All of these files will be copied to the web directory. You can
  add JavaScript, CSS and images in here.

* **layouts:** The layouts are used to render the pages. They have a
  `.html.twig` suffix. You can create base templates and have more specific
  ones that extend them using the `extends` tag. The layouts are renderer
  with [Twig](http://twig.sensiolabs.com)

  Layouts get access to following variables:

  * **posts:** A listing of all posts in reverse order of publication.
  * **latest:** The latest post.
  * **tags:** A listint off all tags.
  * **pages:** A listing of all pages.

  * **relativeRoot:** The relative path from the current page to the root.
    Useful for referencing assets. Must always be followed by a slash, e.g.:
    `{{ relativeRoot }}/main.css`.

  Filenames that follow the `index.$format.twig` naming scheme will get compiled
  to an `index.$format` file.

  Filenames that follow the `tags.$format.twig` naming scheme will get compiled
  to `tags/$tagName.$format` files. Templates have access to `tag` and `posts`
  variables. Here `posts` contains only Posts with current tag

* **posts:** Markdown files representing blog posts. Must be in the format of
  `YYYY-mm-dd-slug.md`, e.g. `2012-09-20-like-a-hacker.md`. Each one must begin
  with a YAML front matter. Here is a sample blog post:

        ---
        layout: post
        title: Blogging Like a Hacker
        ---

        # Blogging Like a Hacker

        * I’m bloggin yo!
        * ORLY?
        * YARLY!

  The following fields are defined:

  * **title:** Title of the blog post for display on the index.
  * **layout:** Layout to be used for rendering this post. Defaults to `default`,
    which means `layouts/default.html.twig` is rendered unless specified
    otherwise.

  Other fields can be defined at will and used in the template.
  `document` variable contains the data of the current post.
  A post has `title`, `body` and any additional front-matter meta data.
  `body` contains the markdown body rendered as html.

* **pages:** Markdown files representing pages. Each one must begin
  with a YAML front matter. Here is a sample page:

        ---
        layout: page
        title: About
        ---

        # About me

        * I’m bloggin yo!
        * ORLY?
        * YARLY!

* **config.yml:** Yaml file with some configuration. All variables will be
  sent to twig templates. Here is a sample:

        site:
          author: Grégoire Pineau
          title:  Foo
          description: Bar

  And in the template:

        <html>
          <head>
            <title>{{ site.title }}</title>

In order to build the site, you can use the `carew build` command:

    $ vendor/bin/carew build

This will populate the `web` directory with a set of files that can be
deployed onto any static web server.

Quote of the day
----------------

"Fly, you fools!" - Gandalf
