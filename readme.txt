=== WooCommerce Marketing Platform & CRM by Metrilo ===
Contributors: MurryIvanoff
Tags: woocommerce, analytics, reporting, tracking, woo, ecommerce, funnels, metrics, kissmetrics, mixpanel, crm, history, products, items, rjmetrics, analytics dashboard, google analytics, products, retention, coupons, customers, big data, customer relationship management, subscriptions, woocommerce subscriptions, churn, customer analytics, insights, ltv, email marketing, email, triggered emails, cohorts, sales analytics, customer intelligence, email marketing
Requires at least: 2.9.2
Tested up to: 4.6.1
Stable Tag: 1.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Instant WooCommerce analytics, powerful CRM and personalized email marketing all in one place

== Description ==

Metrilo is powerful Customer Analytics, CRM and Email Marketing product that turns your WooCommerce data into insights to grow your business. Metrilo helps you know everything about your customers from their first touch with your WooCommerce store to their latest purchase

= WooCommerce CRM =
Meet the people behind your orders

* Track everything visitors do - from first visit to latest order
* Detailed customer profiles
* Product interactions
* Cart Abandonment
* Customer segmentation based on behavior
* Custom tags and notes
* One-on-one marketing emails
* Live view

= WooCommerce Email Marketing =
Powerful personalization engages better, fights cart abandonment and drives repeat sales.

* Personalized e-mail marketing
* Responsive emails composer
* Powerful reporting on email performance
* Custom domains + your branding only
* E-mail automation (coming soon)

= WooCommerce Analytics =
The full overview of your eCommerce business

* In-depth revenue analysis
* Acquisition sources report
* Preset Sales Funnels
* Order statuses updated in real time (and accounted for in revenue)
* Products performance
* Retention Analysis
* CLV, AOV, Conversion Rates, and more metrics


This plugin lets you connect your WooCommerce store with Metrilo in less than 5 minutes. All tracking is real-time and reports are accurate to the minute.

Metrilo is an all-in-one marketing product and runs on a self-standing platform so your website speed is not affected. You can create your account at [metrilo.com](https://www.metrilo.com/signup?ref=wpplugin)

We provide Ask-Us-Anything support. You are more than welcome to get in touch with us anytime using the live-chat on our website.

[Sign up now for free](https://www.metrilo.com/signup?ref=wpplugin) and get all-inclusive 14-day trial.

Metrilo gets better every single day. We're excited to have you onboard!


== Installation ==

Once you install the plugin, follow these steps:

1. Activate the plugin.
2. Go to Woocommerce's Settings page and click the Integrations tab.
3. Enter your unique Metrilo API token and click "Save settings". If you don't have API token, [sign up for free now](https://www.metrilo.com/signup?ref=wpplugin) and get one!
4. That's it. Enjoy a cup of coffee :-)

== Frequently Asked Questions ==

= How does Metrilo actually work? =

As soon as you install the plug-in, your data goes to our servers and Metrilo instantly starts processing. Every report / analysis or customer profile you see is generated in real time. You can access your account at [metrilo.com](https://www.metrilo.com/?ref=wpplugin)

= Will it slow down my site? =

Not at all. Metrilo’s JavaScript tracking library loads asynchronously after each page has already been loaded. All API requests are sent in the background using our fast CDN-backed infrastructure that connects to the nearest node around the globe.

= Is my data secure? =

For our website, we use SSL protection that ensures secure login and billing payments. Goes without saying, we value you and understand your data needs protection – we make sure it’s safe with us and don’t share it with any third parties.

= Is Metrilo real-time? =

Metrilo is almost real-time. Some features require data aggregation that can take up to a few minutes before it shows up in your Metrilo account.

= Is Metrilo real-time? =

Absolutely! We offer a 14-day free trial on every plan. No obligation. No credit card required. Get started for free now and get to know your WooCommerce customers!



== Screenshots ==

1. Overview of your WooCommerce store's performance
2. Easily filter a segment of your WooCommerce customer base
3. Customer profiles with all their cross-device activities, orders and product interactions
4. Analyze your revenue. Find growth opportunities!
5. Easily build and send personalized responsive email campaigns

== Changelog ==

= 1.4.1 =
* Faster: Tracking add_to_cart events now uses it's own endpoint to clear it's cookie and doesn't use admin-ajax.php anymore.

= 1.4.0 =
* New: You can now choose a product attribute to be sent as brand to Metrilo (beta)

= 1.3.9 =
* Works with WordPress 4.6.1

= 1.3.8 =
* Improved: Integrate with Aelia Currency Switcher plugin

= 1.3.7 =
* Updated: Now officially supporting WordPress 4.5.3

= 1.3.6 =
* Optimized: Also send server time when sending API calls to improve reliability of Metrilo order status tracking

= 1.3.5 =
* Optimized: Batch API calls are moved to a better endpoint

= 1.3.4 =
* Optimized: Importing historical orders

= 1.3.3 =
* Fixed: Fixed an issue with PHP Magic quotes

= 1.3.2 =
* Fixed: Fixed an issue with PHP versions older than 5.3

= 1.3.1 =
* Improved: Send orders to Metrilo regardless if the visitor goes to the Thank You page

= 1.3.0 =
* New: Metrilo is now also syncing customer contact data, including phone number

= 1.2.3 =
* Optimized: Tracking accuracy improved for slow order checkout flows

= 1.2.2 =
* Optimized: Optimized orders sync - now using batch API calls to send orders to Metrilo

= 1.2.1 =
* Optimized: When sending order events from session queue, make sure the order status us up-to-date (thanks, Ela!)

= 1.2.0 =
* Optimized: Syncing orders without billing data by using the WP user data

= 1.1.9 =
* Optimized: Syncing orders to Metrilo

= 1.1.8 =
* Improved: Syncing orders to Metrilo now takes less time

= 1.1.7 =
* Fixed: Now supporting WooCommerce 2.1 order statuses

= 1.1.6 =
* Improved: You can now select WP user roles from which Metrilo will ignore sending tracking data

= 1.1.2 =
* Improved: Send customer order location to Metrilo when syncing orders

= 1.1.0 =
* Fixed: issue with non-GMT WooCommerce installations. Thank you for reporting it, Kevin and Gregg!

= 1.0.3 =
* This plugin now keeps your order statuses in sync between WooCommerce and Metrilo

= 1.0.2 =
* Onboarding changes
* Importing WooCommerce orders and customers to Metrilo is now out of beta.

= 0.99 =
* New: You can now import your historical orders and customers to Metrilo (still in beta)
* Optimized: Now optimized for importing thousands of orders

= 0.98 =
* New: You can now import your historical orders and customers to Metrilo. Please note that this feature is still in beta.
* A few small bug fixes.

= 0.96 =
* New: Metrilo now tracks WooCommerce Subscriptions orders automatically to help you measure your retention and churn
* Many small Bug fixes. Thank you for reporting, Paolo and and Luciano!

= 0.92 =
* Bug fix with WooCommerce session integration

= 0.91 =
* Now tracking coupon usage to analyze retention based on coupons applied to orders

= 0.89 =
* Improved reliability of orders tracking

= 0.86 =
* Bug fixes

= 0.81 =
* Backwards compitability with 2.0x versions. Stability improvements.

= 0.71 =
* Metrilo now works as WooCommerce integration. Improved session management using WooCommerce's Session Handler.

= 0.69 =
* Bug fixes and optimizations

= 0.6 =
* Applying coupons to orders is now tracked

= 0.5 =
* First stable version.
