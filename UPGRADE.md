# Upgrade Notes
![upgrade](https://user-images.githubusercontent.com/700119/31535145-3c01a264-affa-11e7-8d86-f04c33571f65.png)  

***

After every update you should check the pimcore extension manager. 
Just click the "update" button or execute the migration command to finish the bundle update.

#### Update from Version 2.x to Version 3.0.0
- **[NEW FEATURE]**: Pimcore 6.0.0 ready
- **[BC BREAK]**: All Services are marked as private now (Except `RoleOptionsProvider`).
- **[BC BREAK]**: All Controllers are registered as services now! If you're using you'r own controller logic, be sure the're adjusted properly!
- **[ATTENTION]**: All Forms are registered in FQCN now. Aliases for the old service IDs are available.

***

#### Update from Version 2.4.x to Version 2.5.0
- **[ATTENTION]**: This release will be the last minor release which supports Pimcore 5!
- ([Milestone for 2.5.0](https://github.com/dachcom-digital/pimcore-members/milestone/7?closed=1)

#### Update from Version 2.3.x to Version 2.4.0
- **[BUGFIX]**: [Restricted Assets Download](https://github.com/dachcom-digital/pimcore-members/issues/94) wrong size header in pimcore > 5.6
- **[BUGFIX]**: [MembersUser groups field does not load/save in admin backend](https://github.com/dachcom-digital/pimcore-members/issues/95) in pimcore > 5.8

#### Update from Version 2.2.x to Version 2.3.0
- **[ATTENTION]**: Installer has moved to the [MigrationBundle](https://github.com/dachcom-digital/pimcore-members/issues/74). After updating to this version you need to enable this extension again!
- **[ATTENTION]**: The user class validation is now mapped via UserInterface (see [#76](https://github.com/dachcom-digital/pimcore-members/issues/76)).
- **[ATTENTION]**: Validation messages has been moved to validation files (see [#66](https://github.com/dachcom-digital/pimcore-members/issues/66)).
- Various Fixes: [See Milestone](https://github.com/dachcom-digital/pimcore-members/milestone/5?closed=1).

#### Update from Version 2.2.0 to Version 2.2.1
- implemented [PackageVersionTrait](https://github.com/pimcore/pimcore/blob/master/lib/Extension/Bundle/Traits/PackageVersionTrait.php)

#### Update from Version 2.1.x to Version 2.2.0
- **[NEW FEATURE]**: Restriction Icons in Tree View. See [#63](https://github.com/dachcom-digital/pimcore-members/issues/63)
- Some Bugfixes

#### Update from Version 2.0.x to Version 2.1.0
- **[BC BREAK]**: Members does not include any routes automatically anymore! Please include the [routes manually](https://github.com/dachcom-digital/pimcore-members#route-installation) if needed.
- **[BC BREAK]**: Validation messages removed from `messages` domain. Read more about it [here](https://github.com/dachcom-digital/pimcore-members/issues/45).
- **[BC BREAK]**: Render Forms via Symfony Form Theme (Bootstrap4 by default). See [#41](https://github.com/dachcom-digital/pimcore-members/issues/41)
- **[NEW FEATURE]**: User deletion. See [#48](https://github.com/dachcom-digital/pimcore-members/issues/48)

***

#### Update from Version 1.x to Version 2.0.0
- TBD