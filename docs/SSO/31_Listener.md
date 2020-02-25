# Listeners

## Clean Up expired Identities
There two ways to clean up expired identities. 

> **Note:** This feature is not enabled by default!

### Important Notes
This Clean-Up task also removes the related user within some default exceptions:
- The user will **not be deleted** if the given user has other SSO identities 
- The user will **not be deleted** if a password has been defined (not empty rule)

You're able to change the default exceptions by alter the `OAUTH_IDENTITY_STATUS_DELETION` event. Read more about it [here](./32_IdentityStatusListener.md).

### I. Expired Date
Use the `clean_up_expired_tokens` configuration flag to enable the clean-up.
In this example, all identities, older than `expiresAt` will be removed.


```yaml
members:
    oauth:
        clean_up_expired_tokens: true
```

SQL Explanation:

```mysql
SELECT entites WHERE expiresAt IS NOT NULL AND expiresAt < NOW();
```

### II. Expired By TTL
Use the `clean_up_expired_tokens` configuration flag to enable the clean-up. Use the `expired_tokens_ttl` configuration flag to define a ttl in seconds.
In this example, all identities, older than 24h will be removed.

```yaml
members:
    oauth:
        clean_up_expired_tokens: true
        expired_tokens_ttl: 86400
```

SQL Explanation:

```mysql
SELECT entites WHERE o_creationDate < (UNIX_TIMESTAMP() - $TTL);
```