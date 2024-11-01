=== HUEM: Huge Upload Enabler, mostly ===
Contributors: frodeborli
Tags: context menu, content aware menu, usability, right click, upload limit, upload size, productivity, user interface, ux, user experience
Requires at least: 4.6
Tested up to: 4.7
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allows you to bypass the upload size limitations of the server.


== Description ==

HUEAM is a plugin that tries to make improvements to WordPress, while not interfering with the way WordPress works. When you install the plugin, you will not notice any immediate changes - but these features have been silently enabled.

* Smart Context Menus
* Chunked uploads to bypass php.ini and .htaccess upload limitations.

We're looking into more exciting improvements to add, and you will be able to easily enable or disable them.

== Smart Context Menus ==

Right clicking any element in site preview mode now will render a nice context menu - that will take you directly to the appropriate admin page for editing. You (or your customer) will never again have to ask where to find the feature. It will save you thousands of mouse clicks.

Pro tip: If you want to see the browser native context menu, simply hold CTRL while right clicking.

Pro tip 2: You can extend the context menu by using the filter `wpa_context_menu`.

Pro tip 3: Disable the context menus in your wp-config.php file like this:

`define('WPAWESOME_DISABLE_CONTEXT_MENUS', true);`


== Huge uploads ==

By making WordPress upload files in multiple chunks, you can now upload huge files through the standard WordPress user interface. No more editing of  .htaccess files, or php.ini files. The uploaded file will be split into chunks that are 1.9 MB large. The chunks are stored in the temp folder - until all chunks have been uploaded. At that time - the plugin will combine all the chunks into one file again.

Pro tip: You can override the maximum upload size by defining the `WPAWESOME_UPLOAD_LIMIT` constant. 

`define('WPAWESOME_UPLOAD_LIMIT', '10000000'); // 10 MB upload limit`

Pro tip 2: Disable the context menus in your wp-config.php file like this:

`define('WPAWESOME_DISABLE_HUGE_UPLOADS', true);`

== 3 * Esc to login ==

Clicking ESC three times, when you're not logged in - will take you directly to the login page. This way, you won't have to remember the URL of the page that prompted you to login. You'll be there instantly after login, and then you can right click the article (or widget or comment) to edit it.

Pro tip: Disable the context menus in your wp-config.php file like this:

`define('WPAWESOME_DISABLE_FAST_LOGIN', true);`

== Screenshots ==

1. Right click to edit a post
2. Right click to edit widgets
3. Set upload limit, without worrying about php.ini or .htaccess file changes.
4. Upload limit is instantly updated, without reloading the web server.
5. Supports any custom post type, such as WooCommerce products.
6. Intelligently detects the content you're clicking, to display a very useful context menu.


== Contribute ==


= Module Authors =

The plugin can be easily extended by adding a filter `add_filter( 'wpa_context_menu', 'my_context_menu', 10, 2)`. The `function my_context_menu` must accept $menu and $items arguments. $items is inspected to detect which element the user clicked. You can look at tag names, class names, element ids and more. If you want to add menu items, you'll simply modify the $menu array structure to add your own choices.

The best way to contribute, is if your module or theme integrates with HUEAM or if you contribute a patch. Patches should fix bugs, improve the user experience or add features. Please don't provide patches that make WP Awesome support other modules, unless that other module is extremely popular.

There's a [GIT repository and wiki](https://bitbucket.org/wpmotor/wpawesome) too if you want to contribute a patch, a translation, provide bug reports or in any other way contribute.


= Translators =

The plugin currently has very few translatable strings, but there are a couple. If you want to contribute with translations there are two ways: 

1. Send a pull request to our bitbucket repository, with your translation file in the Languages/ folder.

2. Upload your pot-file to  https://bitbucket.org/wpmotor/wpawesome/issues/new and mark it as a proposal. We support translation files, but have only a very few translations available at the moment.


== Recommended Settings ==

There are no configuration needed to use this plugin. Just install it, and you are ready. Setting defaults can be configured in wp-config.php, and they can be overridden in the WP Awesome options panel.
