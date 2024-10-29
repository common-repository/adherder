=== AdHerder ===

Contributors: pbackx, trikro
Donate link: http://grasshopperherder.com/
Tags: plugin, widget, automatic, ad, manage
Requires at least: 3.0
Tested up to: 3.4.1
Stable tag: 1.3

== Description ==

AdHerder is an automated A/B advertisement testing platform. Create your ads and AdHerder will select the best ad for each individual user.

This means you can create any number of ads to display in a wordpress widget and AdHerder will perform the following functions:

1. Keep track of which ads are converting well so that you can turn off poorly performing ads.
2. Don't show the same user the same ad over and over. If they didn't click the 3rd time, they're not click the 5th.
3. Don't show an ad the user has already clicked on. Show them something new.

All behaviors are completely configurable.

AdHerder will automatically track clicks on links and can track just about any desired behavior. Please see the FAQ section for information on how to track Facebook likes, Twitter follows and Mailchimp signups.

== Installation ==

Install through the admin interface, or manually in wp-content/plugins/adherder

Once AdHerder is enabled, a new post type will be available, called "Ad".
Create at least one ad and add the AdHerder widget to your theme.

== Frequently Asked Questions ==

= I'm always seeing the same ad. What's happening? =

Most likely, you are using a caching plugin (for instance, W3 Total Cache). In that case,
enable Ajax in the AdHerder options screen.

= How does AdHerder select which ad to display? =

AdHerder tracks the user behavior through a cookie. Based on this data the
ad is selected as follows: An ad that has not been seen by the user has the
highest priority. An ad on which the user has already converted (clicked)
has the lowest priority. You can tweak this behavior in the settings screen.

= How does AdHerder track conversions? =

The plugin automatically tracks clicks on links with a small piece of JavaScript.
If you want to monitor different types of conversion, you need to manually
call the `adherder_track_conversion()` function with as argument the ID of the ad.

= Where do I find the Ad ID? =

To find the ID of the call you can open the reports page and check the table. 
The first column shows the id. Another option is to edit the call and look 
at the url: `/wp-admin/post.php?post=7&action=edit`. In this case, the id is 7.

= How do I track Twitter follows? =

Tracking Twitter conversions (this only tracks people who click on follow and weren't already following you).
Use the following code:

    <a href="<your twitter url>" class="twitter-follow-button" data-show-count="false">Follow me</a>
    <script type="text/javascript">
    jQuery.getScript("//platform.twitter.com/widgets.js", function() {
      twttr.events.bind('follow', function(event) {
        adherder_track_conversion(<ad-id);
      });
    });
    </script>

Don't forget to replace: 
* <your twitter url> with the correct url to your Twitter profile. If you are unsure, you can get it from this page http://twitter.com/about/resources/followbutton
* <ad-id> with the correct WordPress id of your ad (see above)

= How do I track Mailchimp signups? =

Mailchimp signup tracking (tracks every one who receives the signup configuratin mail, but can be changed to track any one who clicks on the submit button):

1. Again it's best to work in HTML mode when entering the data
2. In Mailchimp, create an embedded signup form: Lists > Choose your list > Signup embed form > choose your options, but do not disable JavaScript
3. Copy the code into a new call

    Note: by default the latest version of WordPress comes with jQuery in "no conflict" mode. This is not compatible with the Mailchimp signup form. To fix this, you need to replace every occurence of $ in the form with jQuery (capitalization is important)
4. Find the following text in the call: `function mce_success_cb(resp)` It is in the lower part of the signup code
5. A few lines lower you should see `if (resp.result=="success"){`
6. Just below this line add the tracking code:

        if (resp.result=="success") {
          adherder_track_conversion(<call-id>);
          ...

7. Save the changes

= How do I track Facebook likes? =

In order to track Facebook likes, you need to use the XFBML version of the like button. Use the following code when creating your ad:

    <div id="fb-root"></div>
    <fb:like send="false" layout="button_count" width="200" show_faces="true"></fb:like>
    <script type="text/javascript">
    jQuery.getScript('<facebook script url>', function() {
        FB.init({ status: true, cookie: true, xfbml: true });
        FB.Event.subscribe('edge.create', function(response) {
          adherder_track_conversion(<ad-id>);
        });
    });
    </script>

You may need to change the "fb:like" section to suite your preferences. The easiest way to get it right is to get the code from: http://developers.facebook.com/docs/reference/plugins/like/

= Can I force AdHerder to display a certain ad? For testing? =

Yes you can. It is possible to override the automatic selection of ads. 
Add a `adherder_ad` parameter to the request. For instance, show the add with id 10:

    http://yoursite/?adherder_ad=10

Want a nice button to do this more easily? Ask Tristan@grasshopperherder.com


== Changelog ==

= Version 1.3 =

* Added confidence & relevance calculations for selected items
* Now possible to customize the items that are in the report
* Simplified managment & reporting interface
* Bulk actions on ads (ctr-click to select multiple) 

= Version 1.2 =

* More robust JavaScript
* Changed Twitter & Facebook help so it also works with Ajax version
* Added help on the new ad screen

= Version 1.1 =

* Minimal update to correctly reference authors

= Version 1.0 =

* Initial version.
