# Twig Extension

> **Note:** Always call `members_system_oauth_enabled()` first, the oauth extension could be unavailable!

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
{% if members_system_oauth_enabled() == true %}
    {% set social_route_name = 'members_user_security_oauth_login' %}
    {% set skip_connected_identities = false %}

    {% dump(members_oauth_social_links(social_route_name, skip_connected_identities)) %}
{% endif %}
```

## members_oauth_can_complete_profile
Check if a new registered SSO user can enter the profile completion route:

```twig
{% if members_system_oauth_enabled() == true %}
    {% if members_oauth_can_complete_profile() == true %}
        {# do something funky #}
    {% endif %}
{% endif %}
```