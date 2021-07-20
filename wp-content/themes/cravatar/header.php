<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="profile" href="https://gmpg.org/xfn/11">
	<title><?php wp_title(); ?></title>
	<?php wp_head(); ?>
	<?php ?>
</head>

<body <?php body_class(); ?>>

<?php
wp_body_open();
?>

<header id="site-header" class="navbar navbar-expand-md transparent wp-nav" role="banner">
    <div class="container-fluid container-xxl">
        <a class="navbar-brand" href="/">
            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="227.007" height="50.184" viewBox="0 0 300 69">
                <defs>
                    <style>
                        .cls-1 {
                            filter: url(#filter);
                        }

                        .cls-2 {
                            font-size: 30px;
                            fill: #fff;
                            text-anchor: middle;
                            font-family: "Gen Jyuu Gothic";
                            font-weight: 500;
                            text-transform: uppercase;
                        }
                    </style>
                    <filter id="filter" filterUnits="userSpaceOnUse">
                        <feImage preserveAspectRatio="none" x="0" y="0" width="300" height="69" result="image" xlink:href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMjc1IiBoZWlnaHQ9IjY5IiB2aWV3Qm94PSIwIDAgMjc1IDY5Ij4KICA8ZGVmcz4KICAgIDxzdHlsZT4KICAgICAgLmNscy0xIHsKICAgICAgICBmaWxsOiB1cmwoI2xpbmVhci1ncmFkaWVudCk7CiAgICAgIH0KICAgIDwvc3R5bGU+CiAgICA8bGluZWFyR3JhZGllbnQgaWQ9ImxpbmVhci1ncmFkaWVudCIgeDE9IjU2LjIyMyIgeDI9IjIxOC43NzciIHkyPSI2OSIgZ3JhZGllbnRVbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8c3RvcCBvZmZzZXQ9IjAiIHN0b3AtY29sb3I9IiM0ZDFjYjgiLz4KICAgICAgPHN0b3Agb2Zmc2V0PSIxIiBzdG9wLWNvbG9yPSIjMDI3NGJlIi8+CiAgICA8L2xpbmVhckdyYWRpZW50PgogIDwvZGVmcz4KICA8cmVjdCBjbGFzcz0iY2xzLTEiIHdpZHRoPSIyNzUiIGhlaWdodD0iNjkiLz4KPC9zdmc+Cg=="/>
                        <feComposite result="composite" operator="in" in2="SourceGraphic"/>
                        <feBlend result="blend" in2="SourceGraphic"/>
                    </filter>
                </defs>
                <g id="LOGO" class="cls-1">
                    <text id="CRAVATAR" class="cls-2" transform="translate(175.258 49.996) scale(1.481)">CRAVATAR</text>
                    <image id="gravatar" width="54" height="69" xlink:href="data:img/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyCAYAAAAeP4ixAAAESUlEQVRogc2a34tVVRTHv/tWhtHoFGQEhlFZ1ENNlsFYSAw9TA1BEEKjhP0DIURS+Vgv0UOQ9BIMRT8oKUioIKLQjBCasEyFpkaJXkKswBnLaMr5xBr2peu9+9yz9rnn3un7OLPWd+3v/rH22uvcoD4AuEDSg5LukTQk6Q9Jv0v6SdKMpKMhhF/6Ebs2AGuBQ5TjGLAbuAvoy4RWBnARcMQhoh0/AjuBS/8vQrZXENGK08DTwMXLLeSNHoU0cRy4Nyd2o2YtV9XEc52kT4DngRUeh1IhwM3AK8B3wCzwHjBWYH5h9pC7Y6ekT4HLemIBJoGFguV/JmH/WU1bqx02iWuringAOFcS4KEBCTHMAFfkirgdOOsg/3KAQgzfACtTY+44I8BqSe9ISjq04Y54iw8KI5JedgmR9KKka50Da1TMfI9L+qii+EeAR7taAHdnLvWRNn/v1hqJ9iPAFxW22G/Ala2xGy2DsHpnd+bs5NqfhxDCYUmbJT0l6VyG6+WSnkv+B7gvc1b2tBd7uSuSiO9JME38A1yfErI/g2Rf6sbtRUj0H+tyb6XwajvBNRnOPwNrCgbSk5DIkVN42gquUssZ2VpEnMCOEMKpDPsshBBek7TH6WNXxGSrkPudjl+FEN7tl4gW7IivSg8mzKYBXCLpTqfTC30WsIS44t6MOLZ0XoFNzv04X1QeNAEcdnJZYnkTeAwYLuC6Glh08t2Wc7g+LJuaWNjl4hSwuYDvoJNrm52R9c4lnHba5cIq2vdtBRJ+B5xc603IKqfxbH90LMEK1ScTf59x+g/nCJlz2Cw6uVJIvTq9va/hut/s3pSZgndCk2g4Z1px+ctwtIex/FAxpmHOhMw7jW9w2PRyz7xeMaZh3oSccBpvLDMIIRyT9JKTrxUfFAgpjRlxwnL1qDNXl16ITQBPOEvyOeBZa7UmOFbGmB6MhliinJbUQZbA1hDC285ZssHcIummyP9X279/tXMRQlgo8LVi8C1HmL8tazWdDjiVH/KK6BXOjr5h6dJspt+PnXE3AA8PQITF2OA0/2/swLqMAu1k0cOqJhFrYgwPbMzr2gn2OZ2J1auruZwpYkXukztFMp5BYNhbp5goYm/mGMaLyKYzifbXsc3idspZCUNxNZ5xp7TiZC8JwHwzzkQrRsuIpyqQEtPlpOfSjJfdZEaKbcdUO2fH11TAPid/Lamz+eXDGUmfW6MiFoLNonR1rJ02xu7iUEX+45aaQwhnugqJYqzvdNDZkR8k/pS0KbZaz0PyPRINt2T2Y/sNG8uWlIhS2KM+9liXGzaGbT1NFDCR2VyuGxZ7opbVjt8xZpdBxGy3PnFVMUM9pOYqmIoZtD+Il2ZuBZCD6dLLruYVGo+Fprdq7obFyJWunQYkyJ4Au+LjLOcjzUL02dVRildArb+Tis/mWyXdGH9PYr2qZkvHbnjr2Fiz43tJ34YQztYSWNK/uWw6fro/dYkAAAAASUVORK5CYII="/>
                </g>
            </svg>
            <?php
/*            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $logo           = wp_get_attachment_image_src( $custom_logo_id, 'full' );
            if ( has_custom_logo() ) {
                echo '<img src="' . esc_url( $logo[0] ) . '" alt="' . get_bloginfo( 'name' ) . '" width="' . get_theme_mod( 'lpcn_sections_logo_range', '200' ) . '" >';
            } else {
                echo '<h1>' . esc_attr( get_bloginfo( 'name' ) ) . '</h1>';
            } */?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
                aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <section>
                <nav>
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'primary_menu',
                            'container'      => false,
                            'items_wrap'     => '<ul class="navbar-nav  %2$s">%3$s</ul>',
                            'fallback_cb'    => false,
                            'walker'         => new Wp_Sub_Menu(),
                        )
                    );
                    ?>


                </nav>

                <div class=" header-sign">

                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'register_menu',
                            'container'      => false,
                            'items_wrap'     => '<ul class="navbar-nav  %2$s">%3$s</ul>',
                            'fallback_cb'    => false,
                            'walker'         => new Wp_Sub_Menu(),
                        )
                    );
                    ?>

                </div>
            </section>
        </div>
    </div>
</header>


