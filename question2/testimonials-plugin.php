<?php
/**
 * Plugin Name: Testimonials Manager
 * Plugin URI: https://example.com/testimonials-manager
 * Description: A complete testimonials management system with custom post type, meta boxes, and frontend display shortcode.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: testimonials-manager
 * Domain Path: /languages
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main Testimonials Manager Class
 */
class Testimonials_Manager {
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.0';
    
    /**
     * Post type slug
     */
    const POST_TYPE = 'testimonial';
    
    /**
     * Meta key prefix
     */
    const META_PREFIX = '_testimonial_';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register custom post type
        add_action( 'init', array( $this, 'register_post_type' ) );
        
        // Add meta boxes
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        
        // Save meta box data
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 10, 2 );
        
        // Register shortcode
        add_shortcode( 'testimonials', array( $this, 'testimonials_shortcode' ) );
        
        // Enqueue frontend styles and scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        
        // Enqueue admin styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }
    
    /**
     * Register custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x( 'Testimonials', 'Post type general name', 'testimonials-manager' ),
            'singular_name'         => _x( 'Testimonial', 'Post type singular name', 'testimonials-manager' ),
            'menu_name'             => _x( 'Testimonials', 'Admin Menu text', 'testimonials-manager' ),
            'name_admin_bar'        => _x( 'Testimonial', 'Add New on Toolbar', 'testimonials-manager' ),
            'add_new'               => __( 'Add New', 'testimonials-manager' ),
            'add_new_item'          => __( 'Add New Testimonial', 'testimonials-manager' ),
            'new_item'              => __( 'New Testimonial', 'testimonials-manager' ),
            'edit_item'             => __( 'Edit Testimonial', 'testimonials-manager' ),
            'view_item'             => __( 'View Testimonial', 'testimonials-manager' ),
            'all_items'             => __( 'All Testimonials', 'testimonials-manager' ),
            'search_items'          => __( 'Search Testimonials', 'testimonials-manager' ),
            'parent_item_colon'     => __( 'Parent Testimonials:', 'testimonials-manager' ),
            'not_found'             => __( 'No testimonials found.', 'testimonials-manager' ),
            'not_found_in_trash'    => __( 'No testimonials found in Trash.', 'testimonials-manager' ),
            'featured_image'        => _x( 'Client Photo', 'Overrides the "Featured Image" phrase', 'testimonials-manager' ),
            'set_featured_image'    => _x( 'Set client photo', 'Overrides the "Set featured image" phrase', 'testimonials-manager' ),
            'remove_featured_image' => _x( 'Remove client photo', 'Overrides the "Remove featured image" phrase', 'testimonials-manager' ),
            'use_featured_image'    => _x( 'Use as client photo', 'Overrides the "Use as featured image" phrase', 'testimonials-manager' ),
            'archives'              => _x( 'Testimonial archives', 'The post type archive label used in nav menus', 'testimonials-manager' ),
            'insert_into_item'      => _x( 'Insert into testimonial', 'Overrides the "Insert into post" phrase', 'testimonials-manager' ),
            'uploaded_to_this_item' => _x( 'Uploaded to this testimonial', 'Overrides the "Uploaded to this post" phrase', 'testimonials-manager' ),
            'filter_items_list'     => _x( 'Filter testimonials list', 'Screen reader text for the filter links', 'testimonials-manager' ),
            'items_list_navigation' => _x( 'Testimonials list navigation', 'Screen reader text for the pagination', 'testimonials-manager' ),
            'items_list'            => _x( 'Testimonials list', 'Screen reader text for the items list', 'testimonials-manager' ),
        );
        
        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'publicly_queryable'    => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'testimonial' ),
            'capability_type'       => 'post',
            'has_archive'           => true,
            'hierarchical'          => false,
            'menu_position'         => 20,
            'menu_icon'             => 'dashicons-star-filled',
            'supports'              => array( 'title', 'editor', 'thumbnail' ),
            'show_in_rest'          => true, // Gutenberg support
        );
        
        register_post_type( self::POST_TYPE, $args );
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'testimonial_details',
            __( 'Testimonial Details', 'testimonials-manager' ),
            array( $this, 'render_meta_box' ),
            self::POST_TYPE,
            'normal',
            'high'
        );
    }
    
    /**
     * Render meta box content
     */
    public function render_meta_box( $post ) {
        // Add nonce for security
        wp_nonce_field( 'testimonial_meta_box', 'testimonial_meta_box_nonce' );
        
        // Get existing values
        $client_name = get_post_meta( $post->ID, self::META_PREFIX . 'client_name', true );
        $client_position = get_post_meta( $post->ID, self::META_PREFIX . 'client_position', true );
        $company_name = get_post_meta( $post->ID, self::META_PREFIX . 'company_name', true );
        $rating = get_post_meta( $post->ID, self::META_PREFIX . 'rating', true );
        
        // Set default rating if empty
        if ( empty( $rating ) ) {
            $rating = '5';
        }
        ?>
        
        <div class="testimonial-meta-box">
            <style>
                .testimonial-meta-box {
                    padding: 10px 0;
                }
                .testimonial-field {
                    margin-bottom: 20px;
                }
                .testimonial-field label {
                    display: block;
                    font-weight: 600;
                    margin-bottom: 5px;
                }
                .testimonial-field input[type="text"],
                .testimonial-field select {
                    width: 100%;
                    max-width: 500px;
                    padding: 8px;
                    font-size: 14px;
                }
                .testimonial-field .required {
                    color: #dc3232;
                }
                .testimonial-field .description {
                    font-style: italic;
                    color: #666;
                    font-size: 13px;
                    margin-top: 5px;
                }
            </style>
            
            <div class="testimonial-field">
                <label for="testimonial_client_name">
                    <?php esc_html_e( 'Client Name', 'testimonials-manager' ); ?>
                    <span class="required">*</span>
                </label>
                <input 
                    type="text" 
                    id="testimonial_client_name" 
                    name="testimonial_client_name" 
                    value="<?php echo esc_attr( $client_name ); ?>" 
                    required
                />
                <p class="description">
                    <?php esc_html_e( 'Enter the client\'s full name (required).', 'testimonials-manager' ); ?>
                </p>
            </div>
            
            <div class="testimonial-field">
                <label for="testimonial_client_position">
                    <?php esc_html_e( 'Client Position/Title', 'testimonials-manager' ); ?>
                </label>
                <input 
                    type="text" 
                    id="testimonial_client_position" 
                    name="testimonial_client_position" 
                    value="<?php echo esc_attr( $client_position ); ?>" 
                />
                <p class="description">
                    <?php esc_html_e( 'Enter the client\'s job title or position.', 'testimonials-manager' ); ?>
                </p>
            </div>
            
            <div class="testimonial-field">
                <label for="testimonial_company_name">
                    <?php esc_html_e( 'Company Name', 'testimonials-manager' ); ?>
                </label>
                <input 
                    type="text" 
                    id="testimonial_company_name" 
                    name="testimonial_company_name" 
                    value="<?php echo esc_attr( $company_name ); ?>" 
                />
                <p class="description">
                    <?php esc_html_e( 'Enter the client\'s company name.', 'testimonials-manager' ); ?>
                </p>
            </div>
            
            <div class="testimonial-field">
                <label for="testimonial_rating">
                    <?php esc_html_e( 'Rating', 'testimonials-manager' ); ?>
                </label>
                <select id="testimonial_rating" name="testimonial_rating">
                    <option value="1" <?php selected( $rating, '1' ); ?>>
                        <?php esc_html_e( '1 Star', 'testimonials-manager' ); ?>
                    </option>
                    <option value="2" <?php selected( $rating, '2' ); ?>>
                        <?php esc_html_e( '2 Stars', 'testimonials-manager' ); ?>
                    </option>
                    <option value="3" <?php selected( $rating, '3' ); ?>>
                        <?php esc_html_e( '3 Stars', 'testimonials-manager' ); ?>
                    </option>
                    <option value="4" <?php selected( $rating, '4' ); ?>>
                        <?php esc_html_e( '4 Stars', 'testimonials-manager' ); ?>
                    </option>
                    <option value="5" <?php selected( $rating, '5' ); ?>>
                        <?php esc_html_e( '5 Stars', 'testimonials-manager' ); ?>
                    </option>
                </select>
                <p class="description">
                    <?php esc_html_e( 'Select the rating for this testimonial.', 'testimonials-manager' ); ?>
                </p>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Save meta box data
     */
    public function save_meta_boxes( $post_id, $post ) {
        // Check if this is the correct post type
        if ( self::POST_TYPE !== $post->post_type ) {
            return;
        }
        
        // Verify nonce
        if ( ! isset( $_POST['testimonial_meta_box_nonce'] ) || 
             ! wp_verify_nonce( $_POST['testimonial_meta_box_nonce'], 'testimonial_meta_box' ) ) {
            return;
        }
        
        // Check if user has permission to edit
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // Don't save on autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        
        // Save client name (required)
        if ( isset( $_POST['testimonial_client_name'] ) ) {
            $client_name = sanitize_text_field( $_POST['testimonial_client_name'] );
            update_post_meta( $post_id, self::META_PREFIX . 'client_name', $client_name );
        }
        
        // Save client position
        if ( isset( $_POST['testimonial_client_position'] ) ) {
            $client_position = sanitize_text_field( $_POST['testimonial_client_position'] );
            update_post_meta( $post_id, self::META_PREFIX . 'client_position', $client_position );
        }
        
        // Save company name
        if ( isset( $_POST['testimonial_company_name'] ) ) {
            $company_name = sanitize_text_field( $_POST['testimonial_company_name'] );
            update_post_meta( $post_id, self::META_PREFIX . 'company_name', $company_name );
        }
        
        // Save rating
        if ( isset( $_POST['testimonial_rating'] ) ) {
            $rating = absint( $_POST['testimonial_rating'] );
            // Ensure rating is between 1 and 5
            if ( $rating >= 1 && $rating <= 5 ) {
                update_post_meta( $post_id, self::META_PREFIX . 'rating', $rating );
            }
        }
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only enqueue if shortcode is present on the page
        global $post;
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'testimonials' ) ) {
            wp_enqueue_style( 
                'testimonials-manager-style', 
                plugin_dir_url( __FILE__ ) . 'assets/css/testimonials.css',
                array(), 
                self::VERSION 
            );
            
            wp_enqueue_script( 
                'testimonials-manager-script', 
                plugin_dir_url( __FILE__ ) . 'assets/js/testimonials.js',
                array( 'jquery' ), 
                self::VERSION, 
                true 
            );
        }
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on testimonial edit pages
        global $post_type;
        if ( self::POST_TYPE === $post_type && ( 'post.php' === $hook || 'post-new.php' === $hook ) ) {
            // Admin styles are inline in the meta box for simplicity
        }
    }
    
    /**
     * Testimonials shortcode
     */
    public function testimonials_shortcode( $atts ) {
        // Parse shortcode attributes
        $atts = shortcode_atts( array(
            'count'   => -1,
            'orderby' => 'date',
            'order'   => 'DESC',
        ), $atts, 'testimonials' );
        
        // Sanitize attributes
        $count = intval( $atts['count'] );
        $orderby = sanitize_text_field( $atts['orderby'] );
        $order = strtoupper( sanitize_text_field( $atts['order'] ) );
        
        // Validate order
        if ( ! in_array( $order, array( 'ASC', 'DESC' ) ) ) {
            $order = 'DESC';
        }
        
        // Query testimonials
        $args = array(
            'post_type'      => self::POST_TYPE,
            'posts_per_page' => $count,
            'orderby'        => $orderby,
            'order'          => $order,
            'post_status'    => 'publish',
        );
        
        $testimonials_query = new WP_Query( $args );
        
        // Start output buffering
        ob_start();
        
        if ( $testimonials_query->have_posts() ) {
            ?>
            <div class="testimonials-slider-wrapper">
                <div class="testimonials-slider">
                    <?php
                    while ( $testimonials_query->have_posts() ) {
                        $testimonials_query->the_post();
                        $this->render_testimonial_slide();
                    }
                    ?>
                </div>
                
                <?php if ( $testimonials_query->post_count > 1 ) : ?>
                    <div class="testimonials-navigation">
                        <button class="testimonial-prev" aria-label="<?php esc_attr_e( 'Previous testimonial', 'testimonials-manager' ); ?>">
                            <span>&larr;</span>
                        </button>
                        <div class="testimonials-dots"></div>
                        <button class="testimonial-next" aria-label="<?php esc_attr_e( 'Next testimonial', 'testimonials-manager' ); ?>">
                            <span>&rarr;</span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            
            <style>
                .testimonials-slider-wrapper {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                }
                
                .testimonials-slider {
                    position: relative;
                    overflow: hidden;
                }
                
                .testimonial-slide {
                    display: none;
                    text-align: center;
                    padding: 30px;
                    background: #f9f9f9;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                
                .testimonial-slide.active {
                    display: block;
                    animation: fadeIn 0.5s ease-in-out;
                }
                
                @keyframes fadeIn {
                    from {
                        opacity: 0;
                        transform: translateY(10px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
                
                .testimonial-photo {
                    margin-bottom: 20px;
                }
                
                .testimonial-photo img {
                    width: 100px;
                    height: 100px;
                    border-radius: 50%;
                    object-fit: cover;
                    border: 3px solid #fff;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                }
                
                .testimonial-rating {
                    margin-bottom: 15px;
                    font-size: 20px;
                    color: #ffa500;
                }
                
                .testimonial-rating .star {
                    display: inline-block;
                    margin: 0 2px;
                }
                
                .testimonial-rating .star.filled:before {
                    content: "★";
                }
                
                .testimonial-rating .star.empty:before {
                    content: "☆";
                }
                
                .testimonial-content {
                    font-size: 16px;
                    line-height: 1.6;
                    color: #333;
                    margin-bottom: 20px;
                    font-style: italic;
                }
                
                .testimonial-author {
                    margin-top: 20px;
                }
                
                .testimonial-author .client-name {
                    font-weight: 700;
                    font-size: 18px;
                    color: #222;
                    margin-bottom: 5px;
                }
                
                .testimonial-author .client-info {
                    font-size: 14px;
                    color: #666;
                }
                
                .testimonials-navigation {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    margin-top: 30px;
                    gap: 20px;
                }
                
                .testimonial-prev,
                .testimonial-next {
                    background: #333;
                    color: #fff;
                    border: none;
                    width: 40px;
                    height: 40px;
                    border-radius: 50%;
                    cursor: pointer;
                    font-size: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: background 0.3s ease;
                }
                
                .testimonial-prev:hover,
                .testimonial-next:hover {
                    background: #555;
                }
                
                .testimonials-dots {
                    display: flex;
                    gap: 8px;
                }
                
                .testimonial-dot {
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background: #ccc;
                    cursor: pointer;
                    transition: background 0.3s ease;
                }
                
                .testimonial-dot.active {
                    background: #333;
                }
                
                /* Responsive Design */
                @media (max-width: 768px) {
                    .testimonials-slider-wrapper {
                        padding: 10px;
                    }
                    
                    .testimonial-slide {
                        padding: 20px;
                    }
                    
                    .testimonial-photo img {
                        width: 80px;
                        height: 80px;
                    }
                    
                    .testimonial-content {
                        font-size: 14px;
                    }
                    
                    .testimonial-author .client-name {
                        font-size: 16px;
                    }
                    
                    .testimonial-prev,
                    .testimonial-next {
                        width: 35px;
                        height: 35px;
                        font-size: 18px;
                    }
                }
                
                @media (max-width: 480px) {
                    .testimonial-slide {
                        padding: 15px;
                    }
                    
                    .testimonial-photo img {
                        width: 60px;
                        height: 60px;
                    }
                    
                    .testimonial-rating {
                        font-size: 16px;
                    }
                    
                    .testimonial-content {
                        font-size: 13px;
                    }
                }
            </style>
            
            <script>
                (function() {
                    var currentSlide = 0;
                    var slides = document.querySelectorAll('.testimonial-slide');
                    var totalSlides = slides.length;
                    
                    if (totalSlides <= 1) return;
                    
                    var dotsContainer = document.querySelector('.testimonials-dots');
                    var prevBtn = document.querySelector('.testimonial-prev');
                    var nextBtn = document.querySelector('.testimonial-next');
                    
                    // Create dots
                    for (var i = 0; i < totalSlides; i++) {
                        var dot = document.createElement('span');
                        dot.className = 'testimonial-dot';
                        dot.setAttribute('data-slide', i);
                        dotsContainer.appendChild(dot);
                    }
                    
                    var dots = document.querySelectorAll('.testimonial-dot');
                    
                    function showSlide(n) {
                        if (n >= totalSlides) currentSlide = 0;
                        if (n < 0) currentSlide = totalSlides - 1;
                        
                        for (var i = 0; i < slides.length; i++) {
                            slides[i].classList.remove('active');
                            dots[i].classList.remove('active');
                        }
                        
                        slides[currentSlide].classList.add('active');
                        dots[currentSlide].classList.add('active');
                    }
                    
                    function nextSlide() {
                        currentSlide++;
                        showSlide(currentSlide);
                    }
                    
                    function prevSlide() {
                        currentSlide--;
                        showSlide(currentSlide);
                    }
                    
                    // Event listeners
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
                    
                    // Show first slide
                    showSlide(0);
                })();
            </script>
            <?php
        } else {
            ?>
            <div class="testimonials-empty">
                <p><?php esc_html_e( 'No testimonials found.', 'testimonials-manager' ); ?></p>
            </div>
            <?php
        }
        
        // Reset post data
        wp_reset_postdata();
        
        // Return output buffer
        return ob_get_clean();
    }
    
    /**
     * Render individual testimonial slide
     */
    private function render_testimonial_slide() {
        $post_id = get_the_ID();
        $client_name = get_post_meta( $post_id, self::META_PREFIX . 'client_name', true );
        $client_position = get_post_meta( $post_id, self::META_PREFIX . 'client_position', true );
        $company_name = get_post_meta( $post_id, self::META_PREFIX . 'company_name', true );
        $rating = get_post_meta( $post_id, self::META_PREFIX . 'rating', true );
        
        // Default rating if not set
        if ( empty( $rating ) ) {
            $rating = 5;
        }
        ?>
        
        <div class="testimonial-slide">
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="testimonial-photo">
                    <?php the_post_thumbnail( 'thumbnail', array( 'alt' => esc_attr( $client_name ) ) ); ?>
                </div>
            <?php endif; ?>
            
            <div class="testimonial-rating">
                <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                    <span class="star <?php echo ( $i <= $rating ) ? 'filled' : 'empty'; ?>"></span>
                <?php endfor; ?>
            </div>
            
            <div class="testimonial-content">
                <?php the_content(); ?>
            </div>
            
            <div class="testimonial-author">
                <?php if ( ! empty( $client_name ) ) : ?>
                    <div class="client-name">
                        <?php echo esc_html( $client_name ); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ( ! empty( $client_position ) || ! empty( $company_name ) ) : ?>
                    <div class="client-info">
                        <?php
                        $info_parts = array();
                        if ( ! empty( $client_position ) ) {
                            $info_parts[] = esc_html( $client_position );
                        }
                        if ( ! empty( $company_name ) ) {
                            $info_parts[] = esc_html( $company_name );
                        }
                        echo implode( ' at ', $info_parts );
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php
    }
}

/**
 * Initialize the plugin
 */
function testimonials_manager_init() {
    new Testimonials_Manager();
}
add_action( 'plugins_loaded', 'testimonials_manager_init' );

/**
 * Activation hook - flush rewrite rules
 */
function testimonials_manager_activate() {
    // Register the post type
    $testimonials = new Testimonials_Manager();
    $testimonials->register_post_type();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'testimonials_manager_activate' );

/**
 * Deactivation hook - flush rewrite rules
 */
function testimonials_manager_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'testimonials_manager_deactivate' );