# Upgrade Notes

#### Update from Version 1.3.x to Version 1.4.0
- new config element: `routes.login.redirectAfterRefusal`:
```php
\Members\Model\Configuration::set('routes.login.redirectAfterRefusal', '/%lang/members/refused');
```
- add a `members/refused` page:
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