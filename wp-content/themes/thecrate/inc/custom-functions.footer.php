<?php
/**
CUSTOM FOOTER FUNCTIONS
*/


// SITE PRELOADER ANIMATION: From theme options panel
if (!function_exists('thecrate_loader_animation')) {
    function thecrate_loader_animation(){

        global  $thecrate_redux;
        
        $html = '';

        if (isset($thecrate_redux['thecrate_preloader_animation'])) {
            if ($thecrate_redux['thecrate_preloader_animation'] == 'v1_ball_triangle') {
                $html .= '<div class="thecrate_preloader v1_ball_triangle">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-triangle-path">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v2_ball_pulse'){
                $html .= '<div class="thecrate_preloader v2_ball_pulse">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-pulse">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v3_ball_grid_pulse'){
                $html .= '<div class="thecrate_preloader v3_ball_grid_pulse">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-grid-pulse">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v4_ball_clip_rotate'){
                $html .= '<div class="thecrate_preloader v4_ball_clip_rotate">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-clip-rotate">
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v5_ball_clip_rotate_pulse'){
                $html .= '<div class="thecrate_preloader v5_ball_clip_rotate_pulse">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-clip-rotate-pulse">
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v6_square_spin'){
                $html .= '<div class="thecrate_preloader v6_square_spin">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner square-spin">
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v7_ball_clip_rotate_multiple'){
                $html .= '<div class="thecrate_preloader v7_ball_clip_rotate_multiple">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-clip-rotate-multiple">
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v8_ball_pulse_rise'){
                $html .= '<div class="thecrate_preloader v8_ball_pulse_rise">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-pulse-rise">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v9_ball_rotate'){
                $html .= '<div class="thecrate_preloader v9_ball_rotate">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-rotate">
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v10_cube_transition'){
                $html .= '<div class="thecrate_preloader v10_cube_transition">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner cube-transition">
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v11_ball_zig_zag'){
                $html .= '<div class="thecrate_preloader v11_ball_zig_zag">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-zig-zag">
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v12_ball_zig_zag_deflect'){
                $html .= '<div class="thecrate_preloader v12_ball_zig_zag_deflect">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-zig-zag-deflect">
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v13_ball_scale'){
                $html .= '<div class="thecrate_preloader v13_ball_scale">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-scale">
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v14_line_scale'){
                $html .= '<div class="thecrate_preloader v14_line_scale">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner line-scale">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v15_line_scale_party'){
                $html .= '<div class="thecrate_preloader v15_line_scale_party">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner line-scale-party">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v16_ball_scale_multiple'){
                $html .= '<div class="thecrate_preloader v16_ball_scale_multiple">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-scale-multiple">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v17_ball_pulse_sync'){
                $html .= '<div class="thecrate_preloader v17_ball_pulse_sync">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-pulse-sync">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v18_ball_beat'){
                $html .= '<div class="thecrate_preloader v18_ball_beat">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-beat">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v19_line_scale_pulse_out'){
                $html .= '<div class="thecrate_preloader v19_line_scale_pulse_out">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner line-scale-pulse-out">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }elseif($thecrate_redux['thecrate_preloader_animation'] == 'v20_line_scale_pulse_out_rapid'){
                $html .= '<div class="thecrate_preloader v20_line_scale_pulse_out_rapid">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner line-scale-pulse-out-rapid">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>';
            }else{
                $html .= '<div class="thecrate_preloader v16_ball_scale_multiple">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-scale-multiple">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>'; 
            }
        }else{
            $html .= '<div class="thecrate_preloader v16_ball_scale_multiple">
                            <div class="loaders">
                                <div class="loader">
                                    <div class="loader-inner ball-scale-multiple">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>
                                </div>
                            </div>
                        </div>'; 
        }

        return $html;
    }
}


