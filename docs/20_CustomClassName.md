# Custom Class Names

If you want to use different class names, you need to modify the default configuration.
Add those lines to your `AppBundle/Resources/config/pimcore/config.yml`:
    
```yaml
members:
    adapter:
        class_name: 'customer' # default was 'membersUser'
```

> **Tip:** Add this to a separate config file.