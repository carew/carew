---
title: How to use and create theme?
layout: doc2
navigations: cookbook
---

<div class="pull-right">
    {{ render_document_toc(carew.document) }}
</div>

What is a theme?
----------------

A theme is some default layouts / templates, blocks, and / or assets. You can use multiple
theme in the same project. So thanks to theming, you can easily customize the
rendering of your blog / website. There is two king of theme:

* Personal theme, not shareable. In this case, just put yours templates in the
`layouts/` directory, in the root directory.

* Common theme, shareable. In this case, you can install theme created by the
community.

Existing Themes
---------------

* [Twitter bootstrap](http://github.com/carew/theme-bootstrap/)

Installation
------------

You can use as many themes as you want. This section is only for theme created
by the community.

1. Add the dependency with composer. Generally, the dependency can be found in
the `composer.json` file in the theme repository.

1. Register theme in the `config.yml` file:

        #config.yml
        engine:
            themes:
                - %dir%/vendor/carew/theme-bootstrap

Theme folder can contain `layouts` and `assets` folders. The `%dir%` parameter
will be replaced by the current directory (i.e. the directory that contains the
`config.yml` file).

**Note:** The order matter. Carew will search for template in your `layouts/`
folder,  then in themes folder registered in the configuration, then fallback to
the default theme.

Customization
-------------

If you want to replace a template, create a new template in your `layouts/`
directory with the same name as the original one.

You can also extends the original one with `extends`:

    {% verbatim -%}
    {# my_project/layouts/default.html.twig #}
    {% extends 'vendor/carew/theme-bootstrap/layouts/default.html.twig' %}

    {% block nav_right %}
        <ul class="nav pull-right">
            <li class="dropdown">
                ...
            </li>
        </ul>
    {% endblock %}
    {%- endverbatim %}

Default layouts are in a special namespace `default_theme`:

    {% verbatim -%}
    {# my_project/layouts/default.html.twig #}
    {% extends '@default_theme/default.html.twig' %}

    {% block nav_right %}
        {{ parent() }}
        <ul class="nav pull-right">
            <li class="dropdown">
                <a href="http://gregoirepineau.fr">Visit my personal website</a>
            </li>
        </ul>
    {% endblock %}
    {%- endverbatim %}


Blocks theming
--------------

With carew, you have {{ link('pages/cookbook/helper.md', 'useful helper set')
}}. Almost all helper are customizable thanks to special `blocks.html.twig`. Of
course, you can overide this template:

    {% verbatim -%}
    {% use '@default_theme/blocks.html.twig' %} {# Reimport default blocks #}

    {% block document_toc %}
    {% spaceless -%}
        {% if 0 == deep %}<div class="well">{% endif %}
        <ul class="nav nav-list">
            {% if 0 == deep %}
                <li class="nav-header">Quick access</li>
            {% endif %}
            {% for child in children %}
                <li>
                    <a href="#{{ child.id }}">{{ child.title }}</a>
                    {% if child.children %}
                        {{ render_document_toc(child.children, deep + 1) }}
                    {% endif %}
                </li>
            {% endfor %}
        </ul>
        {% if 0 == deep %}</div>{% endif %}
    {%- endspaceless %}
    {% endblock %}
    {%- endverbatim %}

You have created a theme?
-------------------------

You have created a theme and you want to share it ? Write me an
[email](mailto:lyrixx@lyrixx.info) and I will create a new repository on
[github/carew](https://github.com/carew) for you, or send me a
[pull request](https://github.com/carew/carew.github.com/edit/master/_carew/pages/cookbook/themes.md)
and add a new link.
