# Using Members with the Pimcore Customer Data Framework

The CMF is fully supported by Members.

## About Customer Management Framework
The Customer Management Framework (CMF) for Pimcore adds additional functionality for customer data management, segmentation, personalization and marketing automation. So it allows to aggregate customer data and user profiles, enrich data, link social profiles, build audience segments, trigger events, personalize experience, execute marketing automation and much more.
Read more about the CMF [here](https://github.com/pimcore/customer-data-framework).

## Configuration

1. [Install CMF](https://github.com/pimcore/customer-data-framework/blob/master/doc/02_Installation.md)
2. Install the [Customer Class](https://github.com/pimcore/customer-data-framework/tree/master/install/class_source/optional)
3. Change the parent class of this class to `\MembersBundle\Adapter\User\AbstractCustomerUser`
4. Add all the required [members fields](https://github.com/dachcom-digital/pimcore-members#class-installation).
5. Define the class name for the CMF customer (assuming that your class name is called `MembersCustomer`):

```yaml
pimcore_customer_management_framework:
    general:
        customerPimcoreClass: MembersCustomer
```

6. Define the class name for members:
```yaml
members:
    user:
        adapter:
            class_name: 'MembersCustomer'
```

7. Install Pimcore Members
