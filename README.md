[ ![Image](https://aplazame.com/static/img/banners/banner-728-white-prestashop.png "Aplazame") ](https://aplazame.com "Aplazame")

[![Package version](https://img.shields.io/packagist/v/aplazame/prestashop.svg)](https://packagist.org/packages/aplazame/prestashop) [![Build Status](http://drone.aplazame.com/api/badges/aplazame/prestashop/status.svg)](http://drone.aplazame.com/aplazame/prestashop) [![Dependencies](https://www.versioneye.com/php/aplazame:prestashop/badge.svg)](https://www.versioneye.com/php/aplazame:prestashop)

### Install

1. **Download** the latest plugin from [here](https://s3.eu-central-1.amazonaws.com/aplazame/modules/prestashop/aplazame.latest.zip) to local directory as `aplazame.latest.zip`.
2. Go to the PrestaShop administration page, and then go to **Modules** > **Modules**.
3. **Add new module** and select the `aplazame.latest.zip` file from your computer.

### Update

1. **Install**
2. **Reset** the module.

### Configure

![config](docs/config.png)

* **Sandbox**: Determines if the module is on Sandbox mode.
* **Button**: The CSS Selector for Aplazame payment method. The default selector is `#aplazame_payment_button`. [See bellow](#one-step-checkout-button) to configure button with One Step Checkout modules.
* **Button Image**: [Select the image](http://docs.aplazame.com/#buttons) that appear as payment method on you cart. The default image is `white-148x46`.
* **Secret API Key**: The Secret Key provided by Aplazame. You cannot share this key with anyone!!
* **Public API Key**: The Public Key provided by Aplazame.

> Be sure that on all fields you don't keep any whitespace. Otherwise the module can generate unexpected results.

#### One Step Checkout button

If you have this module set this value in the **Button** field.

* [One Page Checkout PS](http://www.presteamshop.com/modulos-prestashop/one-page-checkout-prestashop.html): `table#table_payment tr:has(input[value='aplazame'])`

> It's important to use simple quotation marks for button CSS Selector.


#### Live demo

This is the online demo for uses to test Aplazame and its features.

[http://prestashop.aplazame.com](http://prestashop.aplazame.com)


#### Install Prestashop

It is easy to deploy Prestashop with [Ansible](http://www.ansible.com/home)!

[https://github.com/aplazame/ansible-prestashop](https://github.com/aplazame/ansible-prestashop)


#### Release history

For new features check [this](HISTORY.md).


#### Help

**Have a question about Aplazame?**

For any support request please drop us an email at [soporte.prestashop@aplazame.com](mailto:soporte.prestashop@aplazame.com?subject=Help me with the module).
