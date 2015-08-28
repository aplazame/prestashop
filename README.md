[ ![Image](https://aplazame.com/static/img/banners/banner-728-white.png "Aplazame") ](https://aplazame.com "Aplazame")

### Install

To install the plugin, copy folders from the repository and activate the plugin on the administration page:

1. Download plugin from [the plugin repository](https://github.com/aplazame/prestashop/archive/master.zip).
2. Unzip locally downloaded file
3. **Create zip archive of aplazame directory**
4. Go to the PrestaShop administration page.
5. Go to **Modules** > **Modules**.
6. **Add new module** and point archive contained plugin (created at point 3)
7. Load the plugin

### Configure

Once the module is installed, we need to configure with our Aplazame credentials provided by Aplazame Team. 

![config](docs/config.png)

- **Live mode - Sandbox Mode**: If you choose "NO", you have your module running on SandBox Mode (No charges against cards). Otherwise will be Production Mode. Verify that the Public and Secret Keys corresponding to the environment for. If you have any question about your keys, contact with Aplazame Team.
- **Host**: Here you need to write the URL that is provided by Aplazame. Default value is: **https://aplazame.com**
- **API Version**: Here you need to write the VERSION that is provided by Aplazame. Default value is: **v1.2**
- **Button**: This value is the DOM id for your payment method on the cart. Is needed to hide if Aplazame is not ready to place a order in the system. The default value is **aplazame_payment_button**
- **Button Image**: With this you can change the image that appear as payment method on you cart. Available at this moment:
    - **button1**:  [ ![Image](https://aplazame.com/static/img/buttons/button1.png "Aplazame") ](https://aplazame.com "Aplazame")


- **Secret API Key**: Here is the Secret Key provided by Aplazame. Be sure to write the correct key that is for Live or Sandbox. You cannot share this key with anyone!!!
- **Public API Key**: Here is the Public Key provided by Aplazame. Be sure to write the correct key that is for Live or Sandbox. 
- **Enable Cookies**: If you want to enable cookie tracking. Default Yes. 

> NOTE: Be sure that on all fields you don't keep any whitespace. Otherwise the module can generate unexpected results. 


#### Live demo

This is the online demo for uses to test Aplazame and its features. 

[http://prestashop.aplazame.com](http://prestashop.aplazame.com)


#### Install Prestashop

It is easy to deploy Prestashop with [Ansible](http://www.ansible.com/home)!

[https://github.com/aplazame/ansible-prestashop](https://github.com/aplazame/ansible-prestashop)


#### Release history

For new features check [this](History.md).


#### Help

**Have a question about Aplazame?**

For any support request please drop us an email at email soporte@aplazame.com.
