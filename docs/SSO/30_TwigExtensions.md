# Twig Extension

## members_system_oauth_enabled
Check if oauth has been enabled:

```twig
{% if members_system_oauth_enabled() == true %}
    {# oauth is enabled #}
{% endif %}
```

## members_oauth_social_links
List all available social connectors:

```twig
{% set social_route_name = 'members_user_security_oauth_login' %}
{% set skip_connected_identities = false %}

{% dump(members_oauth_social_links(social_route_name, skip_connected_identities)) %}

```