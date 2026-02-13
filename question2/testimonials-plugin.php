<?php
/**
 * Plugin Name: Testimonials Manager 
 * Description: Diagnostic version with inline debugging and guaranteed CSS/JS loading
 * Version: 1.0.0
 * Author: Shyam Barua
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Testimonials_Manager_Debug {
    
    const VERSION = '1.0.2';
    const POST_TYPE = 'testimonial';
    const META_PREFIX = '_testimonial_';
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
        add_shortcode( 'testimonials', array( $this, 'testimonials_shortcode' ) );
    }
    
    public function register_post_type() {
        $labels = array(
            'name'                  => 'Testimonials',
            'singular_name'         => 'Testimonial',
            'menu_name'             => 'Testimonials',
            'add_new'               => 'Add New',
            'add_new_item'          => 'Add New Testimonial',
            'edit_item'             => 'Edit Testimonial',
            'view_item'             => 'View Testimonial',
            'all_items'             => 'All Testimonials',
            'featured_image'        => 'Client Photo',
            'set_featured_image'    => 'Set client photo',
            'remove_featured_image' => 'Remove client photo',
            'use_featured_image'    => 'Use as client photo',
        );
        
        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_icon'             => 'dashicons-star-filled',
            'supports'              => array( 'title', 'editor', 'thumbnail' ),
            'show_in_rest'          => true,
        );
        
        register_post_type( self::POST_TYPE, $args );
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'testimonial_details',
            'Testimonial Details',
            array( $this, 'render_meta_box' ),
            self::POST_TYPE,
            'normal',
            'high'
        );
    }
    
    public function render_meta_box( $post ) {
        wp_nonce_field( 'testimonial_save', 'testimonial_nonce' );
        
        $client_name = get_post_meta( $post->ID, self::META_PREFIX . 'client_name', true );
        $client_position = get_post_meta( $post->ID, self::META_PREFIX . 'client_position', true );
        $company_name = get_post_meta( $post->ID, self::META_PREFIX . 'company_name', true );
        $rating = get_post_meta( $post->ID, self::META_PREFIX . 'rating', true );
        
        if ( empty( $rating ) ) {
            $rating = '5';
        }
        
        // Debug output
        echo '<div style="padding: 10px; background: #fff3cd; border: 1px solid #ffc107; margin-bottom: 15px;">';
        echo '<strong>📊 Debug Info:</strong><br>';
        echo 'Post ID: ' . $post->ID . '<br>';
        echo 'Client Name Saved: ' . ( $client_name ? 'Yes (' . esc_html( $client_name ) . ')' : 'No' ) . '<br>';
        echo 'Rating Saved: ' . ( $rating ? 'Yes (' . $rating . ' stars)' : 'No' );
        echo '</div>';
        ?>
        
        <table class="form-table">
            <tr>
                <th><label for="testimonial_client_name">Client Name <span style="color: red;">*</span></label></th>
                <td>
                    <input type="text" id="testimonial_client_name" name="testimonial_client_name" 
                           value="<?php echo esc_attr( $client_name ); ?>" class="regular-text" required>
                    <p class="description">Required field</p>
                </td>
            </tr>
            <tr>
                <th><label for="testimonial_client_position">Position/Title</label></th>
                <td>
                    <input type="text" id="testimonial_client_position" name="testimonial_client_position" 
                           value="<?php echo esc_attr( $client_position ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="testimonial_company_name">Company Name</label></th>
                <td>
                    <input type="text" id="testimonial_company_name" name="testimonial_company_name" 
                           value="<?php echo esc_attr( $company_name ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="testimonial_rating">Rating</label></th>
                <td>
                    <select id="testimonial_rating" name="testimonial_rating">
                        <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                            <option value="<?php echo $i; ?>" <?php selected( $rating, $i ); ?>>
                                <?php echo $i; ?> Star<?php echo $i > 1 ? 's' : ''; ?> 
                                <?php echo str_repeat( '★', $i ); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function save_meta_boxes( $post_id, $post ) {
        if ( self::POST_TYPE !== $post->post_type ) {
            return;
        }
        
        if ( ! isset( $_POST['testimonial_nonce'] ) || 
             ! wp_verify_nonce( $_POST['testimonial_nonce'], 'testimonial_save' ) ) {
            return;
        }
        
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        // Save with debugging
        $saved = array();
        
        if ( isset( $_POST['testimonial_client_name'] ) ) {
            $client_name = sanitize_text_field( $_POST['testimonial_client_name'] );
            update_post_meta( $post_id, self::META_PREFIX . 'client_name', $client_name );
            $saved[] = 'Client Name: ' . $client_name;
        }
        
        if ( isset( $_POST['testimonial_client_position'] ) ) {
            $client_position = sanitize_text_field( $_POST['testimonial_client_position'] );
            update_post_meta( $post_id, self::META_PREFIX . 'client_position', $client_position );
            $saved[] = 'Position: ' . $client_position;
        }
        
        if ( isset( $_POST['testimonial_company_name'] ) ) {
            $company_name = sanitize_text_field( $_POST['testimonial_company_name'] );
            update_post_meta( $post_id, self::META_PREFIX . 'company_name', $company_name );
            $saved[] = 'Company: ' . $company_name;
        }
        
        if ( isset( $_POST['testimonial_rating'] ) ) {
            $rating = absint( $_POST['testimonial_rating'] );
            if ( $rating >= 1 && $rating <= 5 ) {
                update_post_meta( $post_id, self::META_PREFIX . 'rating', $rating );
                $saved[] = 'Rating: ' . $rating;
            }
        }
        
        // Debug log (visible in PHP error log if WP_DEBUG is on)
        error_log( 'Testimonial ' . $post_id . ' saved: ' . implode( ', ', $saved ) );
    }
    
    public function testimonials_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'count'   => -1,
            'orderby' => 'date',
            'order'   => 'DESC',
        ), $atts, 'testimonials' );
        
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => intval( $atts['count'] ),
            'orderby'        => sanitize_text_field( $atts['orderby'] ),
            'order'          => strtoupper( sanitize_text_field( $atts['order'] ) ),
            'post_status'    => 'publish',
        );
        
        $query = new WP_Query( $args );
        
        if ( ! $query->have_posts() ) {
            return '<div class="testimonials-empty"><p>No testimonials found. Please add some testimonials first.</p></div>';
        }
        
        $unique_id = 'testimonials-' . uniqid();
        $total = $query->post_count;
        
        ob_start();
        ?>
        
        <!-- Debug Info (Remove in production) -->
        <div style="padding: 10px; background: #d1ecf1; border: 1px solid #0c5460; margin-bottom: 20px;">
            <strong>🔍 Shortcode Debug:</strong><br>
            Testimonials found: <?php echo $total; ?><br>
            Slider ID: <?php echo esc_html( $unique_id ); ?><br>
            Count parameter: <?php echo esc_html( $atts['count'] ); ?><br>
            <?php echo $total > 1 ? '✅ Navigation will show' : '❌ Need 2+ testimonials for navigation'; ?>
        </div>
        
        <div class="testimonials-wrapper" id="<?php echo esc_attr( $unique_id ); ?>">
            <div class="testimonials-slider">
                <?php
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $this->render_testimonial_slide( get_the_ID() );
                }
                wp_reset_postdata();
                ?>
            </div>
            
            <?php if ( $total > 1 ) : ?>
                <div class="testimonials-navigation">
                    <button class="testimonial-prev" aria-label="Previous">←</button>
                    <div class="testimonials-dots"></div>
                    <button class="testimonial-next" aria-label="Next">→</button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Inline CSS - Guaranteed to load -->
        <style>
            #<?php echo esc_attr( $unique_id ); ?> {
                max-width: 800px;
                margin: 20px auto;
                padding: 20px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonials-slider {
                position: relative;
                min-height: 300px;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-slide {
                display: none;
                text-align: center;
                padding: 40px 30px;
                background: #f8f9fa;
                border-radius: 12px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-slide.active {
                display: block;
                animation: fadeIn 0.5s ease-in-out;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-photo {
                margin: 0 auto 20px;
                width: 100px;
                height: 100px;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-photo img {
                width: 100px !important;
                height: 100px !important;
                border-radius: 50% !important;
                object-fit: cover !important;
                border: 4px solid white;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-rating {
                margin-bottom: 20px;
                font-size: 24px;
                color: #ffa500;
                line-height: 1;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-content {
                font-size: 18px;
                line-height: 1.6;
                color: #333;
                font-style: italic;
                margin-bottom: 25px;
                quotes: """ """ "'" "'";
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-content:before {
                content: open-quote;
                font-size: 48px;
                color: #ddd;
                line-height: 0;
                margin-right: 5px;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-author {
                margin-top: 25px;
                border-top: 2px solid #e9ecef;
                padding-top: 20px;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .client-name {
                font-weight: 700;
                font-size: 20px;
                color: #212529;
                margin-bottom: 8px;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .client-info {
                font-size: 16px;
                color: #6c757d;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonials-navigation {
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 20px;
                margin-top: 30px;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-prev,
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-next {
                background: #212529;
                color: white;
                border: none;
                width: 45px;
                height: 45px;
                border-radius: 50%;
                font-size: 20px;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-prev:hover,
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-next:hover {
                background: #495057;
                transform: scale(1.1);
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonials-dots {
                display: flex;
                gap: 10px;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-dot {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: #dee2e6;
                cursor: pointer;
                transition: all 0.3s;
            }
            
            #<?php echo esc_attr( $unique_id ); ?> .testimonial-dot.active {
                background: #212529;
                transform: scale(1.3);
            }
            
            @media (max-width: 768px) {
                #<?php echo esc_attr( $unique_id ); ?> {
                    padding: 10px;
                }
                #<?php echo esc_attr( $unique_id ); ?> .testimonial-slide {
                    padding: 25px 20px;
                }
                #<?php echo esc_attr( $unique_id ); ?> .testimonial-photo img {
                    width: 80px !important;
                    height: 80px !important;
                }
                #<?php echo esc_attr( $unique_id ); ?> .testimonial-content {
                    font-size: 16px;
                }
            }
        </style>
        
        <!-- Inline JavaScript - Guaranteed to load -->
        <script>
        (function() {
            var wrapper = document.getElementById('<?php echo esc_js( $unique_id ); ?>');
            if (!wrapper) {
                console.error('Testimonials wrapper not found!');
                return;
            }
            
            console.log('✅ Testimonials slider initializing...');
            
            var slides = wrapper.querySelectorAll('.testimonial-slide');
            var totalSlides = slides.length;
            
            console.log('Found ' + totalSlides + ' testimonials');
            
            if (totalSlides === 0) {
                console.error('No testimonial slides found!');
                return;
            }
            
            var currentSlide = 0;
            
            // Show first slide immediately
            slides[0].classList.add('active');
            
            if (totalSlides <= 1) {
                console.log('Only 1 testimonial, navigation disabled');
                return;
            }
            
            var dotsContainer = wrapper.querySelector('.testimonials-dots');
            var prevBtn = wrapper.querySelector('.testimonial-prev');
            var nextBtn = wrapper.querySelector('.testimonial-next');
            
            // Create dots
            for (var i = 0; i < totalSlides; i++) {
                var dot = document.createElement('span');
                dot.className = 'testimonial-dot';
                if (i === 0) dot.classList.add('active');
                dot.setAttribute('data-slide', i);
                dotsContainer.appendChild(dot);
            }
            
            var dots = wrapper.querySelectorAll('.testimonial-dot');
            
            function showSlide(n) {
                if (n >= totalSlides) currentSlide = 0;
                if (n < 0) currentSlide = totalSlides - 1;
                
                slides.forEach(function(slide) {
                    slide.classList.remove('active');
                });
                dots.forEach(function(dot) {
                    dot.classList.remove('active');
                });
                
                slides[currentSlide].classList.add('active');
                dots[currentSlide].classList.add('active');
                
                console.log('Showing slide ' + (currentSlide + 1) + ' of ' + totalSlides);
            }
            
            function nextSlide() {
                currentSlide++;
                showSlide(currentSlide);
            }
            
            function prevSlide() {
                currentSlide--;
                showSlide(currentSlide);
            }
            
            prevBtn.addEventListener('click', prevSlide);
            nextBtn.addEventListener('click', nextSlide);
            
            dots.forEach(function(dot) {
                dot.addEventListener('click', function() {
                    currentSlide = parseInt(this.getAttribute('data-slide'));
                    showSlide(currentSlide);
                });
            });
            
            // Auto-advance every 5 seconds
            setInterval(nextSlide, 5000);
            
            console.log('✅ Testimonials slider ready!');
        })();
        </script>
        
        <?php
        return ob_get_clean();
    }
    
    private function render_testimonial_slide( $post_id ) {
        $client_name = get_post_meta( $post_id, self::META_PREFIX . 'client_name', true );
        $client_position = get_post_meta( $post_id, self::META_PREFIX . 'client_position', true );
        $company_name = get_post_meta( $post_id, self::META_PREFIX . 'company_name', true );
        $rating = get_post_meta( $post_id, self::META_PREFIX . 'rating', true );
        
        if ( empty( $rating ) ) {
            $rating = 5;
        }
        ?>
        
        <div class="testimonial-slide">
            <?php if ( has_post_thumbnail( $post_id ) ) : ?>
                <div class="testimonial-photo">
                    <?php echo get_the_post_thumbnail( $post_id, 'thumbnail' ); ?>
                </div>
            <?php endif; ?>
            
            <div class="testimonial-rating">
                <?php echo str_repeat( '★', intval( $rating ) ); ?>
                <?php echo str_repeat( '☆', 5 - intval( $rating ) ); ?>
            </div>
            
            <div class="testimonial-content">
                <?php echo get_the_content( null, false, $post_id ); ?>
            </div>
            
            <div class="testimonial-author">
                <?php if ( ! empty( $client_name ) ) : ?>
                    <div class="client-name">
                        <?php echo esc_html( $client_name ); ?>
                    </div>
                <?php else : ?>
                    <div class="client-name" style="color: red;">
                        ⚠️ Client Name Missing - Please edit testimonial
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $client_position ) || ! empty( $company_name ) ) : ?>
                    <div class="client-info">
                        <?php
                        $info = array();
                        if ( ! empty( $client_position ) ) {
                            $info[] = esc_html( $client_position );
                        }
                        if ( ! empty( $company_name ) ) {
                            $info[] = esc_html( $company_name );
                        }
                        echo implode( ' at ', $info );
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
    }
}

function testimonials_manager_debug_init() {
    return Testimonials_Manager_Debug::get_instance();
}
add_action( 'plugins_loaded', 'testimonials_manager_debug_init' );

function testimonials_manager_debug_activate() {
    $plugin = Testimonials_Manager_Debug::get_instance();
    $plugin->register_post_type();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'testimonials_manager_debug_activate' );

function testimonials_manager_debug_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'testimonials_manager_debug_deactivate' );