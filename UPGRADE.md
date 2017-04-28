# Upgrade Notes

#### Update from Version 1.3.x to Version 1.4.0

**Config**  
- new config elements:
```php
\Members\Model\Configuration::set('routes.login.redirectAfterRefusal', '/%lang/members/refused');
\Members\Model\Configuration::set('sendRegisterConfirmedFromAdmin', FALSE);
\Members\Model\Configuration::set('emails.registerConfirmed', '/%lang/members/emails/register-confirmed');
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