# Class Customization

### Custom names
If you want to use different class names, you need to modify the default configuration.
Add those lines to your `config/config.yaml`:
    
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

### Custom object keys
You can define a form field (from the registration form) whose value is used to generate the key of the Pimcore object.
```yaml
members:
    user:
        adapter:
            object_key_form_field: 'username' # default was 'email'
```

> **Important:** Choose a unique field to prevent naming conflicts within Pimcore