/**
Function name: 				thecrate_footer_row1()
Function description:		Footer row 1
*/
if (!function_exists('thecrate_footer_row1')) {
    function thecrate_footer_row1(){

    	global  $thecrate_redux;
            echo '<div class="row">';
                echo '<div class="col-md-12 footer-row-1">';
                    echo '<div class="row">';

                    $footer_row_1_layout = $thecrate_redux['thecrate_footer_row_1_layout'];
                    $nr = array("1", "2", "3", "4", "6");

                    if (in_array($footer_row_1_layout, $nr)) {
                        $columns    = 12/$footer_row_1_layout;
                        $class = 'col-md-'.esc_html($columns);
                        for ( $i=1; $i <= $footer_row_1_layout ; $i++ ) { 

                            echo '<div class="'.esc_attr($class).' sidebar-'.esc_attr($i).'">';
                                dynamic_sidebar( 'footer_row_1_'.esc_attr($i) );
                            echo '</div>';
                        }
                    }elseif($footer_row_1_layout == '5'){
                        echo '<div class="col-md-2 col-md-offset-1 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';

                        echo '<div class="col-md-2 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';

                        echo '<div class="col-md-2 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';

                        echo '<div class="col-md-2 sidebar-4">';
                            dynamic_sidebar( 'footer_row_1_4' );
                        echo '</div>';

                        echo '<div class="col-md-2 sidebar-5">';
                            dynamic_sidebar( 'footer_row_1_5' );
                        echo '</div>';
                    }elseif($footer_row_1_layout == 'column_half_sub_half'){
                        echo '<div class="col-md-6 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';

                        echo '<div class="col-md-3 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';

                        echo '<div class="col-md-3 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';
                    }elseif($footer_row_1_layout == 'column_sub_half_half'){
                        echo '<div class="col-md-3 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';

                        echo '<div class="col-md-3 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';

                        echo '<div class="col-md-6 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';
                    }elseif($footer_row_1_layout == 'column_sub_fourth_third'){
                        echo '<div class="col-md-2 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-4">';
                            dynamic_sidebar( 'footer_row_1_4' );
                        echo '</div>';
                        echo '<div class="col-md-4 sidebar-5">';
                            dynamic_sidebar( 'footer_row_1_5' );
                        echo '</div>';
                    }elseif($footer_row_1_layout == 'column_third_sub_fourth'){
                        echo '<div class="col-md-4 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-4">';
                            dynamic_sidebar( 'footer_row_1_4' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-5">';
                            dynamic_sidebar( 'footer_row_1_5' );
                        echo '</div>';
                    }elseif($footer_row_1_layout == 'column_sub_third_half'){
                        echo '<div class="col-md-2 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';
                        echo '<div class="col-md-6 sidebar-4">';
                            dynamic_sidebar( 'footer_row_1_4' );
                        echo '</div>';
                    }elseif($footer_row_1_layout == 'column_half_sub_third'){
                        echo '<div class="col-md-6 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-4">';
                            dynamic_sidebar( 'footer_row_1_4' );
                        echo '</div>';
                    }elseif($footer_row_1_layout == 'column_four_two_two_four'){
                        echo '<div class="col-md-4 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';
                        echo '<div class="col-md-4 sidebar-4">';
                            dynamic_sidebar( 'footer_row_1_4' );
                        echo '</div>';
                    }elseif($footer_row_1_layout == 'column_3_2_2_2_3'){
                        echo '<div class="col-md-3 sidebar-1">';
                            dynamic_sidebar( 'footer_row_1_1' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-2">';
                            dynamic_sidebar( 'footer_row_1_2' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_3' );
                        echo '</div>';
                        echo '<div class="col-md-2 sidebar-3">';
                            dynamic_sidebar( 'footer_row_1_4' );
                        echo '</div>';
                        echo '<div class="col-md-3 sidebar-4">';
                            dynamic_sidebar( 'footer_row_1_5' );
                        echo '</div>';
                    }
                    echo '</div>';
                echo '</div>';
            echo '</div>';
    }
}