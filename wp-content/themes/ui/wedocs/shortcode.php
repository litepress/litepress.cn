<?php if ( $docs ) { ?>
   <!-- --><?php /*wedocs_breadcrumbs(); */?>
        <style>
            .breadcrumb{
                display: none;
            }
        </style>
<div class="wedocs-shortcode-wrap">
    <ul class="wedocs-docs-list col-<?php echo $col; ?>">

        <?php foreach ( $docs as $main_doc ) { ?>
            <li class="wedocs-docs-single">

                <div class="card theme-boxshadow">


                        <div class="project-top row">
                            <div class="col-5 center">
                                <a href="<?php echo get_permalink( $main_doc['doc']->ID ); ?>">
                                    <div class="docs-placeholder">
                                        <?php echo $main_doc['doc']->post_title; ?>
                                    </div>
                                </a>
                              <!--  <img src="https://cdn.learnku.com/uploads/images/201802/28/1/Jk8mC7SGI5.jpg!/both/300x300">--></div>
                            <div class="col-7">
                                <h5 class="card-title"><a href="<?php echo get_permalink( $main_doc['doc']->ID ); ?>"><?php echo $main_doc['doc']->post_title; ?></a></h5>
                                <?php if ( $main_doc['sections'] ) { ?>

                                    <div class="inside card-text">
                                        <ul class="wedocs-doc-sections">
                                            <?php foreach ( $main_doc['sections'] as $section ) { ?>
                                                <li><a href="<?php echo get_permalink( $section->ID ); ?>"><?php echo $section->post_title; ?></a></li>
                                            <?php } ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                                <a href="<?php echo get_permalink( $main_doc['doc']->ID ); ?>" class="btn btn-primary" one-link-mark="yes"><i class="fas fa-search"></i><?php echo $more; ?></a>

                            </div>
                        </div>






                </div>








            </li>
        <?php } ?>
    </ul>
</div>
<?php }
