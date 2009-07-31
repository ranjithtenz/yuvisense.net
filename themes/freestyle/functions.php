<?php
// Remove the WordPress Generator 
function sandbox_remove_generators() { return ''; }  
add_filter('the_generator','sandbox_remove_generators');

// Custom Comments Section
function comment($comment, $args, $depth) {
   $GLOBALS['comment'] = $comment; ?>
   <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
     <div id="comment-<?php comment_ID(); ?>" class="clearfix">
     
      <div class="vcard comment-meta">
         <?php echo get_avatar($comment,$size='48' ); ?>
         
       	  <div class="comment-author">
		 	 <?php printf(__('<strong>%s</strong> <span class="says">says:</span>'), get_comment_author_link()) ?>
		  </div>        
         
	      <div class="commentmetadata"><a href="<?php echo htmlspecialchars( get_comment_link( $comment->comment_ID ) ) ?>"><?php printf(__('%1$s - %2$s'), get_comment_time('g:i A'), get_comment_date('m/d/y')) ?></a></div>         
	      
	      <div class="reply">
	         <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
	      </div>      
      
      </div>
      
      <div class="comment-content">
       <div class="comment-content-inner">
	
	      <?php comment_text() ?>
	      
	      <?php edit_comment_link(__('(Edit)'),'  ','') ?>
	      
	      <?php if ($comment->comment_approved == '0') : ?>
	         <em><?php _e('Your comment is awaiting moderation.') ?></em>
	         <br />
	      <?php endif; ?>	      
	     
	   </div>   
      </div>
      
     </div>
<?php
        }


// Add a Default-Gravatar to Discussion Options 
if ( !function_exists('fb_addgravatar') ) {
	function fb_addgravatar( $avatar_defaults ) {
		$myavatar = get_bloginfo('template_directory') . '/images/default-avatar.gif';
		$avatar_defaults[$myavatar] = 'Theme Default';
 
		return $avatar_defaults;
	}
 
	add_filter( 'avatar_defaults', 'fb_addgravatar' );
}


// Produces a list of pages in the header without whitespace -- er, I mean negative space.
function sandbox_globalnav() {
	echo '<div id="menu"><ul>';
	$menu = wp_list_pages('title_li=&sort_column=menu_order&echo=0'); // Params for the page list in header.php
	echo str_replace( array( "\r", "\n", "\t" ), '', $menu );
	echo "</ul></div>\n";
}

