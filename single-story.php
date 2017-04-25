<?php 
/*
 Template Name: Story
*/

get_header('story'); 
?>    
  
    <?php 
    /**
     *   Main WP post loop
     */
    if (have_posts()) : while (have_posts()) : the_post(); 

        /**
         *  The div id name must match 'Story' menu link location for header link
         *  animation to work.
         */
        ?>
        <div id="story-title">
            <div class="row">

                <div class="text-center">
                    <h1>PROJECT STORY</h1>
                    <h2><?php the_title(); ?></h2>
                    <?php the_content('<p>Read the rest of this page &rarr;</p>');?>
                </div>
            </div><!-- row -->
        </div><!-- story-title -->
        
        <div id="story-level-2">
            <div class="row">
                <div class="col-sm-4 col-sm-offset-2" id="left">
                    <?php
                    echo get_field('level_2_left_text');
                    ?>
                </div>
                <div class="col-sm-5 right tallerspace">
                    <?php
                    echo get_field('level_2_right_text');
                    ?>
                </div>
            </div>
        </div>

        <div id="story-longtext">
            <div class="row text-center">
                
                <hr />

                <h1>
                <?php
                    echo get_field('story_study_result_title');
                ?>
                </h1>
            </div>

            <div class="row" id="bottom-pad-firstp">
                <div class="col-sm-10 col-sm-offset-1 ">
                <p>
                <?php
                    echo get_field('story_study_result_desc');
                ?>
                </p>
                </div>
            </div>

            <div class="row" id="rowtoppad">
                <div class="col-sm-6 col-sm-offset-1">
                    
                    <?php
                    echo get_field('story_study_result_section_1');
                    ?>
                    
                </div>

                <div class="col-sm-3 col-xs-6" >
                    <?php
                    $img =  get_field('story_study_result_section_1_pic');
                    if (!empty($img)) {
                        $imgurl = $img['url'];
                        $imgalt = $img['alt'];
                        echo '<p><a href="http://www.relnei.org/publications/teacher-evaluation-lessons-early-implementation-urban.html"><img class="img-responsive" src="'.$imgurl.'" alt="'.$imgalt.'" /></a></p>';
                    }

                    ?>
                </div>

            </div>

            <div class="row">
                <div class="col-sm-10 col-sm-offset-1 ">
               
                <?php
                    echo get_field('story_study_result_section_1a');
                ?>
              
                </div>
            </div>

        </div>

        <div id="story-dataviz">
            
            <hr />
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1 text-center" >
                    <h1>
                        <?php
                        echo get_field('story_data_viz_title');
                        ?>
                    </h1>
                </div>
            </div>

            <div class="row text-center">
                
                <div class="center-block" >
                    <?php
                    echo get_field('story_data_viz');
                    ?>
                </div>
                <div class="center-block" style="width:600px;">
                    <?php
                    echo get_field('story_data_viz_description');
                    //ok to here:debug
                    ?>
                </div>
            </div>
        </div>

        <div id="story-dataviz-discussion">
            
            <hr />

            <div class="row">
                <div class="col-sm-3 col-sm-offset-1" style="font-size:28px;">
                    
                    <?php
                    echo get_field('story_study_result_section_2_left');
                    ?>
                   
                </div>

                <div class="col-sm-7 tallerspace">
                    
                    <?php
                    echo get_field('story_study_result_section_2_right');
                    ?>
                    
                </div>

            </div>
        </div>

        <div id="story-video">
            
            <div class="row">
                <div class="text-center">
                    <h1>
                    <?php
                    echo get_field('story_video_title');
                    ?>
                    </h1>

                    <hr/>

                    <p>
                    <?php
                    echo get_field('story_video_description');
                    ?>
                    </p>

                    <div class="homevid">
                        <a class="thumbhover" href="http://www.youtube.com/watch?v=7x3R-RDbW3o" rel="wp-video-lightbox" title="">
                        <span></span>
                        <img src="https://img.youtube.com/vi/7x3R-RDbW3o/maxresdefault.jpg" 
                                alt="Youtube"  />
                        </a>
                    </div> 
                </div>
            </div>
        </div>

        <div id="story-bios">
            <div class="row">

                <div class="text-center">
                    <h1>Study Authors</h1>
                </div>
                
                <div id="biobox" class="center-block col-xs-10 col-sm-8 col-md-7" > 
                    <span class="biotext">
                    <?php

                        // Check if the repeater field has rows of data. We need to first
                        // display the default bio content
                        if( have_rows('story_participants') ):

                            // loop through the rows of data
                            while ( have_rows('story_participants') ) : the_row();

                                // display the bio content value if it's the default display
                                if (get_sub_field('default_display_pic')==='yes') {

                                    // display the post_content from the Story Bio CPT object
                                    $default_biocontent = get_sub_field('story_bio_participants');
                                    echo $default_biocontent->post_content;

                                }
                                
                            endwhile;

                        else :

                            echo "No bio information found. Please try again later.";

                        endif;

                    ?>

                    </span>
                </div>

                <div id="bioarrow" class="center-block"></div>

                <div id="biopics" class="text-center">
                    <div class="col-sm-10 col-sm-offset-1">
                     <?php

                        // Check repeater field. Display images in order of selection
                        // on the story dashboard page
                        if( have_rows('story_participants') ):

                            // loop through the rows of data
                            while ( have_rows('story_participants') ) : the_row();

                                // Get the post object for bio individual
                                $biodata = get_sub_field('story_bio_participants');

                                // Get the bio page id
                                $bio_pageid = $biodata->ID;

                                // Get the default display. If 'yes' make opacity 1. 
                                $bio_default_pic_display = get_sub_field('default_display_pic');

                                // Get the featured image - returns an array - display [0]
                                $bio_image=wp_get_attachment_image_src(get_post_thumbnail_id( $bio_pageid));
                                ?>

                                <!-- 
                                    display dynamic Story Bio CPT images and make opacity 1
                                    for default display image
                                -->
                                <a href="#" id="bioimg-<?php echo $bio_pageid;?>" class="bio" <?php if ($bio_default_pic_display==='yes') {echo 'style="opacity:1"';}?>><img src="<?php echo $bio_image[0];?>"></a>

                                <?php

                            endwhile;

                        else :

                            echo "No bio information found. Please try again later.";

                        endif;
                        //ok to here, debug
                        ?>
                    </div>
                </div> 
                
            </div>
        </div>

        <div id="story-slider">
            <div class="row text-center">

                <h1>OTHER PROJECTS FROM NEERA
                </h1>
                
            </div>

            <div class="row text-center">

                <div class="col-sm-12">
                    <?php 
                    //ok to here, debug
                    ?>
                    <div class="col-xs-4 col-sm-2 col-sm-offset-1">
                        <a href="http://www.relnei.org/publications/nh-teacher-evaluation-system-study.html" ><img class="img-responsive storydocs" src="http://www.relnei.org/wp-content/uploads/2016/09/REL_2015030_cover.png"></a>
                    </div>

                    <div class=" col-xs-4 col-sm-2">
                        <a href="http://www.relnei.org/publications/school-climate-teacher-satisfaction-evaluations.html"><img class="img-responsive storydocs" src="http://www.relnei.org/wp-content/uploads/2016/09/REL_2016133_cover.png"></a>
                    </div>

                    <div class="col-xs-4 col-sm-2">
                        <a href="http://www.relnei.org/publications/program-policy-evaluation-toolkit.html" ><img class="img-responsive storydocs" src="http://www.relnei.org/wp-content/uploads/2016/09/REL_2015057_cover.png"></a>
                    </div>

                    <div class="col-xs-4 col-sm-2">
                        <a href="http://www.relnei.org/publications/slo-teacher-evaluation-systems-scan.html" ><img class="img-responsive storydocs" src="http://www.relnei.org/wp-content/uploads/2016/09/REL_2014013_cover.png"></a>
                    </div>

                    <div class="col-xs-4 col-sm-2">
                        <a href="http://www.relnei.org/publications/practitioner-data-use-toolkit.html" ><img class="img-responsive storydocs" src="http://www.relnei.org/wp-content/uploads/2016/09/REL_2015043_cover.png"></a>
                    </div>
                    
                    <div class="col-sm-1"></div>

                </div>
   
            </div>
        </div>
        
        <div id="story-social-media">
            <div class="row">
                <div class="text-center">
                    <h1>
                    REL NORTHEAST &amp; ISLANDS
                    </h1>

                    <p>
                        <a href="https://twitter.com/REL_NEI" target="_blank"  class="story-media story-media-group"><i class="fa fa-2x fa-twitter" aria-hidden="true"></i></a>
                    </p>
                </div>
            </div>
        </div>

        <?php endwhile; endif; // End WP Post loop ?>
                
        
    </div><!-- row: title area-->



</div> <!--story-title-->      

<?php 
get_footer('story'); 
?>