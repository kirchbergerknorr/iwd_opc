IWD_Opc
=================================

IWD Onepagecheckout
For more information please visit https://www.iwdagency.com/extensions/one-step-page-checkout.html

*Please note that we are not the developer of this extension. In this repo we only added modman/composer support. We will not provide any support for this repository. If you have any problems on integration, please use the official link mentioned above.*


Installation
------------

Add the `require` and `repositories` sections to your composer.json as shown in the example below. Then run `composer update`.

*composer.json example*

```
{
    ...
    
    "repositories": [
        {"type": "git", "url": "https://github.com/kirchbergerknorr/IWD_Opc"},
    ],
    
    "require": {
        "kirchbergerknorr/IWD_Opc": "*"
    },
    
    ...
}
```
