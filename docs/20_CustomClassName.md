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
```

> **Tip:** Add this to a separate config file.