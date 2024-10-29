<div id="adherder-help">
<script>
	jQuery(document).ready(function() {
		jQuery('#adherder-help h4').each(function() {
			var tis = jQuery(this), answer = tis.next('div').slideUp();
			tis.click(function() {
				answer.toggle();
			});
		});
	});
</script>

<h3>Frequently Asked Questions</h3>

<h4>I'm always seeing the same ad. What's happening?</h4>

<div>
<p>Most likely, you are using a caching plugin (for instance, W3 Total Cache). In that case,
enable Ajax in the AdHerder options screen.</p>
</div>

<h4>How does AdHerder select which ad to display?</h4>

<div>
<p>AdHerder tracks the user behavior through a cookie. Based on this data the
ad is selected as follows: An ad that has not been seen by the user has the
highest priority. An ad on which the user has already converted (clicked)
has the lowest priority. You can tweak this behavior in the settings screen.</p>
</div>

<h4>How does AdHerder track conversions?</h4>

<div>
<p>The plugin automatically tracks clicks on links with a small piece of JavaScript.
If you want to monitor different types of conversion, you need to manually
call the <code>adherder_track_conversion()</code> function with as argument the ID of the ad.</p>
</div>

<h4>Where do I find the Ad ID?</h4>

<div>
<p>To find the ID of the call you can open the reports page and check the table. 
The first column shows the id. Another option is to edit the call and look 
at the url: <code>/wp-admin/post.php?post=7&amp;action=edit</code>. In this case, the id is 7.</p>
</div>

<h4>How do I track Twitter follows?</h4>

<div>
<p>Tracking Twitter conversions (this only tracks people who click on follow and weren't already following you).
Use the following code:</p>

<pre><code>&lt;a href="&lt;your twitter url&gt;" class="twitter-follow-button" data-show-count="false"&gt;Follow me&lt;/a&gt;
&lt;script type="text/javascript"&gt;
jQuery.getScript("//platform.twitter.com/widgets.js", function() {
  twttr.events.bind('follow', function(event) {
    adherder_track_conversion(&lt;ad-id);
  });
});
&lt;/script&gt;
</code></pre>

<p>Don't forget to replace: 
* <your twitter url> with the correct url to your Twitter profile. If you are unsure, you can get it from this page http://twitter.com/about/resources/followbutton
* <ad-id> with the correct WordPress id of your ad (see above)</p>
</div>

<h4>How do I track Mailchimp signups?</h4>

<div>
<p>Mailchimp signup tracking (tracks every one who receives the signup configuratin mail, but can be changed to track any one who clicks on the submit button):</p>

<ol>
<li>Again it's best to work in HTML mode when entering the data</li>
<li>In Mailchimp, create an embedded signup form: Lists > Choose your list > Signup embed form > choose your options, but do not disable JavaScript</li>
<li><p>Copy the code into a new call</p>

<p>Note: by default the latest version of WordPress comes with jQuery in "no conflict" mode. This is not compatible with the Mailchimp signup form. To fix this, you need to replace every occurence of $ in the form with jQuery (capitalization is important)</p></li>
<li>Find the following text in the call: <code>function mce_success_cb(resp)</code> It is in the lower part of the signup code</li>
<li>A few lines lower you should see <code>if (resp.result=="success"){</code></li>
<li><p>Just below this line add the tracking code:</p>

<pre><code>if (resp.result=="success") {
  adherder_track_conversion(&lt;call-id&gt;);
  ...
</code></pre></li>
<li><p>Save the changes</p></li>
</ol>
</div>

<h4>How do I track Facebook likes?</h4>

<div>
<p>In order to track Facebook likes, you need to use the XFBML version of the like button. Use the following code when creating your ad:</p>

<pre><code>&lt;div id="fb-root"&gt;&lt;/div&gt;
&lt;fb:like send="false" layout="button_count" width="200" show_faces="true"&gt;&lt;/fb:like&gt;
&lt;script type="text/javascript"&gt;
jQuery.getScript('&lt;facebook script url&gt;', function() {
    FB.init({ status: true, cookie: true, xfbml: true });
    FB.Event.subscribe('edge.create', function(response) {
      adherder_track_conversion(&lt;ad-id&gt;);
    });
});
&lt;/script&gt;
</code></pre>

<p>You may need to change the "fb:like" section to suite your preferences. The easiest way to get it right is to get the code from: http://developers.facebook.com/docs/reference/plugins/like/</p>
</div>

<h4>Can I force AdHerder to display a certain ad? For testing?</h4>

<div>
<p>Yes you can. It is possible to override the automatic selection of ads. 
Add a <code>adherder_ad</code> parameter to the request. For instance, show the add with id 10:</p>

<pre><code>http://yoursite/?adherder_ad=10
</code></pre>

<p>Want a nice button to do this more easily? Ask Tristan@grasshopperherder.com</p>
</div>
</div>
