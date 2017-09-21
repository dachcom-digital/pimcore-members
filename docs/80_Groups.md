# Groups

## Add Default Groups to new User

If you want to add some groups to a new user after a successfully registration, you need to extend the configuration.

```yaml
members:
    user:
        initial_groups:
            - 'Group 1' # via group name
            - 22 # via id
```