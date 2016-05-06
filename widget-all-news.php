<?php
/***************************************
 *	
 *	Custom widget to display associated alliance research image in sidebar
 *	
 */	
class Alliance_Newsletter extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'alliance_newsletter', // Base ID
			'Alliance Newsletter', // Name
			array( 'description' => __( 'Alliance Sidebar Newsletter', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 */
	public function widget( $args, $instance ) {
		
        global $post;
        
        /** 
         *   For Main Alliance page and sign up form,  
         *   do not show Newsletter widget
         */
        $noshowarr = array(22, 10412);         
                
		$oktoshow_widget = true;

		for($i=0; $i<count($noshowarr); $i++) {
			if (get_the_id() == $noshowarr[$i]) {
				$oktoshow_widget = false;
			}
		}
		
		if($oktoshow_widget) { 
			extract( $args );
			$title = apply_filters( 'widget_title', $instance['title'] );
			
            $signuplink = 'alliance-sign.html';
            
            /**
             * Get dynamic newsletter for each Alliance. Alliances can
             * have children pages like the Core Planning Group so for 
             * all children within an alliance display the apropos
             * newsletter. 
             */
            $newsletterParent = 262;
            
            if (get_the_id() == 262 || $post->post_parent ==262) {
                
                $titletext = 'NCCRA News'; 
                $flyertext = 'NCCRA Flyer';
                
                $newsletterParent = 262;  
                $archive = '/research-alliances/college-and-career-readiness/newsletter-archive.html';
                
            } else if (get_the_id() == 240 || $post->post_parent ==240) {
                
                $titletext = 'ECEA News'; 
                $flyertext = 'ECEA Flyer';
                
                $newsletterParent = 240; 
                $archive = '/research-alliances/early-childhood-education-alliance/newsletter-archive.html';
                
            } else if (get_the_id() == 1023 || $post->post_parent == 1023) {
                
                $titletext = 'USIA News'; 
                $flyertext = 'USIA Flyer';
                
                $newsletterParent = 1023; 
                $archive='/research-alliances/urban-school-improvement-alliance/newsletter-archive.html';
                
            } else if (get_the_id() == 1094 || $post->post_parent == 1094) {
                
                $titletext = 'NEERA News';
                $flyertext = 'NEERA Flyer';
                
                $newsletterParent = 1094; 
                $archive='/research-alliances/northeast-educator-effectiveness-alliance/newsletter-archive.html';
                
            } else if (get_the_id() == 1056 || $post->post_parent == 1056) {
                
                $titletext = 'NRDRA News';
                $flyertext = 'NRDRA Flyer';
                
                $newsletterParent = 1056; 
                $archive='/research-alliances/northeast-rural-districts-alliance/newsletter-archive.html';    
                
            } else if (get_the_id() == 1159 || $post->post_parent == 1159) {
                
                $titletext = 'ELLA News';
                $flyertext = 'ELLA Flyer';
                
                $newsletterParent = 1159; 
                
                $archive='/research-alliances/ell-alliance/newsletter-archive.html';
            
            } else if (get_the_id() == 1128 || $post->post_parent == 1128) {
                
                $titletext = 'PR Alliance News';
                $flyertext = 'PR Flyer';
                
                $newsletterParent = 1128; 
                $archive='/research-alliances/puerto-rico-dropout-prevention-alliance/newsletter-archive.html';

            } else if (get_the_id() == 1182 || $post->post_parent == 1182) {
                
                $titletext = 'USVI News';
                $flyertext = 'USVI Flyer';
                
                $newsletterParent = 1182;
                $archive='/research-alliances/virgin-islands-college-career-readiness-alliance/newsletter-archive.html';
                
            }
            
            // Get the Custom Field PDF for newsletter
            $pdfFile = get_field('newsletter_pdf',$newsletterParent);
                        
            // Strip domain from PDF 
            $pdfPath = parse_url($pdfFile, PHP_URL_PATH);
        
            // Get the Custom Field PDF for newsletter
            $flyerFile = get_field('flyer_pdf',$newsletterParent);
                        
            // Strip domain from PDF 
            $flyerPath = parse_url($flyerFile, PHP_URL_PATH);
            
            // Display CSS hover panel widget
            echo $before_widget;

            echo '<div class="relnei-hov newswij">';  
            echo    '<h2 class="longtext">Newsletter and Flyer</h2>'; 
            echo    '<div class="mask">'; 
            echo        '<div class="wij-top">';
           
                    echo    '<p class="title">Newsletter and Flyer</p>';
                    echo    '<h3><a href="' .$pdfPath.'" class="trackNewsletter">'.$titletext.'</a></h3>';
                    echo    '<h3><a href="' .$flyerPath.'" class="trackNewsletter">'.$flyertext.'</a></h3>';

            echo        '</div>'; // wij-top end
            echo        '<div class="wij-footer">';
            echo                '<a href="'.$archive.'" class="wij-footerlink">View All</a>';
            echo        '</div>'; // wij-footer end
            echo    '</div>';  
            echo '</div>';  
            echo $after_widget;
            
		} 
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
		<p>
		</p>
		<?php 
	}

} 
// register  widget
add_action( 'widgets_init', create_function( '', 'register_widget( "alliance_newsletter" );' ) );
