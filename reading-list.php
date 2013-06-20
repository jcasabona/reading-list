<?php
/**
 * @package Reading List
 * @version 0.4
 */
 
/*
Plugin Name: Reading List
Plugin URI: http://wordpress.org/#
Description: This plugin creates a custom post type & template page that you can use to display the books you're reading and plan to read
Author: Joe Casabona
Version: 0.4
Author URI: http://casabona.org/
*/


//some set-up.
define('RLIST_PATH', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );
define('RLIST_NAME', "Reading List");
define('RLIST_FULL_STAR', RLIST_PATH."stars/full.gif");
define('RLIST_HALF_STAR', RLIST_PATH."stars/half.gif");
define('RLIST_BLANK_STAR', RLIST_PATH."stars/blank.gif");
define('RLIST_CREDIT_LINK', 'Brought to you by: <a href="http://www.casabona.org">Joe Casabona</a>');
require_once('reading-list-admin.php');

if(get_option('readlist_color_code') === FALSE){
	add_option('readlist_color_code', true);
}



function readList_print_styles(){
print "<link rel='stylesheet'  href='".RLIST_PATH."reading-list.css' type='text/css' media='all' />";
}

add_action('wp_head', 'readList_print_styles');


function readlist_build_rating($r){ 
	if(strlen(trim($r)) > 1){
		$nums= explode(".", $r);
	}else{
		$nums= array($r);
	}
	
	$rating= "";
	$ct= 1;
	
	while($ct <= 5){
		if($ct < $nums[0] || ($ct <= $nums[0] && $nums[1] != 5)){
			$rating.= '<img class="rl-star" src="'. RLIST_FULL_STAR.'" alt="'.$r.'/5" />';
		}else if($nums[1] == 5){
			$rating.= '<img class="rl-star" src="'. RLIST_HALF_STAR.'" alt="'.$r.'/5" />';
			$nums[1] = 0;
		}else{
			$rating.= '<img class="rl-star" src="'. RLIST_BLANK_STAR.'" alt="'.$r.'/5" />';
		}
		
		$ct++;
	}
	
	return $rating;
}


/**EDITOR REPLACEMENT CODE: {READLIST} **/
function insert_read_list($content){
		while(ereg('(\{READLIST\})',$content, $match)){
 		 	$content = str_ireplace($match[0],readList_print_books(),$content);
   		}
  return $content;

}

add_filter('the_content', 'insert_read_list');


/**TEMPLATE TAGS***/

function books_alpha( $orderby ){
	global $rl_alpha;
    return $rl_alpha;
}

$rl_main= "";
$rl_alpha= "-";

switch(get_option('readlist_orderby')){
	case "author":
		$rl_main= 'post_type=reading-list&orderby=meta_value&meta_key=author&order=ASC&nopaging=true';
		$rl_alpha= "meta_value ASC, post_title ASC";
		break;
	case "title":
		$rl_main= 'post_type=reading-list&orderby=title&order=ASC&nopaging=true';
		$rl_alpha= "";
		break;
	case "rating":
		$rl_main= 'post_type=reading-list&orderby=meta_value&meta_key=rating&nopaging=true';
		$rl_alpha= "meta_value DESC, post_title ASC";
		break;
	default:
		$rl_main= 'post_type=reading-list&orderby=meta_value&meta_key=bookStatus&nopaging=true';
		$rl_alpha= "meta_value DESC, post_title ASC";
		break;
		
}

function readList_print_books(){

global $rl_alpha, $rl_main;

if($rl_alpha != ""){
	 add_filter('posts_orderby', 'books_alpha' );
}

$rl_review= get_option('readlist_show_review');
query_posts($rl_main);

$rl_order= get_option('readlist_orderby');
?>		
		<table class="reading">
			<thead>
				<th>Rating</th>
				<th>Title</th>
				<th>Author</th>
				<?php if($rl_review){ ?> <th>Review</th> <?php } ?>
			</thead>
			<tbody>
	
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<?php
			$title= str_ireplace('"', '', trim(get_the_title()));
			$desc= str_ireplace('"', '', trim(get_the_content()));
			$author= get_post_custom_values('author');
			$amzn= get_post_custom_values('amazonLink');
			$status= get_post_custom_values('bookStatus');
			$r= get_post_custom_values('rating');
			$linkedTitle= ($amzn[0] != "") ? '<a href="'.$amzn[0].'">'.$title.'</a>' : $title;
			
			if(get_option('readlist_color_code')){
				if($status[0] == 1) $class="current";
				else if($status[0] == -1) $class="finished";
				else $class="book";
			}else{
				$class= "";
			}
			
			$rating= ($status[0] == -1 && $r[0] != "") ? $r[0]."/5" : "--";
		?>
		
			<tr class="post <?=$class?>" id="post-<?php the_ID(); ?>">
			<td class="rating"><?=$rating?></td>
			<td><?=$linkedTitle?></td>	
			<td><?=$author[0]?></td>
			<?php if($rl_review){ ?> <td><?php if($desc != ""){  ?><a href="<?php the_permalink(); ?>">Review</a> <?php }else{ print "N/A"; } ?></td> <?php } ?>

			</tr>

		
<?php endwhile; endif; wp_reset_query();?>

			</tbody>
		
		</table>
<?php
}  

function readlist_single_loop(){

	if (have_posts()) : while (have_posts()) : the_post();
			
			$author= get_post_custom_values('author');
			$amzn= get_post_custom_values('amazonLink');
			$status= get_post_custom_values('bookStatus');
			$r= get_post_custom_values('rating');
			if($status[0] == 1) $status[1]= "Reading";
			else if($status[0] == -1) $status[1]= "Finished Reading";
			else $status[1]= "Want to Read";
			
			$rating= ($status[0] == -1 && $r[0] != "") ? readlist_build_rating($r[0]) : "--";
		?>
		
			<h1 class="entry-title full-title"><em><?php the_title(); ?></em> by: <?php print $author[0]; ?></h1>
			<p class="status">Status: <?php print $status[1]; ?> <?php if($status[0] == -1){ ?> | Rating: <?php print $rating; ?> <?php } ?></p>
			<p><?php if($amzn[0] != "") print '<p><a href="'.$amzn[0].'">Get <em>'. get_the_title() .'</em> on Amazon</a></p>'; ?></p>
			
			<?php the_content(); ?>

		
	<?php endwhile; endif;
}


?>