<?php
/*
Plugin Name: Multi Rating Comment
Version: 1.0
Plugin URI: http://www.faycal-benouda.com
Description: A plug-in to add additional fields in the comment form.
Author: Fayçal ben ouda
Author URI: http://www.faycal-benouda.com
*/

// Add custom meta (Multi Ratings) fields to the default comment form
// Default comment form includes name, email and URL
// Default comment form elements are hidden when user is logged in
 //wp_register_script ( 'mysample', plugins_url ( 'js/myjs.js', __FILE__ ) );
 wp_register_style ( 'mysample', plugins_url ( 'css/style.css', __FILE__ ) );
//wp_enqueue_script('mysample');
wp_enqueue_style('mysample');



function GetListRating(){

	$Item1=array("id"=>1,"alias"=>"accueil","titre"=>"Accueil");
	$Item2=array("id"=>2,"alias"=>"disponibilite","titre"=>"Disponibilité");
	$Item3=array("id"=>3,"alias"=>"ecoute","titre"=>"Écoute");
	$Item4=array("id"=>4,"alias"=>"respect_du_client","titre"=>"Respect du client");
	$Item5=array("id"=>5,"alias"=>"realisation_de_la_prestation","titre"=>"Réalisation de la prestation");
	$Item6=array("id"=>6,"alias"=>"prix","titre"=>"Prix");

	$ListRatings=array($Item1,$Item2,$Item3,$Item4,$Item5,$Item6);
	return $ListRatings;
}
add_filter('comment_form_default_fields','custom_fields');
function custom_fields($fields) {

		$commenter = wp_get_current_commenter();
		$req = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );

		$fields[ 'author' ] = '<div class="comment-form-author colthree"><input id="author" name="author" type="text" value="'. esc_attr( $commenter['comment_author'] ) . 
			'" size="30" tabindex="1"' . $aria_req . ' placeholder="'. __( 'Name' ) .'" /></div>';
		
		$fields[ 'email' ] = '<div class="comment-form-email  colthree mediuimcol">
			<input id="email" name="email" type="text" value="'. esc_attr( $commenter['comment_author_email'] ) . 
			'" size="30"  tabindex="2"' . $aria_req . ' placeholder="'. __( 'Email' ) .'" /></div>';
					
		$fields[ 'url' ] = '<div class="comment-form-url  colthree"><input id="url" name="url" type="text" value="'. esc_attr( $commenter['comment_author_url'] ) .'" size="30"  tabindex="3" placeholder="' . __( 'Website' )  .'" /></div>';

		/*$fields[ 'phone' ] = '<p class="comment-form-phone">'.
			'<label for="phone">' . __( 'Phone' ) . '</label>'.
			'<input id="phone" name="phone" type="text" size="30"  tabindex="4" /></p>';*/

	return $fields;
}

// Add fields after default fields above the comment box, always visible

add_action( 'comment_form_logged_in_after', 'additional_fields' );
add_action( 'comment_form_after_fields', 'additional_fields' );

function additional_fields () {

	?>

	<?php 
	echo '<p class="comment-form-title">'.
	'<label for="title">' . __( 'Title' ) . '</label>'.
	'<input id="title" name="title" type="text" size="30"  tabindex="5" /></p>';

$ListRatings=GetListRating();
echo ' <table class="star-rating" border="0">';
	foreach ($ListRatings as $key => $itemrating) {
		echo"<tr>";
		echo "<td>";
			/*echo '<p class="comment-form-rating">'.
			'<label for="rating">'. __($itemrating["titre"]) . '<span class="required">*</span></label>
			</p>';*/
			echo '<p class="comment-form-rating">'.
			'<label for="rating">'. __($itemrating["titre"]) . ' : </label>
			</p>';
			echo "</td>";
			echo "<td>";
		
			?>
			<div class="star-rating__wrap">
				<?php for( $i=1; $i <= 5; $i++ ):?>
				
			        <input class="star-rating__input" id="star-<?php echo $itemrating["alias"];?>-<?php echo $i;?>" type="radio" name="<?php echo $itemrating["alias"];?>" value="<?php echo $i;?>">
			        <label class="star-rating__ico" for="star-<?php echo $itemrating["alias"];?>-<?php echo $i;?>" ></label>
				<?php endfor;	?>
			</div>
			<?php 
			echo "</td>";
			echo "</tr>";			
	}
	echo  "</table>";
}

// Save the comment meta data along with comment

add_action( 'comment_post', 'save_comment_meta_data' );
function save_comment_meta_data( $comment_id ) {
	if ( ( isset( $_POST['phone'] ) ) && ( $_POST['phone'] != '') )
	$phone = wp_filter_nohtml_kses($_POST['phone']);
	add_comment_meta( $comment_id, 'phone', $phone );

	if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') )
	$title = wp_filter_nohtml_kses($_POST['title']);
	add_comment_meta( $comment_id, 'title', $title );


	$ListRatings=GetListRating();

	foreach ($ListRatings as $key => $itemrating) {
		if ( ( isset( $_POST[$itemrating["alias"]] ) ) && ( $_POST[$itemrating["alias"]] != '') )
		$rating = wp_filter_nohtml_kses($_POST[$itemrating["alias"]]);
		add_comment_meta( $comment_id, $itemrating["alias"], $rating );
	}
}


