# Installation
The sso connector is disabled by default for flexibility reasons.
But it's quite easy to enable it. Use our helper command, which guides you through the installation process:

## Helper Command
![oauth2 helper command](https://user-images.githubusercontent.com/700119/68656716-a00e0b00-0532-11ea-863f-664ef3c55ea7.png)

```bash
$ bin/console members:oauth:setup
```

If you don't want to use it, you need to check several steps which we're going to explain to you right now:

## Install Classes
-- 

## Add SSO Identity Relation Field
--

## Add SSO IdentityAwareInterface
-- 

## Install Dependencies
--

## Enable Feature

```yaml
members:
    oauth:
        enabled: true
        activation_type: 'complete_profile' # choose between "complete_profile" and  "instant"
```