// Generates semantic classes for BODY element
function sandbox_body_class( $print = true ) {
	global $wp_query, $current_user;

	// It's surely a WordPress blog, right?
	$c = array('wordpress');

	// Applies the time- and date-based classes (below) to BODY element
	sandbox_date_classes( time(), $c );

	// Generic semantic classes for what type of content is displayed
	is_front_page()  ? $c[] = 'home'       : null; // New 'front' class for WP 2.5
	is_home()        ? $c[] = 'blog'       : null; // Class for the posts, if set
	is_archive()     ? $c[] = 'archive'    : null;
	is_date()        ? $c[] = 'date'       : null;
	is_search()      ? $c[] = 'search'     : null;
	is_paged()       ? $c[] = 'paged'      : null;
	is_attachment()  ? $c[] = 'attachment' : null;
	is_404()         ? $c[] = 'four04'     : null; // CSS does not allow a digit as first character

	// Special classes for BODY element when a single post
	if ( is_single() ) {
		$postID = $wp_query->post->ID;
		the_post();

		// Adds 'single' class and class with the post ID
		$c[] = 'single postid-' . $postID;

		// Adds classes for the month, day, and hour when the post was published
		if ( isset( $wp_query->post->post_date ) )
			sandbox_date_classes( mysql2date( 'U', $wp_query->post->post_date ), $c, 's-' );

		// Adds category classes for each category on single posts
		if ( $cats = get_the_category() )
			foreach ( $cats as $cat )
				$c[] = 's-category-' . $cat->slug;

		// Adds tag classes for each tags on single posts
		if ( $tags = get_the_tags() )
			foreach ( $tags as $tag )
				$c[] = 's-tag-' . $tag->slug;

		// Adds MIME-specific classes for attachments
		if ( is_attachment() ) {
			$mime_type = get_post_mime_type();
			$mime_prefix = array( 'application/', 'image/', 'text/', 'audio/', 'video/', 'music/' );
				$c[] = 'attachmentid-' . $postID . ' attachment-' . str_replace( $mime_prefix, "", "$mime_type" );
		}

		// Adds author class for the post author
		$c[] = 's-author-' . sanitize_title_with_dashes(strtolower(get_the_author_login()));
		rewind_posts();
	}

	// Author name classes for BODY on author archives
	elseif ( is_author() ) {
		$author = $wp_query->get_queried_object();
		$c[] = 'author';
		$c[] = 'author-' . $author->user_nicename;
	}

	// Category name classes for BODY on category archvies
	elseif ( is_category() ) {
		$cat = $wp_query->get_queried_object();
		$c[] = 'category';
		$c[] = 'category-' . $cat->slug;
	}

	// Tag name classes for BODY on tag archives
	elseif ( is_tag() ) {
		$tags = $wp_query->get_queried_object();
		$c[] = 'tag';
		$c[] = 'tag-' . $tags->slug;
	}

	// Page author for BODY on 'pages'
	elseif ( is_page() ) {
		$pageID = $wp_query->post->ID;
		$page_children = wp_list_pages("child_of=$pageID&echo=0");
		the_post();
		$c[] = 'page pageid-' . $pageID;
		$c[] = 'page-author-' . sanitize_title_with_dashes(strtolower(get_the_author('login')));
		// Checks to see if the page has children and/or is a child page; props to Adam
		if ( $page_children != '' )
			$c[] = 'page-parent';
		if ( $wp_query->post->post_parent )
			$c[] = 'page-child parent-pageid-' . $wp_query->post->post_parent;
		rewind_posts();
	}

	// For when a visitor is logged in while browsing
	if ( $current_user->ID )
		$c[] = 'loggedin';

	// Paged classes; for 'page X' classes of index, single, etc.
	if ( ( ( $page = $wp_query->get('paged') ) || ( $page = $wp_query->get('page') ) ) && $page > 1 ) {
		$c[] = 'paged-' . $page;
		if ( is_single() ) {
			$c[] = 'single-paged-' . $page;
		} elseif ( is_page() ) {
			$c[] = 'page-paged-' . $page;
		} elseif ( is_category() ) {
			$c[] = 'category-paged-' . $page;
		} elseif ( is_tag() ) {
			$c[] = 'tag-paged-' . $page;
		} elseif ( is_date() ) {
			$c[] = 'date-paged-' . $page;
		} elseif ( is_author() ) {
			$c[] = 'author-paged-' . $page;
		} elseif ( is_search() ) {
			$c[] = 'search-paged-' . $page;
		}
	}
	
	// A little Browser detection shall we?
	$browser = $_SERVER[ 'HTTP_USER_AGENT' ];
	
	// Mac, PC ...or Linux
	if ( preg_match( "/Mac/", $browser ) ){
			$c[] = 'mac';
		
	} elseif ( preg_match( "/Windows/", $browser ) ){
			$c[] = 'windows';
		
	} elseif ( preg_match( "/Linux/", $browser ) ) {
			$c[] = 'linux';

	} else {
			$c[] = 'unknown-os';
	}
	
	// Checks browsers in this order: Chrome, Safari, Opera, MSIE, FF
	if ( preg_match( "/Chrome/", $browser ) ) {
			$c[] = 'chrome';

			preg_match( "/Chrome\/(\d.\d)/si", $browser, $matches);
			$ch_version = 'ch' . str_replace( '.', '-', $matches[1] );      
			$c[] = $ch_version;

	} elseif ( preg_match( "/Safari/", $browser ) ) {
			$c[] = 'safari';
			
			preg_match( "/Version\/(\d.\d)/si", $browser, $matches);
			$sf_version = 'sf' . str_replace( '.', '-', $matches[1] );      
			$c[] = $sf_version;
			
	} elseif ( preg_match( "/Opera/", $browser ) ) {
			$c[] = 'opera';
			
			preg_match( "/Opera\/(\d.\d)/si", $browser, $matches);
			$op_version = 'op' . str_replace( '.', '-', $matches[1] );      
			$c[] = $op_version;
			
	} elseif ( preg_match( "/MSIE/", $browser ) ) {
			$c[] = 'msie';
			
			if( preg_match( "/MSIE 6.0/", $browser ) ) {
					$c[] = 'ie6';
			} elseif ( preg_match( "/MSIE 7.0/", $browser ) ){
					$c[] = 'ie7';
			} elseif ( preg_match( "/MSIE 8.0/", $browser ) ){
					$c[] = 'ie8';
			}
			
	} elseif ( preg_match( "/Firefox/", $browser ) && preg_match( "/Gecko/", $browser ) ) {
			$c[] = 'firefox';
			
			preg_match( "/Firefox\/(\d)/si", $browser, $matches);
			$ff_version = 'ff' . str_replace( '.', '-', $matches[1] );      
			$c[] = $ff_version;
			
	} else {
			$c[] = 'unknown-browser';
	}
	
	

	// Separates classes with a single space, collates classes for BODY
	$c = join( ' ', apply_filters( 'body_class',  $c ) );

	// And tada!
	return $print ? print($c) : $c;
}

