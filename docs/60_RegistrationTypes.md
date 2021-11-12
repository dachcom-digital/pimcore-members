# Registration Types
You can choose between three types of registration completion:

### Confirm by Mail
Name: `confirm_by_mail`

> This is the default value.

After registration the user will receive a confirmation mail with a confirmation url. By clicking on that link the user gets automatically activated.

If `send_admin_mail_after_register` is enabled, the system will send a notification mail to a defined admin.

### Confirm By Admin
Name: `confirm_by_admin`

After registration the user has to wait until a authorized admin activates the user in backend.

If `send_user_mail_after_confirmed` is enabled, the system will send a notification mail to user.

### Confirm Instant
Name: `confirm_instant`

After registration the user gets automatically logged in without any further actions.

## Configuration
Add those lines to your `config/config.yaml`:
    
```yaml
members:
    # choose between 'confirm_by_mail', 'confirm_by_admin', 'confirm_instant'
    post_register_type: 'confirm_by_mail' 
    
    #optional: see "Confirm by Mail"
    send_admin_mail_after_register: false
    
    #optional: see "Confirm By Admin"
    send_user_mail_after_confirmed: false
```

## Registration Types with SSO
If you're using the [SSO feature](./SSO/10_Overview.md), you may want to define some independent mail workflows.
If Members detects an SSO registration process, you're able to define the registration type via the `post_register_type_oauth` flag.

> **Attention*: SSO registration types only works if `activation_type` has been set to `complete_profile`.

They work the same as above, however `confirm_instant` is the default value:

```yaml
members:
    # choose between 'confirm_by_mail', 'confirm_by_admin', 'confirm_instant'
    post_register_type_oauth: 'confirm_instant' 
```
