[ ![Image](https://aplazame.com/static/img/banners/Banner-white-1.png "Aplazame") ](https://aplazame.com "Aplazame")

# Prestashop module

## Installation

To install the plugin, copy folders from the repository and activate the plugin on the administration page:

1. Download plugin from [the plugin repository](https://github.com/aplazame/prestashop/archive/master.zip).
2. Unzip locally downloaded file
3. **Create zip archive of aplazame directory**
4. Go to the PrestaShop administration page.
5. Go to **Modules** > **Modules**.
6. **Add new module** and point archive contained plugin (created at point 3)
7. Load the plugin

## Configuration

Once the module is installed, we need to configure with our Aplazame credentials provided by Aplazame Team. 

[ ![Image](http://www.webimpacto.es/images/image_aplazame.jpg "Aplazame") ](https://aplazame.com "Aplazame")

- **Live mode - Sandbox Mode**: If you choose "NO", you have your module running on SandBox Mode (No charges against cards). Otherwise will be Production Mode. Verify that the Public and Secret Keys corresponding to the environment for. If you have any question about your keys, contact with Aplazame Team.
- **API URL**: Here you need to write the URL that is provided by Aplazame. Default value is: **https://api.aplazame.com**
- **API Version**: Here you need to write the VERSION that is provided by Aplazame. Default value is: **v1**
- **Button**: This value is the DOM id for your payment method on the cart. Is needed to hide if Aplazame is not ready to place a order in the system. If you don't know what to write, write the following: **aplazame_payment_button**
- **Button Image**: With this you can change the image that appear as payment method on you cart. Available at this moment:
	- **button1** -  [ ![Image](http://www.webimpacto.es/images/aplazame_button1.jpg "Aplazame") ](https://aplazame.com "Aplazame")


- **Secret API Key**: Here is the Secret Key provided by Aplazame. Be sure to write the correct key that is for Live or Sandbox. You cannot share this key with anyone!!!
- **Public API Key**: Here is the Public Key provided by Aplazame. Be sure to write the correct key that is for Live or Sandbox. 
- **Enable Cookies**: If you want to enable cookie tracking. Default Yes. 

NOTE: Be sure that on all fields you don't keep any whitespace. Otherwise the module can generate unexpected results. 
