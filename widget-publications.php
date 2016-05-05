<?php
/***************************************
 *	MOD:
 *	Custom widget to display the most recent active Publication.
 *	
 */	
class Publications extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'publications', // Base ID
			'Publications', // Name
			array( 'description' => __( 'Recent Released Publications', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$reltype = apply_filters( 'widget_reltype', $instance['reltype'] );
		global $post;
		
		//find out which region this is
		$ptitle='';
		$l2id= getLevel2ID();
		$stext='';

        $post_parent = 8209;

		if($reltype=='all events') { 
			$stext=' '; 
		}
		
		// priority areas
		if($post->ID==12) { 
			$stext="AND M.meta_key='eventpriorities'"; 
		}
		
		// alliances
		if($post->ID==22) { 
			$stext="AND M.meta_key='eventalliances'"; 
		}

		if($stext!='') {

			// If coming from the Alliance main page, get the priority associated with that
			// alliance so it can be passed as a URL variable.

			// If parent is the main research alliance page
			if (wp_get_post_parent_id($post->ID_==22 )) {

				// Get the priority value associated with the Alliance
				$q1 = "
					SELECT 
	                    ID,meta_key,M.meta_value as thepriority 
	                FROM 
	                	wp_posts P
					JOIN 
	                    wp_postmeta M ON M.post_id=P.ID
					WHERE 
	                    P.ID=$post->ID
	                AND 
	                  	M.meta_key =  'eventpriorities'
	                AND
	                    P.post_status='publish'
	                AND (
	                    CAST( M.meta_value AS CHAR ) LIKE  '%\"1\"%'
	                    OR
	                    CAST( M.meta_value AS CHAR ) LIKE  '%\"2\"%'
	                    OR
	                    CAST( M.meta_value AS CHAR ) LIKE  '%\"3\"%'
	                    OR
	                    CAST( M.meta_value AS CHAR ) LIKE  '%\"4\"%'
	                    OR
	                    CAST( M.meta_value AS CHAR ) LIKE  '%\"5\"%'
	                    OR
	                    CAST( M.meta_value AS CHAR ) LIKE  '%\"6\"%'
	                    OR
	                    CAST( M.meta_value AS CHAR ) LIKE  '%\"7\"%'     
					)
					";

					$r1 = mysql_query ("$q1");
	 				
	 				// No loop, just one row returned
                    $row = mysql_fetch_assoc($r1);      

	    			// Set Category values. Value is a string (of a numeric)
	                // inside a serialized array in the DB per Advanced
	                // Custom Fields
	                $effective_teachers = '"1"';
	                $low_performing_schools = '"2"';
	                $educational_equity = '"3"';
	                $college_careers = '"4"';

	                // Set alliance priority down 1 digit because jquery
	                // 'active' parameter starts at 0. 
	 				if (stripos($row['thepriority'], $effective_teachers)) {
	 					$alliance_priority = 0;
	 				}
	 				elseif (stripos($row['thepriority'], $low_performing_schools)) {
	 					$alliance_priority = 1;
	 				}
	 				elseif (stripos($row['thepriority'], $educational_equity)) {
	 					$alliance_priority = 2;
	 				}
	 				elseif (stripos($row['thepriority'], $college_careers)) {
	 					$alliance_priority = 3;
	 				}
					
			}

			// code here
 			$q = "SELECT 
                   	post_content,post_title,ID,meta_key,meta_value 
                FROM 
                  	wp_posts P
				JOIN 
 					wp_postmeta M ON M.post_id=P.ID
				WHERE 
                	P.ID!=130
                AND
                	P.post_status='publish' 
                AND 
                	P.post_parent=$post_parent  
                    $stext
				ORDER BY 
                    post_date DESC
				LIMIT 1
				";

			//echo $q;
			$r = mysql_query ("$q"); 
			$rs=array();
			$i=0;

			while ($row = mysql_fetch_array ($r)) {
				$rs[$i]['ID']=$row['ID'];
				$rs[$i]['title']=$row['post_title'];
				$i++;
			}
			if(count($rs)>0) {
				echo $before_widget;
                                
                echo '<div class="relnei-hov pubswij">';  
                echo    '<h2>Publications</h2>'; 
                echo    '<div class="mask">'; 
                echo        '<div class="wij-top">';

                	echo    	'<p class="title">Publications</p>';
                	echo    	'<h3><a href="/?p='.$rs[$i]['ID'].'">'.$rs[$i]['title'].'</a></h3>';

               	echo    	'</div>'; // wij-top end
        
               	echo        '<div class="wij-footer">';
               	echo            '<a href="/publications.html?thepid='.$alliance_priority.'" class="wij-footerlink">View All</a>';  
               	echo        '</div>'; // wij-footer end
               	echo    '</div>';  // mask div end
               	echo '</div>';  
               	echo $after_widget;                
                        
			}			
		}
	}

	/**
	 * Sanitize widget form values as they are saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['reltype'] = strip_tags( $new_instance['reltype'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'reltype' ] ) ) {
			$reltype = $instance[ 'reltype' ];
		}
		else {
			$reltype = __( 'this section only', 'text_domain' );
		}
		?>
		<p><label for="<?php echo $this->get_field_id( 'reltype' ); ?>">Show works for:</label>
			<select id="<?php echo $this->get_field_id( 'reltype' ); ?>" name="<?php echo $this->get_field_name( 'reltype' ); ?>" class="widefat" style="width:100%;">
				<option <?php if ( 'this section only' == $instance['reltype'] ) echo 'selected="selected"'; ?> value="this section only">this section only</option>
				<option <?php if ( 'all new publications' == $instance['reltype'] ) echo 'selected="selected"'; ?> value="all new publications">all publications</option>
			</select>
		</p>
		<?php 
	}

} 
// register  widget
add_action( 'widgets_init', create_function( '', 'register_widget( "publications" );' ) );