// Add the filter to check if the comment meta data has been filled or not

add_filter( 'preprocess_comment', 'verify_comment_meta_data' );
function verify_comment_meta_data( $commentdata ) {

	$ListRatings=GetListRating();

	foreach ($ListRatings as $key => $itemrating) {
		if ( ! isset( $_POST[$itemrating["alias"]] ) )
    	wp_die( __( 'Error: You did not add your rating. Hit the BACK button of your Web browser and resubmit your comment with rating.' ) );
	}
	return $commentdata;
}

//Add an edit option in comment edit screen  

add_action( 'add_meta_boxes_comment', 'extend_comment_add_meta_box' );
function extend_comment_add_meta_box() {
    add_meta_box( 'title', __( 'Comment Metadata - Extend Comment' ), 'extend_comment_meta_box', 'comment', 'normal', 'high' );
}
 
function extend_comment_meta_box ( $comment ) {
    //$phone = get_comment_meta( $comment->comment_ID, 'phone', true );
    $title = get_comment_meta( $comment->comment_ID, 'title', true );


    $ListRatings=GetListRating();

	foreach ($ListRatings as $key => $itemrating) {
		${$itemrating["alias"]} = get_comment_meta( $comment->comment_ID, $itemrating["alias"], true );
	}

    wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
    ?>
    <?php /*<p>
        <label for="phone"><?php _e( 'Phone' ); ?></label>
        <input type="text" name="phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat" />
    </p>*/?>
    <p>
        <label for="title"><?php _e( 'Title' ); ?></label>
        <input type="text" name="title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
    </p>

	<?php 
	$ListRatings=GetListRating();
	foreach ($ListRatings as $key => $itemrating) {?>
	 <p>
        <label for="<?php echo $itemrating["alias"];?>"><?php _e( $itemrating["titre"]." : " ); ?></label>
			<span class="commentratingbox">
			   <?php for( $i=1; $i <= 5; $i++ ) {
				echo '<span class="commentrating"><input type="radio" name="'.$itemrating["alias"].'" id="'.$itemrating["alias"].'" value="'. $i .'"';
				if ( ${$itemrating["alias"]} == $i ) echo ' checked="checked"';
				echo ' />'. $i .' </span>'; 
				} ?>
			</span>
    </p>
	<?php }?>
    <?php
}

// Update comment meta data from comment edit screen 

add_action( 'edit_comment', 'extend_comment_edit_metafields' );
function extend_comment_edit_metafields( $comment_id ) {
    if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;

	if ( ( isset( $_POST['phone'] ) ) && ( $_POST['phone'] != '') ) : 
	$phone = wp_filter_nohtml_kses($_POST['phone']);
	update_comment_meta( $comment_id, 'phone', $phone );
	else :
	delete_comment_meta( $comment_id, 'phone');
	endif;
		
	if ( ( isset( $_POST['title'] ) ) && ( $_POST['title'] != '') ):
	$title = wp_filter_nohtml_kses($_POST['title']);
	update_comment_meta( $comment_id, 'title', $title );
	else :
	delete_comment_meta( $comment_id, 'title');
	endif;


	$ListRatings=GetListRating();
	foreach ($ListRatings as $key => $itemrating) {

		if ( ( isset( $_POST[$itemrating["alias"]] ) ) && ( $_POST[$itemrating["alias"]] != '') ):
		${$itemrating["alias"]} = wp_filter_nohtml_kses($_POST[$itemrating["alias"]]);
		update_comment_meta( $comment_id, $itemrating["alias"], $rating );
		else :
		delete_comment_meta( $comment_id, $itemrating["alias"]);
		endif;

	}

}

// Add the comment meta (saved earlier) to the comment text 
// You can also output the comment meta values directly in comments template  

add_filter( 'comment_text', 'modify_comment');
function modify_comment( $text ){

	$plugin_url_path = WP_PLUGIN_URL;

	if( $commenttitle = get_comment_meta( get_comment_ID(), 'title', true ) ) {
		$commenttitle = '<strong>' . esc_attr( $commenttitle ) . '</strong><br/>';
		$text = $commenttitle . $text;
	} 

	$ListRatings=GetListRating();
	foreach ($ListRatings as $key => $itemrating) {

		if( $comment{$itemrating["alias"]} = get_comment_meta( get_comment_ID(), $itemrating["alias"], true ) ) {
			$comment{$itemrating["alias"]} = '<p class="comment-rating">	<img src="'. $plugin_url_path .
			'/ExtendComment/images/'. $comment{$itemrating["alias"]} . 'star.gif"/> '.$itemrating["titre"].' : <strong>'. $comment{$itemrating["alias"]}.' / 5</strong></p>';
			$text = $text . $comment{$itemrating["alias"]};	
		}	
	}


	return $text;
}