# Integration Types
There are two basic integration types:

## Login
Triggers, if a new user want's to connect. That said, there are two ways to complete users registration:

### Instant Login
A successfully connected users gets logged in instantly. 

```yaml
members:
    oauth:
        enabled: true
        activation_type: 'instant'
```

### Login via Profile Completion
After the request was successful, the user gets redirected to the registration form. 
There is no need to enter a password. Some fields may already be pre-filled (Read more about it [here](./12_ResourceMapping.md).

> **Attention**: The `validation_group` of `RegistrationFormType` changes to `SSO` while registration is in `complete_profile` mode.

```yaml
members:
    oauth:
        enabled: true
        activation_type: 'complete_profile'
```

## Connect
Triggers, if an existing user want's to connect a profile with a given provider (e.g. Google).
A list of all possible connectors are available in user's profile (There are some [twig extensions](./30_TwigExtensions.md)).