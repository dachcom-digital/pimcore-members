# Roles

Every Group is connected with the `ROLE_USER` role by default.

## Add Roles

Adding Roles is quite simple. Add this to your `app/config/config.yml`:

```yaml

security:
    role_hierarchy:
        ROLE_MEMBERS_MODERATOR: [ROLE_USER]
        
```

Open your group object, select `ROLE_MEMBERS_MODERATOR` and save group. 

> Note: Make sure that your user is connected with this group.

## Example
 
 Check the Role in a controller

```php
<?php

class RoleController {
    
    public function checkAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_MEMBERS_MODERATOR', null, 'Unable to access this page!');
    }
}
    
```