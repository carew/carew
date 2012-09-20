# Balrog

The tiny static site generator.

## Usage

To start a new site, just add the dependency via composer:

    $ composer init --require igorw/balrog:dev-master -n

Now you can add the following directories:

* **assets:** All of these files will be copied to the web directory. You can
  add JavaScript, CSS and images in here.

* **layouts:** The layouts are used to render the pages. They have a
  `.html.twig` suffix. You can create base templates and have more specific
  ones that extend them using the `extends` tag.

  Layouts get access to following variables:

  * **post:** Contains the data of the current post (it is not set on the index
    page). A post has `title`, `body` and any additional front-matter meta data.
    `body` contains the markdown body rendered as html.

  * **posts:** A listing of all posts in reverse order of publication.

  * **relativeRoot:** The relative path from the current page to the root.
    Useful for referencing assets. Must always be followed by a slash, e.g.:
    `{{ relativeRoot }}/main.css`.

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

  Any instance of `$relativeRoot` will be replaced with the relative root, so
  you can reference an image like this:

      ![Amazing shark with lasers]($relativeRoot/shark.png)

In order to build the site, you can use the `balrog build` command:

    $ vendor/bin/balrog build

This will populate the `web` directory with a set of files that can be
deployed onto any static web server.

## Quote of the day

"Fly, you fools!" - Gandalf
