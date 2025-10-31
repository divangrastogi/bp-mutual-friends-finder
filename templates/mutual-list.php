<?php
/**
 * Mutual friends list template
 *
 * @package BP_Mutual_Friends_Finder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<ul class="bpmff-friends-list">
	<?php foreach ( $friends as $friend ) : ?>
		<li class="bpmff-friend-item">
			<a href="<?php echo esc_url( $friend['link'] ); ?>" class="bpmff-friend-link">
				<img src="<?php echo esc_url( $friend['avatar'] ); ?>" 
				     alt="<?php echo esc_attr( $friend['name'] ); ?>"
				     class="bpmff-friend-avatar"
				     loading="lazy">
				<span class="bpmff-friend-name"><?php echo esc_html( $friend['name'] ); ?></span>
			</a>
			
			<?php if ( isset( $friend['meta'] ) && ! empty( $friend['meta'] ) ) : ?>
				<span class="bpmff-friend-meta">
					<?php echo esc_html( $friend['meta'] ); ?>
				</span>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
