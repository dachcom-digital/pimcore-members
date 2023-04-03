# Upgrade Notes
![upgrade](https://user-images.githubusercontent.com/700119/31535145-3c01a264-affa-11e7-8d86-f04c33571f65.png)  

***

After every update you should check the pimcore extension manager. 
Just click the "update" button or execute the migration command to finish the bundle update.

#### Update from Version 3.1.5 to Version 3.1.6
- **[ENHANCEMENT]**: It is now possible to download protected assets from a S3 bucket ([@aarongerig](https://github.com/dachcom-digital/pimcore-members/pull/173))

#### Update from Version 3.1.4 to Version 3.1.5
- **[ENHANCEMENT]**: You can now define a form field which is later used as object-key in Pimcore, when a user registers ([#154](https://github.com/dachcom-digital/pimcore-members/issues/154))

#### Update from Version 3.1.3 to Version 3.1.4
- **[ENHANCEMENT]**: Improving and adding additional Events for Restriction Changes on Entities ([#148](https://github.com/dachcom-digital/pimcore-members/issues/148))
- **[ENHANCEMENT]**: Update Twig navigation to allow parameters ([@kjkooistra-youwe](https://github.com/dachcom-digital/pimcore-members/pull/147))
- **[ENHANCEMENT]**: Protect documents starting with admin ([@youwe-petervanderwal](https://github.com/dachcom-digital/pimcore-members/pull/145))

#### Update from Version 3.1.2 to Version 3.1.3
- **[ENHANCEMENT]**: Pimcore 6.6.5 ready
- **[ENHANCEMENT]**: only set Initial Groups if present ([#135](https://github.com/dachcom-digital/pimcore-members/pull/135))
- **[ENHANCEMENT]**: change file write mode to `w` only, to allow installation on AWS ([#137](https://github.com/dachcom-digital/pimcore-members/issues/137))
- **[BUGFIX]**: Implement `preSetData` ([#140](https://github.com/dachcom-digital/pimcore-members/issues/140))
- **[BUGFIX]**: [GroupMultiselect] Dependencies resolver added ([#139](https://github.com/dachcom-digital/pimcore-members/issues/139))

#### Update from Version 3.1.1 to Version 3.1.2
- **[ENHANCEMENT]**: Pimcore 6.6.0 ready

#### Update from Version 3.1.0 to Version 3.1.1
- **[ENHANCEMENT]**: Allow to redirect back to requested secure page after login: [#133](https://github.com/dachcom-digital/pimcore-members/issues/133)
- **[BUGFIX]**: Use right route form login `check_path` 

#### Update from Version 3.0.1 to Version 3.1.0
- **[ENHANCEMENT]**: Pimcore 6.4.0 and 6.5.0 ready
- **[NEW FEATURE]**: [SSO via OAuth2](https://github.com/dachcom-digital/pimcore-members/issues/21)
- **[IMPROVEMENTS]**: [Make Mailer-Implementation switchable](https://github.com/dachcom-digital/pimcore-members/issues/107)
- **[IMPROVEMENTS, SECURITY BC BREAK]**: [Harmonize Asset Restriction Query](https://github.com/dachcom-digital/pimcore-members/issues/118): Every asset living in `restricted-assets` will be rejected in listing if you're using the `addRestrictionInjection()` method by default

#### Update from Version 3.0.0 to Version 3.0.1
- **[IMPROVEMENTS]**: [User helper functions added](https://github.com/dachcom-digital/pimcore-members/issues/105)
- **[BUGFIX]**: [Fix UserAwareEncoderFactory auto loading issue](https://github.com/dachcom-digital/pimcore-members/issues/114)
- **[BUGFIX]**: [Fix wrong wrong conditions in restriction manager](https://github.com/dachcom-digital/pimcore-members/issues/115)

#### Update from Version 2.x to Version 3.0.0
- **[NEW FEATURE]**: Pimcore 6.0.0 ready
- **[BC BREAK]**: All Services are marked as private now (Except `RoleOptionsProvider`).
- **[BC BREAK]**: All Controllers are registered as services now! If you're using you'r own controller logic, be sure the're adjusted properly!
- **[ATTENTION]**: All Forms are registered in FQCN now. Aliases for the old service IDs are available.

***

Members 2.x Upgrade Notes: https://github.com/dachcom-digital/pimcore-members/blob/2.5/UPGRADE.md
