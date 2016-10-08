
Welcome to HybridAuth for Drupal

Installation:
-------------
Install as any other module: 
http://drupal.org/documentation/install/modules-themes

This module needs third-party library to work with authentication providers - 
HybridAuth.
Download it at https://github.com/hybridauth/hybridauth/releases and unpack into
'sites/all/libraries/hybridauth' directory.
HybridAuth library requires php-curl extension.

Execute "composer install" in 'sites/all/libraries/hybridauth' to generate the
autoload.php file in 'sites/all/libraries/hybridauth/vendor'. See
https://getcomposer.org/ for using Composer.

If you need additional providers support like Mail.ru - then you need to
copy needed additional providers to the library and clear Drupal caches.
For instance, to get Mail.ru provider working you need to copy
'additional-providers/hybridauth-mailru/Providers/Mailru.php' to
'hybridauth/Hybrid/Providers/Mailru.php', clear caches, and you are good to go.
After that you just need to configure your application ID, private and secret
keys at module configuration pages.

After installation please go through the configuration settings and grant your
users permission to use HybridAuth:
- anonymous users - to login using HybridAuth widget
- authenticated users - to add more HybridAuth identities to the account

Dependencies:
-------------
- Ctools module (http://drupal.org/project/ctools) - it is used for an overlay,
provider and icon pack plugins.

Icon packs:
-----------
Yes, you can now easily have your own icon packs as Ctools plugins.

To make it happen you need to implement hook_ctools_plugin_directory() in your
custom mymodule.module:
<code>
/**
 * Implements hook_ctools_plugin_directory().
 */
function mymodule_ctools_plugin_directory($module, $type) {
  if ($module == 'hybridauth' && $type == 'icon_pack') {
    return 'plugins/icon_pack';
  }
}
</code>

And then place your icon pack plugins into 'plugins/icon_pack/iconpackname".
This directory should contain 2 or 3 files - plugin definition and css/js files:
plugins/icon_pack/iconpackname/iconpackname.inc
<code>
/**
 * Plugin declaration.
 */
$plugin = array(
  'title' => t('Mymodule icon pack'),
  // Specify css file name to include.
  'css' => 'iconpackname.css',
  // Specify 'js' key if js file needs to be included.
  'js' => 'iconpackname.js',
);
</code>

Take a look at this module icon packs in "plugins/icon_pack" and you will
figure it out.

Themes can also define their icon packs - instead of implementing
hook_ctools_plugin_directory() you should just add this line to the theme .info
file:
<code>
plugins[hybridauth][icon_pack] = plugins/icon_pack
</code>

Recommended additions:
----------------------
It is recommended to have the following modules:
- Token (http://drupal.org/project/token) - to get a list of available tokens
on administration pages.
- Rules (http://drupal.org/project/rules) - to map HybridAuth data to user
profile fields and other great stuff. See this issue for a working example -
http://drupal.org/node/1808456
- Real name (http://drupal.org/project/realname) - as it caches display names
and improves performance of your site.

Troubleshooting:
----------------
If you have an issue with any provider not working - please check provider
documentation at http://hybridauth.sourceforge.net/userguide.html - just click
on the provider name and read the instructions related to registering your
application at the provider's site and configuring this application settings.

If nothing helps - welcome to the issue queue at
http://drupal.org/project/issues/hybridauth.
