===uProc for Wordpress===
Contributors: uProc LLC
Tags: email, validation, anti-spam, comments, contact form, contact form 7, email validation, grunion, jetpack, spam, woocommerce
Requires at least: 3.0.1
Donate Link: https://uproc.io/uproc_for_wordpress
Tested up to: 5.3.0
Stable tag: 1.0.8
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

With the uProc for Wordpress plugin you can easily verify if an email address really exists and is valid.
Additionally, you can check all existing tools (more than 400) at [https://uproc.io](https://uproc.io).

== Description ==

This plugin uses the uProc for Wordpress API [https://uproc.io](https://uproc.io) to check if an email address really exists or not.

The plugin integrates with the is_email() function of WordPress. It works seamlessly with:

* Contact Form 7
* Jetpack/Grunion
* WordPress registration forms
* and with any other form which uses the is_email() function (no changes required).

The plugin can also be integrated into 3rd party forms that do not use the is_email() function.

= How does it work? =

The uProc API real-time validation process includes all of the following tests:

* Syntax verification (IETF/RFC standard conformance)
* DNS validation, including MX record lookup
* Disposable email address (DEA) detection
* Misspelled domain detection to prevent Typosquatting
* Freemail address detection
* SMTP connection and availability checking
* Temporary unavailability detection
* Mailbox existence checking
* Catch-All testing
* Greylisting detection
* Block free domain emails (like Gmail, Hotmail, Yahoo, ...)
* Avoid email validations on Woocommerce orders

The plugin requires an API Key (free, no credit card required):
[https://app.uproc.io/#/signup](https://app.uproc.io/#/signup)

We do not send any email to the recipient address during the entire validation process. All processing is done on our servers, your IP addresses and domains are not affected in any way by the Email-Validator service - absolutely no blacklisting risk for your IPs and domains.

Data Protection and Privacy Policy: see the Frequently Asked Questions section.

== Frequently Asked Questions ==

= Data Protection and Privacy Policy =

We are committed to protect your data and your privacy when processing personal data.
We do not process or store email addresses for any purpose other than validation.
We do not share any client data with any third party at any time.
We keep all data supplied by clients for the purposes of validation as confidential, to not disclose data to anyone within our organization without a need to receive it for the specific purpose for which its is being disclosed to us, and to not disclose this data to any third party.

= Do you store validated email addresses? =

All data from API requests and batch/list processing will be automatically deleted within 30 days after the data has been processed.

== Installation ==

1. Unzip and upload the plugin to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the plugin in the Settings section of the Administrator Dashboard.
4. Register for an API key (some credits free and no credit card is required) at:
[https://app.uproc.io/app/login](https://app.uproc.io/app/login)
5. Copy and paste your API key to the Settings section of the Administrator Dashboard.

== Screenshots ==

1. Plugin admin page.
2. Free email form detection.
3. Wordpress install page.

== Changelog ==

= 1.0.8 =
* Fixed some bugs
* Checked with last major versions

= 1.0.7 =
* Fixed error on caching

= 1.0.5 =
* Fixed styles

= 1.0.4 =
* Add support for "Accept free emails"
* Add support for "Check on orders"

= 1.0.3 =
* Change code organization

= 1.0.2 =
* Fix some errors on ajax submit

= 1.0.1 =
* Fix new auth method

= 1.0.0 =
* Initial release

== What is uProc ==

uProc is a SaaS platform where you can check or enrich any available field in your database. Please, check our catalog at
[https://app.uproc.io/#/tools](https://app.uproc.io/#/tools)
to find the right tool for you.

Next fields can be processed with our catalog:
* Audio: speeches
* Communication: Email, Mobile, Phone
* Company: Cif, Name, Employee
* Finance: Credit card, Account, Currency
* Geographical: Country, City, Coordinates
* Image: Screenshot, EXIF
* Internet: Domain, IP, URI
* Personal: Name, Surname, Gender
* Product: EAN, UPC, ASIN, GTIN, ISBN
* Security: Password, MD5, Luhn
* Text: Uppercase, Lowercase, Lists
