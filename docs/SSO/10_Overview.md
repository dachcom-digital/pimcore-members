# Single Sign On (SSO)

Members supports integration with third-party services for single sign on. 
Services like Google, Facebook etc. can be integrated and used as SSO providers for user registration and user login while Members will act as SSO client.

## Dependencies
We're using the [KnpUOAuth2ClientBundle](https://github.com/knpuniversity/oauth2-client-bundle) which relies on [league/oauth2-client](https://oauth2-client.thephpleague.com).

## Integration Types
There are two integration types: "*Login*" and "*Connect*". 
The login type comes with two sub workflows: "*complete_profile*" and "*instant*"
Read more about the integration types [here](./11_IntegrationTypes.md)

## Downsides
Currently, there is no oAuth1 Support.