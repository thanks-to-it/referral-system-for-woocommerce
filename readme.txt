=== Referral System for WooCommerce ===
Contributors: karzin
Tags: referral,affiliate
Requires at least: 4.4
Tested up to: 4.9
Stable tag: 1.0.0
Requires PHP: 5.6.0
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Turn your WooCommerce Coupons into Referral codes.

== Description ==

Increase your sales by letting your customers promote your site with this Referral plugin.

== How it works? (A simple explanation) ==
* You will be able to set a default WooCommerce Coupon as a Referral Code

* A Referral Code is nothing more than a URL pointing to your site with a custom parameter

* Referrers will see the Referral Code visiting **My Account page**

* Once new users visit your site through this URL and fulfil the coupon requirements, the referrer responsible for the URL will be rewarded with money

== How it works for the Referrer? ==
* Once the regular user is converted to a Referrer, he can access his **My Account** page.

* There will be two new tabs there **(Referral Codes and Referrals)**.

* **Referral Codes** tab will show all the available Referral Codes he can use.
   * In order to use a Referral Code, all the Referrer needs to do is send it to some other user. This user will have to access this URL and makes a purchase fulfilling the coupon requirements.

   * Once this user purchases and the order is set to **Complete** it will generate a **Referral**

* **Referrals** tab will display all the referrals

* A referrer should fill up its payment details on **My Account > Account Details** page. After a Referral is considered reliable by the shop owner, the payment details can be used for the payment.


== How it works? (A complete explanation) ==
Shop owners will be able to turn a WooCommerce Coupon into a Referral Code, choosing what kind of Reward the Referrer will receive and the requirements for using the coupon. A Referral Code is nothing more than a URL pointing to your site with a custom parameter. Once new users visit your site through this URL and buy some product fulfilling the coupon requirements a new order will be generated and checked against some Fraud Detection methods. The results will be available on admin order page.

After order is complete, depending on the results and configuration, a new Referral will be created for the correspondent Referrer and it will be displayed on his **My Account** page. Along with other Referral info, there will be displayed an **Authenticity** status corresponding to **Fraud Detection** checking. If shop owner decide it's a reliable Referral, he can reward the Referrer with money.

Each Referral Code can be configured with its own reward and coupon requirements.

To become a Referrer, any user can simply check the **"Become a Referrer"** option that will be present on Registration form or on **My Account > Account Details** tab

Please read the FAQ if you have more questions.

== Premium Version ==
* More Reward Types
   * Reward by order percentage

* More Fraud Detection Methods
   * Check Referrer and Customer IP
   * Save Cookie on Referrer Browser
   * Check Referrer and Customer Cookie

* Better Referrals Report

* Support

== Frequently Asked Questions ==

= How to become a Referrer? =
To become a Referrer, any user can simply check the **"Become a Referrer"** option that will be present on Registration form or on **My Account > Account Details** tab

= How the Referrer will receive its money? =
A referrer should fill up its payment details on **My Account > Account Details** page.
The shop owner has to transfer the money to the Referrer manually using the payment details or any other methods.

= What is a Fraud Detection Method? =
It's a mechanism that will try to prevent Referral frauds.
The **Free version** of this plugin will have only one method that will check if the referrer and customer emails are the same.
There will be more Fraud Detection methods on **Premium Version**

= What is Referral Authenticity? =
It's only a label that will be automatically set to a Referral depending on Fraud Detection checking.

= How can I contribute? Is there a github repository? =
If you are interested in contributing - head over to the [Referral System for WooCommerce plugin GitHub Repository](https://github.com/thanks-to-it/referral-system-for-woocommerce) to find out how you can pitch in.

== Installation ==

1. Upload the entire 'referral-system-for-woocommerce' folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Start by visiting plugin settings at WooCommerce > Settings > Referral.

== Screenshots ==

== Changelog ==

= 1.0.0 - 18/12/2018 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
* Initial Release.