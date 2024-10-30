<?php
/**
 * Plugin Name: Better Scheduled Posts
 * Description: Improves the management of your scheduled Posts by making them visible on the front end to administrators/contributors, adding them to the internal link box and enabling you to push them back by any number of days.
 * Author: Carlo Manf
 * Author URI: http://carlomanf.id.au
 * Version: 2.0.0
 */

// Show scheduled posts to administrators/contributors
add_filter( 'pre_get_posts', 'bsp_show_scheduled_posts' );
function bsp_show_scheduled_posts( $query ) {

	if ( current_user_can( 'edit_posts' ) && !$query->is_singular() && !is_admin() ) {
		$statuses = $query->get( 'post_status' );

		if ( !$statuses )
			$statuses = 'publish';

		if ( is_string( $statuses ) )
			$statuses = explode( ',', $statuses );

		if ( !in_array( 'future', $statuses ) ) {
			$statuses[] = 'future';
			$query->set( 'post_status', $statuses );
		}
	}

	return $query;
}

// Validate data before rescheduling posts
function bsp_validate_post_data() {
	$valid = true;
	$date = explode( '-', $_POST[ 'start_date' ] );

	if ( 3 !== count( $date ) || !checkdate( $date[ 1 ], $date[ 2 ], $date[ 0 ] ) ) {
		echo '<div class="error"><p>Invalid start date.</p></div>';
		$valid = false;
	}

	if ( !intval( $_POST[ 'no_of_days' ] ) ) {
		echo '<div class="error"><p>Invalid number of days.</p></div>';
		$valid = false;
	}

	return $valid;
}

// Reschedule posts
function bsp_push_posts() {

	$scheduled_posts = get_posts( array( 'post_status' => 'future', 'posts_per_page' => -1 ) );
	$success = 0;

	foreach ( $scheduled_posts as $post ) {
		$date = strtotime( $post->post_date );
		$date_gmt = strtotime( $post->post_date_gmt );
		$start_date = strtotime( $_POST[ 'start_date' ] );

		if ( $date_gmt < $start_date )
			continue;

		$updated_post[ 'ID' ] = $post->ID;
		$updated_post[ 'post_date' ] = date( 'Y-m-d H:i:s', $date + ( (int) $_POST[ 'no_of_days' ] * 86400 ) );
		$updated_post[ 'post_date_gmt' ] = date( 'Y-m-d H:i:s', $date_gmt + ( (int) $_POST[ 'no_of_days' ] * 86400 ) );

		wp_update_post( $updated_post );

		$success++;
	}

	if ( $success )
		echo '<div class="updated"><p>Successfully rescheduled ' . $success . ' post(s)!</p></div>';
	else
		echo '<div class="updated"><p>No changes were made because no scheduled posts were found.</p></div>';

}

// Tools page
function bsp_tools_page() {

	if ( !empty( $_POST[ 'push' ] ) )
		if ( bsp_validate_post_data() )
			bsp_push_posts();

	?><div class="wrap">
		<h2>Better Scheduled Posts</h2>
		<p>Use this tool to push back your scheduled posts.</p>
		<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
			<table class="form-table">
				<tr>
					<th scope="row"><label for="start_date">Start Date</label></th>
					<td>
						<input type="text" id="start_date" name="start_date" value=""><p class="description">Format: yyyy-mm-dd</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="no_of_days">Number of Days</label></th>
					<td><input type="text" id="no_of_days" name="no_of_days" value=""></td>
				</tr>
			</table>
			<p class="submit"><input type="submit" name="push" class="button button-primary" value="Reschedule Posts"></p>
		</form>
	</div><?php

}

// Add menu page
add_action( 'admin_menu', 'bsp_add_menu_page' );
function bsp_add_menu_page() {
	add_submenu_page( 'tools.php', 'Better Scheduled Posts', 'Scheduled Posts', 'edit_others_posts', 'bsp', 'bsp_tools_page' );
}

// Clean permalinks for scheduled posts
add_filter( 'post_link', 'bsp_clean_permalinks', 10, 3 );
function bsp_clean_permalinks( $permalink, $post, $leavename ) {

	if ( 'future' === $post->post_status ) {
		$temp_post = clone $post;
		$temp_post->post_status = 'publish';
		$permalink = get_permalink( $temp_post );
	}

	return $permalink;
}

// Add scheduled posts to link box
add_action( 'pre_get_posts', 'bsp_link_box' );
function bsp_link_box( $query ) {

	// Ensure request for internal linking
	if ( ! isset( $_POST ) || ! isset( $_POST[ 'action' ] ) || 'wp-link-ajax' !== $_POST[ 'action' ] )
		return;

	// Add future posts to the query
	$post_status = (array) $query->query_vars[ 'post_status' ];
	if ( !in_array( 'future', $post_status ) )
		$post_status[] = 'future';

	$query->set( 'post_status', $post_status );
}

