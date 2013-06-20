<?php

function reading_list_currently_reading_widget($args) {
  extract($args);
  $isReading= false;
  echo $before_widget;
  echo $before_title;?>Currently Reading<?php echo $after_title;
  	echo '<ul>';
 	query_posts('post_type=reading-list&orderby=meta_value&meta_key=bookStatus&nopaging=true');
 	
 	if (have_posts()) : while (have_posts()) : the_post();
		$status= get_post_custom_values('bookStatus');
		
		if($status[0] == 1){
		
			$title= str_ireplace('"', '', trim(get_the_title()));
			$author= get_post_custom_values('author');
			$amzn= get_post_custom_values('amazonLink');
			$cover= get_the_post_thumbnail();
			
			$linkedCover= ($cover == "") ? "" : '<a href="'.$amzn[0].'">'.$cover.'</a>';
			$linkedTitle= ($amzn[0] != "") ? '<a href="'.$amzn[0].'">'.$title.'</a>' : $title;
			
			echo '<div class="rl-widget">'. $linkedCover.'<br/>'. $linkedTitle .' by '. $author[0].'</div>';
			$isReading= true;
		}
	
	endwhile; endif; wp_reset_query();
	
	if(!$isReading){
		echo '<li>Nothing right now. Any Suggestions?</li>';
	}
	
	echo '</ul>';

 
  echo $after_widget;
}


function reading_list__widget($args) {

  extract($args);
  $isReading= false;
  echo $before_widget;
  echo $before_title;?>Reading List<?php echo $after_title;
 
	query_posts('post_type=reading-list&orderby=meta_value&meta_key=bookStatus&nopaging=true');

?>		
		<table class="reading">
			<tbody>
	
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

		<?php
			
			$title= str_ireplace('"', '', trim(get_the_title()));
			$desc= str_ireplace('"', '', trim(get_the_content()));
			$author= get_post_custom_values('author');
			$amzn= get_post_custom_values('amazonLink');
			$status= get_post_custom_values('bookStatus');
			$linkedTitle= ($amzn[0] != "") ? '<a href="'.$amzn[0].'">'.$title.'</a>' : $title;
			
			
			if($status[0] >= 0){
			
				if(get_option('readlist_color_code')){
					if($status[0] == 1) $class="current";
					else if($status[0] == -1) $class="finished";
					else $class="book";
				}else{
					$class= "";
				}
		?>
		
				<tr class="post <?=$class?>" id="post-<?php the_ID(); ?>">
				<?php 
					if($status[0] == 1){
						$cover= get_the_post_thumbnail();
						$cover= ($cover == "") ? "" : '<a href="'.$amzn[0].'">'.$cover.'</a>';
				
				?>
					<td colspan="2" style="text-align: center;">
						<p><strong>Currently Reading:</strong></p>
						<?=$cover;?>
						<p><?=$linkedTitle?> by <?=$author[0]?></p>
					</td>
				<?php
					}else{
				?>
					<td><?=$linkedTitle?></td>	
					<td>by <?=$author[0]?></td>
					
				<?php } ?>
				</tr>
				
			<?php } ?>

		
<?php endwhile; endif; wp_reset_query();?>

			</tbody>
		
		</table>
<?php

  echo $after_widget;
}

wp_register_sidebar_widget( 'currently-reading', 'Currently Reading', 'reading_list_currently_reading_widget' );
wp_register_sidebar_widget( 'reading-list', 'Reading List', 'reading_list__widget' );

?>