// Generates semantic classes for each post DIV element
function sandbox_post_class( $print = true ) {
	global $post, $sandbox_post_alt;

	// hentry for hAtom compliace, gets 'alt' for every other post DIV, describes the post type and p[n]
	$c = array( 'hentry', "p$sandbox_post_alt", $post->post_type, $post->post_status );

	// Author for the post queried
	$c[] = 'author-' . sanitize_title_with_dashes(strtolower(get_the_author('login')));

	// Category for the post queried
	foreach ( (array) get_the_category() as $cat )
		$c[] = 'category-' . $cat->slug;

	// Tags for the post queried; if not tagged, use .untagged
	if ( get_the_tags() == null ) {
		$c[] = 'untagged';
	} else {
		foreach ( (array) get_the_tags() as $tag )
			$c[] = 'tag-' . $tag->slug;
	}

	// For password-protected posts
	if ( $post->post_password )
		$c[] = 'protected';

	// Applies the time- and date-based classes (below) to post DIV
	sandbox_date_classes( mysql2date( 'U', $post->post_date ), $c );

	// If it's the other to the every, then add 'alt' class
	if ( ++$sandbox_post_alt % 2 )
		$c[] = 'alt';

	// Separates classes with a single space, collates classes for post DIV
	$c = join( ' ', apply_filters( 'post_class', $c ) );

	// And tada!
	return $print ? print($c) : $c;
}

// Define the num val for 'alt' classes (in post DIV and comment LI)
$sandbox_post_alt = 1;

// Generates semantic classes for each comment LI element
function sandbox_comment_class( $print = true ) {
	global $comment, $post, $sandbox_comment_alt;

	// Collects the comment type (comment, trackback),
	$c = array( $comment->comment_type );

	// Counts trackbacks (t[n]) or comments (c[n])
	if ( $comment->comment_type == 'comment' ) {
		$c[] = "c$sandbox_comment_alt";
	} else {
		$c[] = "t$sandbox_comment_alt";
	}

	// If the comment author has an id (registered), then print the log in name
	if ( $comment->user_id > 0 ) {
		$user = get_userdata($comment->user_id);

		// For all registered users, 'byuser'; to specificy the registered user, 'commentauthor+[log in name]'
		$c[] = 'byuser comment-author-' . sanitize_title_with_dashes(strtolower( $user->user_login ));
		// For comment authors who are the author of the post
		if ( $comment->user_id === $post->post_author )
			$c[] = 'bypostauthor';
	}

	// If it's the other to the every, then add 'alt' class; collects time- and date-based classes
	sandbox_date_classes( mysql2date( 'U', $comment->comment_date ), $c, 'c-' );
	if ( ++$sandbox_comment_alt % 2 )
		$c[] = 'alt';

	// Separates classes with a single space, collates classes for comment LI
	$c = join( ' ', apply_filters( 'comment_class', $c ) );

	// Tada again!
	return $print ? print($c) : $c;
}

// Generates time- and date-based classes for BODY, post DIVs, and comment LIs; relative to GMT (UTC)
function sandbox_date_classes( $t, &$c, $p = '' ) {
	$t = $t + ( get_option('gmt_offset') * 3600 );
	$c[] = $p . 'y' . gmdate( 'Y', $t ); // Year
	$c[] = $p . 'm' . gmdate( 'm', $t ); // Month
	$c[] = $p . 'd' . gmdate( 'd', $t ); // Day
	$c[] = $p . 'h' . gmdate( 'H', $t ); // Hour
}

// For category lists on category archives: Returns other categories except the current one (redundant)
function sandbox_cats_meow($glue) {
	$current_cat = single_cat_title( '', false );
	$separator = "\n";
	$cats = explode( $separator, get_the_category_list($separator) );

	foreach ( $cats as $i => $str ) {
		if ( strstr( $str, ">$current_cat<" ) ) {
			unset($cats[$i]);
			break;
		}
	}

	if ( empty($cats) )
		return false;

	return trim(join( $glue, $cats ));
}

