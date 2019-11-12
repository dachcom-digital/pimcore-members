# Custom Class Names

If you want to use different class names, you need to modify the default configuration.
Add those lines to your `AppBundle/Resources/config/pimcore/config.yml`:
    
```yaml
members:
    # for the user class
    user:
    	adapter:
            class_name: 'customer' # default was 'MembersUser'
    
    # for the group class
    group:
        adapter:
            class_name: 'group' # default was 'MembersGroup'

    # for the sso identity class
    # only available if you're using the SSO feature
    sso:
        adapter:
            class_name: 'identity' # default was 'SsoIdentity'
```

> **Tip:** Add this to a separate config file.