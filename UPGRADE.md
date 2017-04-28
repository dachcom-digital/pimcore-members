# Upgrade Notes

#### Update from Version 1.3.x to Version 1.4.0

**Config**  
- new config element: `routes.login.redirectAfterRefusal`:
```php
\Members\Model\Configuration::set('routes.login.redirectAfterRefusal', '/%lang/members/refused');
```

**Pages**  
- add a page at `members/refused`:
```json
{
    "type": "page",
    "path": "members",
    "key": "refused",
    "name": "Refused",
    "title": "Access Refused",
    "module": "Members",
    "controller": "profile",
    "action": "refused",
    "template": "/members/profile/refused.php"
}
```

**Email**  
- new config elements: `sendRegisterConfirmedFromAdmin` and `emails.registerConfirmed`:
```php
Configuration::set('sendRegisterConfirmedFromAdmin', FALSE);
Configuration::set('emails.registerConfirmed', '/%lang/members/emails/register-confirmed');
```

- add a new email at `members/emails/register-confirmed` (get content from `install/documents-Members.json`):
```json
{
   "type": "email",
   "path": "members/emails",
   "key": "register-confirmed",
   "module": "Members",
   "controller": "email",
   "action": "email",
   "template": "/members/email/email.php",
}
```