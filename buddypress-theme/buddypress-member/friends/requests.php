<div class="content-header">
</div>

<div id="content">
	<div class="pagination-links" id="pag">
		<?php bp_friend_pagination() ?>
	</div>
	
	<h2><?php _e( 'Friendship Requests', 'buddypress' ); ?></h2>
	<?php do_action( 'template_notices' ) // (error/success feedback) ?>
	
	<?php if ( bp_has_friendships() ) : ?>
		<ul id="friend-list">
		<?php while ( bp_user_friendships() ) : bp_the_friendship(); ?>
			<li>
				<?php bp_friend_avatar_thumb() ?>
				<h4><?php bp_friend_link() ?></h4>
				<span class="activity"><?php bp_friend_time_since_requested() ?></span>
				<div class="action">
					<a href="<?php bp_friend_accept_request_link() ?>" id="accept"><?php _e( 'Accept', 'buddypress' ); ?></a> 
					<a href="<?php bp_friend_reject_request_link() ?>" id="reject"><?php _e( 'Reject', 'buddypress' ); ?></a> 
				</div>
			</li>
		<?php endwhile; ?>
		</ul>
	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'You have no pending friendship requests.', 'buddypress' ); ?></p>
		</div>

	<?php endif;?>
</div>