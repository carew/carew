---
title: How to configure carew?
layout: doc2
navigations: cookbook
---

You can configure carew thanks to `config.yml` file. This file should be in  the
root directory. Here is a sample:

    site:
        title:        Carew
        description:  The tiny website generator
        author_name:  Grégoire Pineau
        author_email: lyrixx@lyrixx.info
        author_url:   http://gregoirepineau.fr

    engine:
        themes:
            - %dir%/vendor/lyrixx/my-theme-bootstrap
            - %dir%/vendor/carew/theme-bootstrap
        extensions:
            - Carew\Plugin\Sami\SamiExtension

Most values under the `site` section are default values for your layout. Each
values are available in your template with the following syntax:

    {% verbatim -%}
    {{ carew.site.decription }}
    {# render: The tiny website generator #}
    {%- endverbatim %}

The `engine` section is used to configure carew's internal. Learn more about
{{ link('pages/cookbook/themes.md', 'theming') }} and about
{{ link('pages/cookbook/plugins.md', 'extension') }} in dedicated chapters.
