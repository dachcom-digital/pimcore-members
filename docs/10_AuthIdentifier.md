# Auth identifier
By default, MembersBundle will use the `userName` field from your Users entity.
If you want to use the `email` field instead, you need to tell it like this:

```yaml
members:
    user:
        auth_identifier: 'email' # can be "username" (default) or "email"
```

## Auth Identifier only for registration
You're able to define if your registration form should only contain the `auth_identifier` field (optional).
If `only_auth_identifier_registration` is set to `true` (`false` by default) and `members.user.auth_identifier` is set to `email` for example, the registration form type will only show up with `email` and `password` fields. 
By default `email`, `password` and `password` fields are required for registration form.

> Attention! If you're using `username` as only registration field, your user entity won't provide an email address!
> Keep that in mind when it comes to confirming users profile!
 
```yaml
members:
    user:
        only_auth_identifier_registration: true
```