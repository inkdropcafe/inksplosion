<!-- GRAWLIX TEMPLATE: This comes from snippet.follow -->
					<div id="follow">
						<h3>Follow me</h3>
						<a class="rss" title="Follow RSS" href="<?=show('rss')?>">RSS</a>
<?php
$info = $this->services['follow']; // Get info from database via page class
if ( $info['deviantart'] ) : ?>
						<a class="deviantart" title="Follow me on deviantART" href="http://<?=$info['deviantart'] ?>.deviantart.com"></a>
<?php endif;
if ( $info['facebook'] ) : ?>
						<a class="facebook" title="Follow me on Facebook" href="https://www.facebook.com/<?=$info['facebook'] ?>"></a>
<?php endif;
if ( $info['googleplus'] ) : ?>
						<a class="googleplus" title="Follow me on Google Plus" href="https://plus.google.com/+<?=$info['googleplus'] ?>"></a>
<?php endif;
if ( $info['instagram'] ) : ?>
						<a class="instagram" title="Follow me on Instagram" href="http://instagram.com/<?=$info['instagram'] ?>"></a>
<?php endif;
if ( $info['linkedin'] ) : ?>
						<a class="linkedin" title="Follow me on LinkedIn" href="http://www.linkedin.com/in/<?=$info['linkedin'] ?>"></a>
<?php endif;
if ( $info['pinterest'] ) : ?>
						<a class="pinterest" title="Follow me on Pinterest" href="http://www.pinterest.com/<?=$info['pinterest'] ?>/"></a>
<?php endif;
if ( $info['tumblr'] ) : ?>
						<a class="tumblr" title="Follow me on Tumblr" href="http://<?=$info['tumblr'] ?>.tumblr.com/"></a>
<?php endif;
if ( $info['twitter'] ) : ?>
						<a class="twitter" title="Follow me on Twitter" href="https://twitter.com/<?=$info['twitter'] ?>"></a>
<?php endif; ?>
					</div>
