---
title: How to use and create plugins?
layout: doc2
navigations: cookbook
---

Existing Plugins
----------------

* [Sami](https://github.com/carew/plugin-sami#readme): Build api doc from carew
* [Next](https://github.com/gnugat/carew-next#readme): Functions to get next/previous document

Installation
------------

You can use as many plugins as you want.

1. Add the dependency with composer. Generally, the dependency can be found in
the `composer.json` file in the theme repository.

1. Register theme in the `config.yml` file:

        #config.yml
        engine:
            extensions:
                - Carew\Plugin\Toc\TocExtension

That's it.

How to write a plugin
---------------------

The plugin must implements `Carew\ExtensionInterface`.

`Carew` will call `ExtensionInterface::register` and give itself
to the extension. So the plugin can alter everything.

The plugin can access to:

* The `carew` instance
* The `container`, an instance of [pimple](http://pimple.sensiolabs.org/).
* The `event_dispatcher`, an instance of [Symfony EventDispatcher](https://github.com/symfony/EventDispatcher).

You can have a look to the `CoreExtension` for more information.

Learn more about the carew architecture in the {{ link('pages/cookbook/internal.md',
'internal chapter') }}.

You have created a plugin
-------------------------

You have created a plugin and you want to share it?
Write me an [email](mailto:lyrixx@lyrixx.info) and I will create a new
repository on [github/carew](https://github.com/carew) for you, or send me a
[pull request](https://github.com/carew/carew.github.com/edit/master/_carew/pages/cookbook/plugins.md)
and add a new link.
