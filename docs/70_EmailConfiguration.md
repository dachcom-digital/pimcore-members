# Email Configuration

Members will install some emails for you by default. But you may need some more complex implementation.
You'll find the default mails in `/email/`.

### Default Paths
Feel free to move the mails to a different location and define it in your configuration:

```yaml
members:
    emails:
        default:
            register_confirm: '/email/register-confirm'
            register_confirmed: '/email/register-confirmed'
            register_password_resetting: '/email/password-reset'
            admin_register_notification: '/email/admin-register-notification'
```

### Mail Localization
If you have a multiple languages, you may want to use the `{_locale}` fragment in your paths:

```yaml
members:
    emails:
        default:
            register_confirm: '/{_locale}/email/register-confirm'
            register_confirmed: '/{_locale}/email/register-confirmed'
            register_password_resetting: '/{_locale}/email/password-reset'
            admin_register_notification: '/{_locale}/email/admin-register-notification'
```

### Site based Mail Templates
If you have multiple sites, you have to define them like this:

```yaml
members:
    emails:
        # if no site gets found, the default stays as fallback.
        default:
            register_confirm: '/{_locale}/email/register-confirm'
            register_confirmed: '/{_locale}/email/register-confirmed'
            register_password_resetting: '/{_locale}/email/password-reset'
            admin_register_notification: '/{_locale}/email/admin-register-notification'
        sites:
            -
                main_domain: 'your-domain1'
                emails:
                    register_confirm: '/domain1/email/register-confirm'
                    register_confirmed: '/domain1/email/register-confirmed'
                    register_password_resetting: '/domain1/email/password-reset'
                    admin_register_notification: '/domain1/email/admin-register-notification'
            -
                main_domain: 'your-domain2'
                emails:
                    register_confirm: '/domain2/{_locale}/email/register-confirm'
                    register_confirmed: '/domain2/{_locale}/email/register-confirmed'
                    register_password_resetting: '/domain2/{_locale}/email/password-reset'
                    admin_register_notification: '/domain2/{_locale}/email/admin-register-notification'
```

### Locale Mapping
Since Members allows to activate users through backend, the locale definition may gets lost.
If a user gets registered, Members will append two properties to the user object:

- `_site_domain`: optional, if the registration happens in a site request, the main domain gets stored.
- `_user_locale`: the registration request locale.