=== Metrilo - WooCommerce Growth Platform ===
Contributors: MurryIvanoff
Tags: woocommerce, analytics, reporting, tracking, woo, ecommerce, funnels, metrics, kissmetrics, mixpanel, crm, history, products, items, rjmetrics, analytics dashboard, google analytics, products, retention, coupons, customers, big data, customer relationship management, subscriptions, woocommerce subscriptions, churn, customer analytics, insights, ltv, email marketing, email, triggered emails, cohorts, sales analytics, customer intelligence, email marketing, automation, cart abandonment, cart recovery
Requires at least: 2.9.2
Tested up to: 5.2.3
Stable Tag: 1.7.15
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Ecommerce Analytics and behaviour-driven customer engagement tools for ecommerce brands.

== Description ==

Metrilo is the growth marketing platform for modern consumer brands.
Metrilo’s analytics measure your business performance and give you insights to make strategic decisions. The engagement tools build on top of the vast data made accessible and enable you to bond with your customers long-term.

Metrilo helps your brand grow by making your data actionable and increasing customer retention.

= Optimize your WooCommerce store marketing =
Metrilo connects marketing and sales to show you how to maximize ROI

* Key sales metrics and marketing performance at a glance
* Comparison of marketing channels
* Real-time revenue reporting and many breakdowns
* Pre-set sales funnel tracking
* Product performance data to help you develop your brand

= Grow your WooCommerce store thanks to existing customers =
Metrilo unlocks opportunities for growth in your customer data

* Comprehensive customer profiles so you truly understand your buyers
* Manage your entire customer database with tags and segments
* Improve the buying experience with customer journey tracking
* Find out what stimulates repeat orders in your business

= WOOCOMMERCE EMAIL MARKETING =
Metrilo makes it easy to engage your customer base for higher retention
* Integrated data and email for building relationships with customers
* Customer lifecycle monitoring and loyalty analysis
* Segmentations and emails for personalized communication
* Tailored engagement on autopilot
* Automated feedback gathering
* Recover lost sales

The official Metrilo plugin connects your WooCommerce store with the Metrilo platform in less than 5 minutes. It is a self-standing platform that tracks data in real time and reports are accurate to the minute without slowing down your website.

You can start growing your brand today - your insights will be available immediately as soon as you start your trial at [metrilo.com](https://www.metrilo.com/signup?ref=wpplugin&plan=premium&skip=true).
[Sign up now for free](https://www.metrilo.com/signup?ref=wpplugin&plan=premium&skip=true) and get all-inclusive 14-day trial.

We provide Ask-Us-Anything support. You are more than welcome to get in touch with us anytime using the live chat on our website.


== Installation ==

Once you install the plugin, follow these steps:

1. Activate the plugin.
2. Go to Woocommerce's Settings page and click the Integrations tab.
3. Enter your unique Metrilo API token and click "Save settings". If you don't have API token, [sign up for free now](https://www.metrilo.com/signup?ref=wpplugin&plan=premium&skip=true) and get one!
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

= 1.7.15 =
* Fixed: Decode html entities before syncing data with Metrilo.

= 1.7.14 =
* Fixed: Last name parameter taking values for First name when syncing data with Metrilo.

= 1.7.13 =
* New: Additional functionality to handle phone orders.

= 1.7.12 =
* Fixed: extracting image url instead of GUID when syncing with Metrilo.

= 1.7.11 =
* Fixed: applied_coupon event not syncing with Metrilo.

= 1.7.10 =
* Excluding orders without email from sync with Metrilo.

= 1.7.9 =
* Fixed: Send remove from card event to Metrilo on every interaction with the user's cart.
* Fixed: Subtract refunds from order amount, when syncing with Metrilo.

= 1.7.8 =
* Fixed: Exception when using WooCommerce before version 3.

= 1.7.7 =
* Fixed: Sending order events with deleted products.

= 1.7.6 =
* Send SKU information for parent products and variations.
* Check integration credentials on save.

= 1.7.5 =
* Using wp_get_product instead of WC_Product_Variation (thanks to Tung Quach for the suggestion)

= 1.7.4 =
* Compatible with WP 4.9.1

= 1.7.3 =
* Fixed: WooCommerce subscriptions filter glitch

= 1.7.1 =
* Fixed: Fix for version 1.7.0 that would cause issues on versions of PHP older than 5.5

= 1.7.0 =
* Optimized: Now using direct methods in all cases to avoid generating Notices for WooCommerce 3.x

= 1.6.3 =
* Optimized: jQuery-related operations moved to wp_footer to avoid issues on WooCommerce setups where jQuery is not loaded at the beginning

= 1.6.2 =
* New: You can now prefix all your order IDs (for multi-shop setups)

= 1.6.1 =
* Improved: Not using methods marked as deprecated in WooCommerce 3.x
* New: Being able to send a specific tag with every customer (for multi-shop setups)

= 1.6.0 =
* Fixed: Now works with WooCommerce 3.0

= 1.5.1 =
* Improved: Order sessions tracking is now more accurate

= 1.5.0 =
* New: Added possibility for Metrilo to sync orders with your WooCommerce store that were not synced due to technical reasons

= 1.4.6 =
* Option: You can now choose if your backend WooCommerce calls should be sent to Metrilo through HTTP or HTTPS

= 1.4.5 =
* Bug fix: Identify calls can now also be ignored when sending to Metrilo
* Improved: Send status change debugging data to Metrilo to help fixing status issues

= 1.4.4 =
* Improved: Order status change tracking improvement

= 1.4.3 =
* New: You can now choose which tracking events to not be sent to Metrilo

= 1.4.2 =
* New: You can now send your user's roles as tags to Metrilo

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
