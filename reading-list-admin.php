<?php
require_once('reading-list-options-page.php');
require_once('reading-list-widget.php');

add_action('init', 'reading_list_register');  
  
function reading_list_register() {  
    $args = array(  
        'label' => __('Reading List'),  
        'singular_label' => __('Book'),  
        'public' => true,  
        'show_ui' => true,  
        'capability_type' => 'post',  
        'hierarchical' => false,  
        'rewrite' => true,  
        'supports' => array('title', 'editor', 'thumbnail')  
       );  
  
    register_post_type( 'reading-list' , $args );  
    set_post_thumbnail_size(160);
}  


add_action("admin_init", "admin_init");  
add_action('save_post', 'save_amazon_link');  

function admin_init(){  
    add_meta_box("amazonLink-meta", "Book Options", "meta_options", "reading-list", "normal", "low");   
}  
  

function meta_options(){  
        global $post;  
        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
        $custom = get_post_custom($post->ID);
        $author= $custom["author"][0];  
        $link = $custom["amazonLink"][0];  
        $status= $custom["bookStatus"][0];
        $rating= $custom["rating"][0];
        $rating= ($rating == "") ? 0 : $rating;
        $priority= $custom["priority"][0];
        $priority= ($priority == "") ? 99999 : $priority;
?>  
	<table>
	<tr>
		<td><label>Author:</label></td>
		<td><input name="author" value="<?php echo $author; ?>" /></td>
	</tr>
	<tr>
		<td><label>Amazon Link:</label></td>
		<td><input name="amazonLink" value="<?php echo $link; ?>" /></td>
	</tr>
	<tr>
    	<td><label>Status: </label></td>
    	<td><select name="bookStatus">
    		<option value="0" <?php if($status == 0) print "selected"; ?>>Want to Read</option>
    		<option value="1"  <?php if($status == 1) print "selected"; ?>>Currently Reading</option>
    		<option value="-1"  <?php if($status == -1) print "selected"; ?>>Finished</option>
    	</select></td>
    </tr>
    <tr>
   	 	<td><label>Rating (Out of 5):</label></td>
   	 	<td><input name="rating" value="<?php echo $rating; ?>" /></td>
   	</tr>
   	<tr>
   		<td><label>Priority</label></td>
   		<td><input name="priority" value="<?php echo $priority; ?>" /></td>
   	</tr>
    </table>
<?php  
    }  
  
function save_amazon_link(){  
    global $post;  
    
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){ 
		return $post_id;
	}else{
		update_post_meta($post->ID, "author", $_POST["author"]);
    	update_post_meta($post->ID, "amazonLink", $_POST["amazonLink"]); 
    	update_post_meta($post->ID, "bookStatus", $_POST["bookStatus"]);
    	update_post_meta($post->ID, "rating", $_POST["rating"]);
    	update_post_meta($post->ID, "priority", $_POST["priority"]);
    } 
}  



add_filter("manage_edit-reading-list_columns", "book_edit_columns");  
add_action("manage_posts_custom_column",  "book_custom_columns");  
  
function book_edit_columns($columns){  
        $columns = array(  
            "cb" => "<input type=\"checkbox\" />",  
            "title" => "Title",  
            "auth" => "Author", 
            "description" => "Review", 
            "link" => "Link",  
            "rate" => "Rating",  
            "mod" => "Updated",  
        );  
  
        return $columns;  
}  
  
function book_custom_columns($column){  
        global $post;  
        switch ($column)  
        {  
        	case "auth":
        		$custom = get_post_custom();  
                echo $custom["author"][0];  
                break;  
            case "description":  
                the_excerpt();  
                break;  
            case "link":  
                $custom = get_post_custom();  
                if(trim($custom["amazonLink"][0]) != ""){
                	echo '<a href="'.$custom["amazonLink"][0].'">Link</a>'; 
                }else{
                	echo "n/a";
                } 
                break;  
            case "rate":  
                $custom = get_post_custom();  
                echo $custom["rating"][0]."/5";  
                break;  
           case "mod":  
                $dateFinished= get_the_modified_date('\<\s\t\r\o\n\g\>Y\<\/\s\t\r\o\n\g\>: F, j');  
                $custom= get_post_custom();
                if($custom['bookStatus'][0] == -1 && $dateFinished){
                	echo $dateFinished;
                }  
                break;  
        }  
}

?>