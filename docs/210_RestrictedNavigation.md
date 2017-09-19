# Restricted Navigation

Now - since you have restricted some documents to certain groups, you need to manipulate the pimcore navigation renderer.

**Navigation:** Do **not** use the default nav builder extension (`pimcore_build_nav`). Just use the `members_build_nav` to build secure menus. 
Otherwise your restricted pages will show up. This twig extension will also handel your navigation cache strategy.

### Usage
  
```twig
{% set nav = members_build_nav(currentDoc, documentRootDoc, null, true) %}
{{ pimcore_render_nav(nav, 'menu', 'renderMenu', { maxDepth: 2 }) }}
```
