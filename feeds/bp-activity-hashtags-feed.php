<?php
header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
header('Status: 200 OK');
?>
<?php echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	<?php do_action('etivite_bp_activity_hashtags_feed'); ?>
>

<channel>
	<title><?php echo bp_site_name() ?> | <?php echo htmlspecialchars( $bp->action_variables[0] ); ?> | <?php _e( 'Hashtag', 'bp-hashtags' ) ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php echo $link; ?></link>
	<description><?php  echo htmlspecialchars( $bp->action_variables[0] ); ?> - <?php _e( 'Hashtag', 'buddypress' ) ?></description>
	<generator>http://buddypress.org/?v=<?php echo BP_VERSION ?></generator>
	<language><?php echo get_option('rss_language'); ?></language>
	<?php do_action('etivite_bp_activity_hashtags_feed_head'); ?>
	<?php if ( bp_has_activities( 'max=50&display_comments=stream&search_terms=#'. $bp->action_variables[0] . '<' ) ) : ?>
		<?php while ( bp_activities() ) : bp_the_activity(); ?>
			<?php if ( etivite_bp_activity_hashtags_current_activity() == 0 ) : ?>
				<pubDate><?php echo mysql2date('D, d M Y H:i:s O', bp_get_activity_date_recorded(), false); ?></pubDate>
			<?php endif; ?>
			<item>
				<guid><?php bp_activity_thread_permalink() ?></guid>
				<title><![CDATA[<?php bp_activity_feed_item_title() ?>]]></title>
				<link><?php echo bp_activity_thread_permalink() ?></link>
				<pubDate><?php echo mysql2date('D, d M Y H:i:s O', bp_get_activity_feed_item_date(), false); ?></pubDate>

				<description>
					<![CDATA[
					<?php bp_activity_feed_item_description() ?>

					<?php if ( bp_activity_can_comment() ) : ?>
						<p><?php printf( __( 'Comments: %s', 'buddypress' ), bp_activity_get_comment_count() ); ?></p>
					<?php endif; ?>

					<?php if ( 'activity_comment' == bp_get_activity_action_name() ) : ?>
						<br /><strong><?php _e( 'In reply to', 'buddypress' ) ?></strong> -
						<?php bp_activity_parent_content() ?>
					<?php endif; ?>
					]]>
				</description>
				<?php do_action('etivite_bp_activity_hashtags_feed_item'); ?>
			</item>
		<?php endwhile; ?>

	<?php endif; ?>
</channel>
</rss>
