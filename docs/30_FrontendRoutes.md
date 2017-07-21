# Frontend Routes and Views

### Auth

Prefix: `/{_locale}/members`

| Route name | Path | View | Methods |
|------------------------------|--------------|----------------------|-----------|
| members_user_security_login | /login | Auth/login.html.twig | GET, POST |
| members_user_security_check | /login_check | -- | POST |
| members_user_security_logout | /logout | -- | GET, POST |

### Registration

Prefix: `/{_locale}/members/register`

| Route name | Path | View | Methods |
|------------------------------|--------------|----------------------|-----------|
| members_user_registration_register | / | Registration/show.html.twig | GET, POST |
| members_user_registration_check_email | /check-email | Registration/check_email.html.twig | GET |
| members_user_registration_check_admin | /check-admin | Registration/check_admin.html.twig | GET |
| members_user_registration_confirm | /confirm/{token} | -- | GET |
| members_user_registration_confirmed | /confirmed | Registration/confirmed.html.twig | GET |

### Change Password

Prefix: `/{_locale}/members/profile`

| Route name | Path | View | Methods |
|------------------------------|--------------|----------------------|-----------|
| members_user_change_password | /change-password | ChangePassword/change_password.html.twig | GET, POST |

### Profile

Prefix: `/{_locale}/members/profile`

| Route name | Path | View | Methods |
|------------------------------|--------------|----------------------|-----------|
| members_user_profile_show | / | Auth/show.html.twig | GET |
| members_user_profile_edit | /edit | Auth/edit.html.twig | GET, POST |
| members_user_restriction_refused | /refused | Auth/refused.html.twig | GET |

### Resetting

Prefix: `/{_locale}/members/resetting`

| Route name | Path | View | Methods |
|------------------------------|--------------|----------------------|-----------|
| members_user_resetting_request | /request | Resetting/request.html.twig | GET |
| members_user_resetting_send_email | /send-email | -- | POST |
| members_user_resetting_check_email | /check-email | Resetting/check_email.html.twig | GET |
| members_user_resetting_reset | /reset/{token} | Resetting/reset.html.twig | GET, POST |