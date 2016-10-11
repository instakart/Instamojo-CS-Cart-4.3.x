# Instamojo's CS-Cart 4.3.x and Multi-Vendor 4.3.x Addon

## Download:

Download the zip file from [latest release](https://github.com/Instamojo/Instamojo-CS-Cart-4.3.x/releases/latest).

## Installation

#### Automatic Installation

1. In CS-Cart's admin backend navigate to `Addons -> Manage add-ons`.
2. Click on plus icon(`+`) on the right side to upload and install add-on.
3. Now select on zip file you had downloaded earlier and click on *Upload & install*.
4. Now search for Instamojo in your plugins and click on "Install" button corresponding to Instamojo module.

#### Manual Installation:

1. Extract the zip file and copy the content of `upload` directory in your CS-Cart installation root directory
2. Now in your CS-Cart's admin backend navigate to `Addons -> Manage add-ons` and click on `Browse all available add-ons`.
3. Look for Instamojo and click on install.


## Configuration
1. In CS-Cart's admin click on `Administration-> Payment Methods`. 
2. Look for `Instamojo` and click on setting dropdown and select `edit`.
3. Feel free to change fields in general tab except processor and template.
4. Click on `configure` tab and fill the following fields
    
    - **Client ID** and **Client Secret** - Client Secret and Client ID can be generated on the [Integrations page](https://www.instamojo.com/integrations/). Related support article: [How Do I Get My Client ID And Client Secret?](https://support.instamojo.com/hc/en-us/articles/212214265-How-do-I-get-my-Client-ID-and-Client-Secret-)

    - **Test Mode:** If enabled you can use our [Sandbox environment](https://test.instamojo.com) to test payments. Note that in this case you should use `Client Secret` and `Client ID` from the test account not production.

Phone number is a required field for this addon to work properly. Go to `Administration -> Profile fields` and mark the `Phone` field as both `Show / Required` during checkout.


## Migrating from older version(version < 2.0.0)

If you were already using older version of our plugin then follow these steps:

1. In CS-Cart's admin backend navigate to `Addons -> Manage add-ons`.
2. Look for `Instamojo` and click on setting dropdown and select `Uninstall`.
3. Now remove the following files:
    - app/payments/instamojo.php
    - design/backend/templates/views/payments/components/cc_processors/instamojo.tpl

## Support

For any issue send us an email to support@instamojo.com and share the `imojo.log` file. The location of `imojo.log` file is `var/log/imojo.log`.
 