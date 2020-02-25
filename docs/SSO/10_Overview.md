# Single Sign On (SSO)

Members supports integration with third-party services for single sign on. 
Services like Google, Facebook etc. can be integrated and used as SSO providers for user registration and user login while Members will act as SSO client.

(![image](https://user-images.githubusercontent.com/700119/75279841-a176f100-580c-11ea-84ee-ff8ca12f3c4a.png))

## Dependencies
We're using the [KnpUOAuth2ClientBundle](https://github.com/knpuniversity/oauth2-client-bundle) which relies on [league/oauth2-client](https://oauth2-client.thephpleague.com).

## Integration Types
There are two integration types: "*Login*" and "*Connect*". 
The login type comes with two sub workflows: "*complete_profile*" and "*instant*"
Read more about the integration types [here](./11_IntegrationTypes.md)

## Downsides
Currently, there is no oAuth1 Support.

## Clean expired identities
By default, no identities get deleted after its expiration. 
However, you may want to enable the [clean up listener](./31_Listener.md).