// For tag lists on tag archives: Returns other tags except the current one (redundant)
function sandbox_tag_ur_it($glue) {
	$current_tag = single_tag_title( '', '',  false );
	$separator = "\n";
	$tags = explode( $separator, get_the_tag_list( "", "$separator", "" ) );

	foreach ( $tags as $i => $str ) {
		if ( strstr( $str, ">$current_tag<" ) ) {
			unset($tags[$i]);
			break;
		}
	}

	if ( empty($tags) )
		return false;

	return trim(join( $glue, $tags ));
}

// Produces an avatar image with the hCard-compliant photo class
function sandbox_commenter_link() {
	$commenter = get_comment_author_link();
	if ( ereg( '<a[^>]* class=[^>]+>', $commenter ) ) {
		$commenter = ereg_replace( '(<a[^>]* class=[\'"]?)', '\\1url ' , $commenter );
	} else {
		$commenter = ereg_replace( '(<a )/', '\\1class="url "' , $commenter );
	}
	$email = get_comment_author_email();
	$avatar = str_replace( "class='avatar", "class='photo avatar", get_avatar( "$email", "32" ) );
	echo $avatar . ' <span class="fn n">' . $commenter . '</span>';
}

// Sandbox gallery short code; styles used in style.css file
function sandbox_gallery($attr) {
	global $post;
	// Sets our defaults for the Sandbox gallery short code
	extract( shortcode_atts(
		array(
			'orderby'    => 'menu_order ASC, ID ASC',
			'id'         => $post->ID,
			'itemtag'    => 'dl',
			'icontag'    => 'dt',
			'captiontag' => 'dd',
			'columns'    => 3,
			'size'       => 'thumbnail',
		),
		$attr ) );
	$id = intval($id);
	$orderby = addslashes($orderby);
	$attachments = get_children("post_parent=$id&post_type=attachment&post_mime_type=image&orderby=\"{$orderby}\"");
	// If we have nothing, show nothing
	if ( empty($attachments) )
		return '';
	// Shows simple image links when viewed in a feed
	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $id => $attachment )
			$output .= wp_get_attachment_link( $id, $size, true ) . "\n";
		return $output;
	}
	$listtag = tag_escape($listtag);
	$itemtag = tag_escape($itemtag);
	$captiontag = tag_escape($captiontag);
	$columns = intval($columns);
	// Divides number of columns for even widths
	$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
	// Let's begin our gallery
	$output = apply_filters( "gallery_style", "<div class='gallery gallery-set-1'>" );
	// We're using pretty much the same code from ../wp-includes/media.php
	foreach ( $attachments as $id => $attachment ) {
		$link = wp_get_attachment_link( $id, $size, true );
		// This is our 'wrapper' for each gallery item
		$output .= "<{$itemtag} class='gallery-item' style='width:{$itemwidth}%;'>";
		// And now we have the actual image link
		$output .= "<{$icontag} class='gallery-icon'>$link</{$icontag}>";
		// Next, show the image caption if present
		if ( $captiontag && trim($attachment->post_excerpt) ) {
			$output .= "<{$captiontag} class='gallery-caption'>{$attachment->post_excerpt}</{$captiontag}>";
		}
		// Close the gallery item 'wrapper'
		$output .= "</{$itemtag}>";
		// Start a new gallery set for our 'columns'
		if ( $columns > 0 && ++$i % $columns == 0 ) {
			$gallery_count = 2;
			$output .= "\n</div>\n<div class='gallery gallery-set-" . $gallery_count . "'>\n";
			$gallery_count++;
		}
	}
	// Ends our gallery
	$output .= "</div>\n";
	// And spits out the chewed up code
	return $output;
}


// Add Sandbox function to gallery short code
add_shortcode( 'gallery', 'sandbox_gallery' );

// Adds filters so that things run smoothly
add_filter( 'archive_meta', 'wptexturize' );
add_filter( 'archive_meta', 'convert_smilies' );
add_filter( 'archive_meta', 'convert_chars' );
add_filter( 'archive_meta', 'wpautop' );

// Remember: the Sandbox is for play.




if ( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'before_widget' => '<div class="right-sidebar">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widgettitle">',
		'after_title' => '</h3>',
	));
}

?>