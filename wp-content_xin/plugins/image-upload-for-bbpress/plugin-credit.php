<?php
/**
 * Author: Aspen Grove Studios
 * License: GNU General Public License version 3 or later
 */
 ?>
<div style="background-color: #adb67e; max-width: 500px; text-align: center; padding: 20px; color: #fff;">
    <a href="https://aspengrovestudios.com/?utm_source=<?php echo($potent_slug); ?>&amp;utm_medium=link&amp;utm_campaign=wp-plugin-credit-link" target="_blank">
        <img src="<?php echo(plugins_url('images/ags-logo.png', __FILE__)); ?>" alt="Aspen Grove Studios" style="max-width: 100%; margin-bottom: 20px;" />
    </a>
    <div style="margin-bottom: 10px; font-size: 1.2em;">
        <strong>If you like our free plugin, please:</strong>
    </div>
    <div style="margin-bottom: 10px;">
        <a href="https://wordpress.org/support/view/plugin-reviews/<?php echo($potent_slug); ?>" target="_blank" class="button-secondary">Write a Review</a>
    </div>
    <div style="margin-bottom: 10px;">
        <a href="https://www.facebook.com/aspengrovestudios" target="_blank" class="button-secondary">Like us on Facebook</a>
        <a href="https://www.youtube.com/channel/UCdlXMjnBCYOTcd4XZ-LbihQ" target="_blank" class="button-secondary">Subscribe our YouTube Channel</a>
    </div>
</div>

<!-- FB -->
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.5";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<!-- Twitter -->
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>