// Sponsored
if ( !function_exists( 'carlomanf_sponsored' ) ) {
	add_action( 'admin_footer', 'carlomanf_sponsored', 0 );
	function carlomanf_sponsored() {
		$variations = array(
			array( 'Is your online business struggling?', 'We can help you.', '' ),
			array( 'Is your online business struggling?', 'We can help.', '' ),
			array( 'Is your online business struggling?', 'Click Here', ' class="button"' ),
			array( 'Is your online business struggling?', 'There\'s a better way.', '' ),
			array( 'Is your online business struggling?', 'There\'s a better way&hellip;', '' ),
			array( 'Is your WordPress business struggling?', 'We can help you.', '' ),
			array( 'Is your WordPress business struggling?', 'We can help.', '' ),
			array( 'Is your WordPress business struggling?', 'Click Here', ' class="button"' ),
			array( 'Is your WordPress business struggling?', 'There\'s a better way.', '' ),
			array( 'Is your WordPress business struggling?', 'There\'s a better way&hellip;', '' ),
			array( 'Struggling to build your online business with WordPress?', 'We can help you.', '' ),
			array( 'Struggling to build your online business with WordPress?', 'We can help.', '' ),
			array( 'Struggling to build your online business with WordPress?', 'Click Here', ' class="button"' ),
			array( 'Struggling to build your online business with WordPress?', 'There\'s a better way&hellip;', '' ),
			array( 'Why are you struggling to build your online business with WordPress', 'when there\'s a better way?', '' ),
			array( 'Fed up with comment spam, security holes and technical issues?', 'There\'s a better way.', '' ),
			array( 'Fed up with comment spam, security holes and technical issues?', 'There\'s a better way&hellip;', '' ),
			array( 'Fed up with comment spam, security holes and technical issues?', 'Click here for the solution.', '' ),
			array( 'Fed up with comment spam, security holes and technical issues?', 'Click here for the solution&hellip;', '' ),
			array( 'Why are you struggling with comment spam, security holes and technical issues', 'when there\'s a better way?', '' ),
			array( 'Fed up with writing pages and pages worth of content to appease Google?', 'There\'s a better way.', '' ),
			array( 'Fed up with writing pages and pages worth of content to appease Google?', 'There\'s a better way&hellip;', '' ),
			array( 'Fed up with writing pages and pages worth of content to appease Google?', 'Click here for the solution.', '' ),
			array( 'Fed up with writing pages and pages worth of content to appease Google?', 'Click here for the solution&hellip;', '' ),
			array( 'Why are you writing pages and pages worth of content to appease Google', 'when there\'s a better way?', '' ),
			array( 'Fed up with difficult CSS, PHP and SEO?', 'There\'s a better way.', '' ),
			array( 'Fed up with difficult CSS, PHP and SEO?', 'There\'s a better way&hellip;', '' ),
			array( 'Fed up with difficult CSS, PHP and SEO?', 'Click here for the solution.', '' ),
			array( 'Fed up with difficult CSS, PHP and SEO?', 'Click here for the solution&hellip;', '' ),
			array( 'Why are you struggling with difficult CSS, PHP and SEO', 'when there\'s a better way?', '' ),
			array( 'This course will change the way you think about online business forever.', 'Click here to learn more&hellip;', '' ),
			array( 'This course will change the way you think about online business forever.', 'Click here to find out more&hellip;', '' ),
			array( 'This course will change the way you think about online business forever.', 'Learn more&hellip;', '' ),
			array( 'This course will change the way you think about online business forever.', 'Learn more', ' class="button"' ),
			array( 'This course will change the way you think about online business forever.', 'Find out more&hellip;', '' ),
			array( 'This course will change the way you think about online business forever.', 'Find out more', ' class="button"' ),
			array( 'The secret to a successful online business? Getting the numbers on your side.', 'This course shows you how.', '' ),
			array( 'The secret to a successful online business?', 'Getting the numbers on your side.', '' ),
			array( 'The secret to a successful online business? Following a proven system.', 'This course shows you how.', '' ),
			array( 'The secret to a successful online business?', 'Following a proven system.', '' ),
			array( 'The secret to a successful online business? High-priced, high-margin products.', 'This course shows you how.', '' ),
			array( 'The secret to a successful online business?', 'High-priced, high-margin products.', '' ),
			array( 'The biggest reward of a successful online business?', 'Becoming a better version of yourself.', '' ),
			array( 'The biggest reward of a successful online business? Becoming a better version of yourself.', 'This course shows you how.', '' ),
			array( 'Students of this online business course have collectively earned nearly $40 million in revenue.', 'Become one of them.', '' ),
			array( 'Students of this online business course have collectively earned nearly $40 million in revenue.', 'Learn more&hellip;', '' ),
			array( 'Students of this online business course have collectively earned nearly $40 million in revenue.', 'Find out more&hellip;', '' ),
			array( 'Want to try our number one marketing system that pays you $1,250, $3,300 & $5,500?', 'Try Now', ' class="button"' ),
			array( 'Want to try our number one marketing system that pays you $1,250, $3,300 & $5,500?', 'Click Here', ' class="button"' ),
			array( 'Want to try our number one marketing system that pays you $1,250, $3,300 & $5,500?', 'Click here to try it now.', '' ),
			array( 'Want to try our number one marketing system that pays you $1,250, $3,300 & $5,500?', 'Click here to try it now&hellip;', '' ),
			array( 'Want to try our number one marketing system that pays you $1,250, $3,300 & $5,500?', 'Sure, I\'ll try it!', ' class="button"' ),
			array( 'You don\'t seriously think you\'ll get a flood of free traffic from Google do you?', 'Click here for a better alternative.', '' ),
			array( 'You don\'t seriously think you\'ll get a flood of free traffic from Google do you?', 'Click here for a better alternative&hellip;', '' ),
			array( 'You don\'t seriously think you\'ll get a flood of free traffic from Google do you?', 'Click here for the solution.', '' ),
			array( 'You don\'t seriously think you\'ll get a flood of free traffic from Google do you?', 'Click here for the solution&hellip;', '' ),
			array( 'You don\'t seriously think you\'ll get a flood of free traffic from Google do you?', 'There\'s a better way.', '' ),
			array( 'You don\'t seriously think you\'ll get a flood of free traffic from Google do you?', 'There\'s a better way&hellip;', '' ),
			array( 'Free traffic from Google is too risky and volatile.', 'Remove the risk from your online business.', '' ),
			array( 'Remove the risk from your online business.', 'This course shows you how.', '' ),
			array( 'Learn how to remove the risk from your online business.', 'Click Here', ' class="button"' ),
			array( 'Find out how to remove the risk from your online business.', 'Click Here', ' class="button"' ),
			array( 'Learn how to stop relying on Google and remove the risk from your online business.', 'Click Here', ' class="button"' ),
			array( 'Find out how to stop relying on Google and remove the risk from your online business.', 'Click Here', ' class="button"' ),
			array( 'Want to learn how to run an online business in ' . date( 'Y' ) . '?', 'Click Here', ' class="button"' ),
			array( 'Want to learn how to run a profitable online business in ' . date( 'Y' ) . '?', 'Click Here', ' class="button"' ),
			array( 'Want to learn how to run a successful online business in ' . date( 'Y' ) . '?', 'Click Here', ' class="button"' ),
			array( 'Want to learn how to run a real online business in ' . date( 'Y' ) . '?', 'Click Here', ' class="button"' ),
			array( 'Want to know the best way to run an online business in ' . date( 'Y' ) . '?', 'Click Here', ' class="button"' ),
			array( 'Want to know the right way to run an online business in ' . date( 'Y' ) . '?', 'Click Here', ' class="button"' ),
			array( 'Free Online Business Video', 'Watch Now', ' class="button"' ),
			array( 'Discover the secrets behind the system that has generated over $25 million in the last 12 months', 'Watch Now', ' class="button"' ),
			array( 'Free Video: How to Boost Your Retirement Funds with an Online Business', 'Watch Now', ' class="button"' ),
			array( 'Free Video Reveals 21 Steps to Earning Up to $10,500 Per Sale Online', 'Watch Now', ' class="button"' ),
			array( 'Get $1,250, $3,300 and $5,500 Commissions Deposited into Your Bank Account Without Ever Having to Pick Up the Phone!', 'Get Instant Access', ' class="button"' ),
			array( 'FREE VIDEO: We Are Willing To Bet $500 That You Will Succeed With This Proven System', 'Get Instant Access', ' class="button"' ),
			array( 'FREE VIDEO: We Are Willing To Bet $500 That You Will Succeed With This Proven System', 'Watch Now', ' class="button"' ),
			array( 'FREE VIDEO: We Are Willing To Bet $500 That You Will Succeed With This Proven System', 'Click Here', ' class="button"' ),
			array( 'FREE VIDEO: We Are Willing To Bet $500 That You Will Succeed With This Proven System', 'Try Now', ' class="button"' ),
			array( 'FREE VIDEO: We Are Willing To Bet $500 That You Will Succeed With This Proven System', 'Learn More', ' class="button"' ),
			array( 'FREE VIDEO: We Are Willing To Bet $500 That You Will Succeed With This Proven System', 'Learn More&hellip;', ' class="button"' ),
			array( 'FREE VIDEO: We Are Willing To Bet $500 That You Will Succeed With This Proven System', 'Find Out More', ' class="button"' ),
			array( 'FREE VIDEO: We Are Willing To Bet $500 That You Will Succeed With This Proven System', 'Find Out More&hellip;', ' class="button"' ),
		);
		$r = array_rand( $variations );
		echo '<div class="notice updated"><p><strong>' . $variations[ $r ][ 0 ] . ' <a' . $variations[ $r ][ 2 ] . ' href="http://track.mobetrack.com/aff_c?offer_id=10&aff_id=663853&aff_sub=wordpress&aff_sub2=' . $r . '">' . $variations[ $r ][ 1 ] . '</a></strong></p></div>';
		echo '<img src="http://track.mobetrack.com/aff_i?offer_id=10&aff_id=663853&aff_sub=wordpress&aff_sub2=' . $r . '" width="1" height="1">';
	}
}
