=== GoDaddy Email Marketing ===
Contributors: godaddy, fjarrett, jonathanbardo, eherman24, susanygodaddy, madmimi
Tags: email, forms, godaddy, mailing list, marketing, newsletter, opt-in, signup, subscribe, widget, contacts
Requires at least: 3.8
Tested up to: 5.2
Stable tag: 1.4.2
License: GPL-2.0
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add the GoDaddy Email Marketing plugin to your WordPress site! Easy to set up, the plugin allows your site visitors to subscribe to your email lists.

== Description ==

The GoDaddy Email Marketing Signup Forms plugin makes it easy to start building an email list to drive repeat traffic to your WordPress site! Use this plugin to add a signup form to your site in no time flat.

With a GoDaddy Email Marketing starter account, you can collect as many email addresses as you like for free. And you can send up to 50 total emails to try it out. To learn more about GoDaddy Email Marketing, check out an overview [here](https://www.godaddy.com/online-marketing/email-marketing).

[youtube https://youtu.be/0dNbib686ss]

Once the plugin is activated, you can easily add a default signup form to your site using a widget. Or you can build your own custom signup form in GoDaddy Email Marketing and add it to your site by using a widget, shortcode, or template tag.

Setup is easy; in the plugin Settings, simply enter your GoDaddy username and GoDaddy Email Marketing API key. Don’t have one? The plugin makes it easy to sign up.

**Official GoDaddy Email Marketing Signup Forms plugin features:**

* Automatically add new forms for users to subscribe to an email list of your choice.
* Insert unlimited signup forms using the widget, shortcode, or template tag.
* Try GoDaddy Email Marketing for free — no credit card required.

**Languages Supported:**

* English
* Dansk
* Deutsch
* Ελληνικά
* Español
* Español de México
* Suomi
* Français
* हिन्दी
* Bahasa Indonesia
* Italiano
* 日本語
* 한국어
* मराठी
* Bahasa Melayu
* Norsk bokmål
* Nederlands
* Polski
* Português do Brasil
* Português
* Русский
* Svenska
* ไทย
* Tagalog
* Türkçe
* Українська
* Tiếng Việt
* 简体中文
* 香港中文版
* 繁體中文

**Find a bug?**

Please fill out an issue [here](https://github.com/godaddy/wp-godaddy-email-marketing/issues).

== Installation ==

1. [Install the plugin manually](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation) by uploading a ZIP file, or [install it automatically](https://codex.wordpress.org/Managing_Plugins#Automatic_Plugin_Installation) by searching for **GoDaddy Email Marketing**.
2. Once the plugin has been installed, click **Activate**.
3. Nagivate to **Settings > GoDaddy Email Marketing** where you will find a **Settings** tab.
4. Follow the instructions to access your username and API key. If you don't have a GoDaddy Email Marketing account, you'll be able to create one.
5. Click **Save Settings**.

After your account is verified, you can insert a form into your site by using a **widget**, **shortcode**, or **template tag** directly in your theme. See the FAQ section for more details.

That's it. You're ready to go!

== Frequently Asked Questions ==

= What is GoDaddy Email Marketing? =

GoDaddy Email Marketing is the easiest way to create, send, share, and track email newsletters online. It's for people who want email marketing to be simple.

= Do I need a GoDaddy Email Marketing account to use this plugin? =

Yes, this plugin requires a [GoDaddy Email Marketing](https://www.godaddy.com/online-marketing/email-marketing) account.

= Is there a widget? =

Absolutely. Use it by finding the GoDaddy Email Marketing widget under **Appearance > Widgets** in the WordPress Dashboard and dragging it into the widget area of your choice. You can then add a title and select a form!

= Is there a shortcode? =

Yes! You can add a form to any post or page by adding the shortcode with the form ID (e.g., `[gem id=123456 ]`) in the page/post editor.

= Is there a template tag? =

Yup! Add the following template tag into any WordPress theme template file: `<?php gem_form( $form_id ); ?>`. For example: `<?php gem_form( 123456 ); ?>` where `123456` is your form ID.

= Where can I find my form IDs? =

To find your form IDs, navigate to **Settings > GoDaddy Email Marketing** and select the **Forms** tab. If you've recently created new forms click the **Refresh Forms** button to pull them into your WordPress site.

= Where can I find the API Key? =

You can find your **Secret API Key** in the [Settings section](https://gem.godaddy.com/user/edit?set_api=&account_info_tabs=account_info_personal) of your GoDaddy Email Marketing account on the right hand side.

== Screenshots ==

1. Settings screen.
2. A full list of your GoDaddy Email Marketing Webforms, with ready shortcodes.
3. The widget, on the widgets page.
4. The widget, on the front-end.
5. GoDaddy Email Marketing widget block.
6. GoDaddy Email Marketing widget preview, in the block editor.
7. GoDaddy Email Marketing widget, on the block front-end.

== Changelog ==

= 1.4.2 =
* Fix: Update help tab iframe URL with www. @props [aaroncampbell](https://github.com/aaroncampbell)

= 1.4.1 =
* Fix: Update plugin bypassing cache when fetching customer forms
* Fix: Fix US help tab iframe URL
* Tweak: Update `SelectControl` label to `GoDaddy Email Marketing Form`
* Tweak: Update strings in Russian translation file. @props [beebeatle](https://github.com/beebeatle)

= 1.4.0 =
* New: Introduce GoDaddy Email Marketing content block.

= 1.3.0 =
* New: Add support for GDPR fields (Age consent, terms of service and tracking option)
* Fix: Update text domain to match plugin slug.
* Tweak: Update translation functions and regenerate translations.

= 1.2.1 =
* Fix: Switch `wp_nonce` to `_wpnonce`, fixing the ability to refresh GEM forms.

= 1.2.0 =
* New: Help tab on the Settings screen
* New: Dismissible admin notice after on-boarding

= 1.1.4 =
* Tweak: Indicate support for WordPress 4.7

= 1.1.3 =
* Fixed: CSRF - thanks to pluginvulnerabilities.com for reporting it

= 1.1.2 =
* Minor URL fix

= 1.1.1 =
* Minor improvements and bug fixes.

= 1.1.0 =
* UI & UX overhaul using tabbed navigation & enhanced admin notices.
* Added the `debug` setting to replace the `gem_debug` filter.
* Added Shortcake plugin integration for the `gem` shortcode.
* Language support for many new locales.

= 1.0.6 =
* Fixed shortcode display and localization bugs [#12](https://github.com/godaddy/wp-godaddy-email-marketing/pull/12)
* Localization updates

= 1.0.5 =
* Refresh branding

= 1.0.4 =
* Localization
* Code style improvements
* Unit tests

= 1.0.3 =
* Added support for web form fancy fields
* Made some styling changes to mobile view

= 1.0.2 =
* Fixed incorrectly loaded stylesheet
* Minor style improvements to front-end form output

= 1.0.1 =
* Move the "Powered by GoDaddy Link" below the submit button and link it up to the correct place

= 1.0 =
* Initial version. forked from the Mad Mimi Sign Up Forms WordPress Plugin: https://wordpress.org/plugins/mad-mimi-sign-up-forms/
