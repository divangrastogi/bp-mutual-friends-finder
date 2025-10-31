<?php
/**
 * Tooltip template for mutual friends
 *
 * Available variables:
 * @var int   $count   Total mutual friends count
 * @var array $friends Array of friend objects
 * @var int   $user_id Target user ID
 *
 * @package BP_Mutual_Friends_Finder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="bpmff-tooltip" role="tooltip" aria-live="polite">
	<?php if ( $count > 0 ) : ?>
		<div class="bpmff-mutual-list">
			<div class="bpmff-avatars">
				<?php foreach ( $friends as $friend ) : ?>
					<a href="<?php echo esc_url( $friend['link'] ); ?>" 
					   class="bpmff-avatar" 
					   title="<?php echo esc_attr( $friend['name'] ); ?>">
						<img src="<?php echo esc_url( $friend['avatar'] ); ?>" 
						     alt="<?php echo esc_attr( $friend['name'] ); ?>"
						     loading="lazy">
					</a>
				<?php endforeach; ?>
			</div>
			
			<div class="bpmff-count">
				<?php
				printf(
					esc_html( _n(
						'%s mutual friend',
						'%s mutual friends',
						$count,
						'bp-mutual-friends-finder'
					) ),
					'<strong>' . number_format_i18n( $count ) . '</strong>'
				);
				?>
			</div>
		</div>
		
		<?php if ( $count > count( $friends ) ) : ?>
			<a href="#" 
			   class="bpmff-view-all" 
			   data-user-id="<?php echo esc_attr( $user_id ); ?>">
				<?php esc_html_e( 'View all', 'bp-mutual-friends-finder' ); ?>
			</a>
		<?php endif; ?>
	<?php endif; ?>
</div>
