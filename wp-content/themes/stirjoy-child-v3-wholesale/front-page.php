<?php
/**
 * Template Name: Front Page
 * The front page template for STIRJOY - Based on Figma Design
 */

get_header();

// Remove default page spacing and breadcrumbs for front page
remove_action('thecrate_before_primary_area', 'thecrate_header_title_breadcrumbs_include');

// Helper function to get image URL from images folder
function stirjoy_get_image_url($filename) {
    $image_path = get_stylesheet_directory_uri() . '/images/home page/' . $filename;
    return $image_path;
}
?>

<!-- Hero Section -->
<section class="stirjoy-hero-section" style="background-image: url('<?php echo esc_url(stirjoy_get_image_url('c5eb69843577e51f40926a6780c5892843d1e942.jpg')); ?>');">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="hero-title">Live to the full, we've got dinner covered</h1>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-hero-primary">SEE MENU</a>
            </div>
            <div class="col-md-4">
                <!-- Right side content can be removed or kept for spacing -->
            </div>
        </div>
    </div>
</section>

<!-- Feature Bar -->
<section class="stirjoy-feature-bar">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="feature-bar-wrapper">
                    <div class="feature-items">
                        <span class="feature-text">clean ingredients</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">plant-based</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">protein</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">fibre</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">veggies</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">lots of joy</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">$6 per portion</span>
                        <span class="feature-icon">-</span>
                        <!-- Duplicate for seamless loop -->
                        <span class="feature-text">clean ingredients</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">plant-based</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">protein</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">fibre</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">veggies</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">lots of joy</span>
                        <span class="feature-icon">-</span>
                        <span class="feature-text">$6 per portion</span>
                        <span class="feature-icon">-</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Introduction Section -->
<section class="stirjoy-product-intro">
    <div class="container">
        <div class="row">
            <div class="col-md-1 text-center"></div>
            <div class="col-md-10 text-center">
                <h1 class="intro-text">Easy, plant-powered meal kits that live in your pantry. Just add water and simmer.</h1>
            </div>
            <div class="col-md-1 text-center"></div>
        </div>
        <div class="row product-cards">
            <?php
            // Get featured products or latest products
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => 3,
                'orderby' => 'date',
                'order' => 'DESC',
            );
            $products = new WP_Query($args);
            
            if ($products->have_posts()) :
                while ($products->have_posts()) : $products->the_post();
                    global $product;
                    $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
            ?>
            <div class="col-md-4">
                <div class="product-card">
                    <?php if ($product_image) : ?>
                        <div class="product-image">
                            <img src="<?php echo esc_url($product_image[0]); ?>" alt="<?php the_title(); ?>">
                        </div>
                    <?php endif; ?>
                    <h3 class="product-title"><?php the_title(); ?></h3>
                </div>
            </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else:
            ?>
            <!-- Fallback product cards if no products exist -->
            <div class="col-md-4">
                <div class="product-card">
                    <div class="product-image">
                        <div class="product-placeholder"></div>
                    </div>
                    <h3 class="product-title">Risotto with lion's mane and grilled mushrooms</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="product-card">
                    <div class="product-image">
                        <div class="product-placeholder"></div>
                    </div>
                    <h3 class="product-title">Burrito bowl with quinoa and dried tomatoes</h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="product-card">
                    <div class="product-image">
                        <div class="product-placeholder"></div>
                    </div>
                    <h3 class="product-title">Lentil curry with sweet potato and coconut</h3>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-hero-primary">CUSTOMIZE YOUR BOX</a>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="stirjoy-health-planet">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="section-title">Meals designed for your health, your wallet, and the planet</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="health-benefits-wrapper">
                    <div class="central-bowl">
                        <div class="bowl-placeholder"></div>
                    </div>
                    <div class="benefit-item benefit-top-left">
                        <span class="benefit-icon">🥕</span>
                        <span class="benefit-text">squeezed veggies</span>
                    </div>
                    <div class="benefit-item benefit-mid-left">
                        <span class="benefit-icon">⚡</span>
                        <span class="benefit-text">loads of fiber</span>
                    </div>
                    <div class="benefit-item benefit-bottom-left">
                        <span class="benefit-icon">👍</span>
                        <span class="benefit-text">$6 per portion</span>
                    </div>
                    <div class="benefit-item benefit-top-right">
                        <span class="benefit-icon">💪</span>
                        <span class="benefit-text">loads of protein</span>
                    </div>
                    <div class="benefit-item benefit-mid-right">
                        <span class="benefit-icon">🌱</span>
                        <span class="benefit-text">plant-based</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="stirjoy-how-it-works">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="section-title-white">How it works</h2>
            </div>
        </div>
        <div class="row steps">
            <div class="col-md-4 text-center">
                <div class="step-icon">👆</div>
                <h3 class="step-title">Choose Your Meals</h3>
                <p class="step-description">We prepare a monthly menu of 6 meals for you, and you can customize it to your taste!</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="step-icon">📦</div>
                <h3 class="step-title">We Prepare & Deliver</h3>
                <p class="step-description">From Canadian farms to your door, for only $9 per portion.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="step-icon">🍲</div>
                <h3 class="step-title">Enjoy at your convenience!</h3>
                <p class="step-description">Just add water and simmer.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-12 text-center">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-hero-primary">CUSTOMIZE YOUR BOX</a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="stirjoy-testimonials">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="section-title">Don't just take our word for it!</h2>
                <p class="section-subtitle">The Foodies have spoken</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-illustration">
                        <div class="illustration-placeholder"></div>
                    </div>
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"So easy to prep, real ingredients, and tastes amazing!"</p>
                    <p class="testimonial-author">- Alissa A.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"Healthy, easy, and my digestion has never been better!"</p>
                    <p class="testimonial-author">- James D.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="stars">★★★★★</div>
                    <p class="testimonial-text">"Love the convenience and the taste is incredible!"</p>
                    <p class="testimonial-author">- Sarah L.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission Statement Section -->
<section class="stirjoy-mission">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2 text-center">
                <h2 class="mission-title">Our mission is to make good nutrition less costly for humans and the planet on which we live.</h2>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-hero-primary">GET STARTED</a>
            </div>
        </div>
    </div>
</section>

<!-- FAQs Section -->
<section class="stirjoy-faqs">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="section-title-white">FAQs</h2>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 offset-md-1">
                <div class="faq-wrapper">
                    <div class="faq-illustration">
                        <div class="faq-illustration-placeholder"></div>
                    </div>
                    <div class="faq-accordion">
                        <div class="faq-item active">
                            <div class="faq-question">Are dehydrated meals nutritious?</div>
                            <div class="faq-answer">Yes! Dehydration preserves nutrients while removing water, making meals shelf-stable and nutritious. Our process maintains vitamins, minerals, and fiber content.</div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">Why are Stirjoy meal kits so much cheaper than traditional meal kits?</div>
                            <div class="faq-answer">Our dehydrated format reduces shipping costs and extends shelf life, allowing us to offer better prices without compromising on quality or nutrition.</div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">How does the Stirjoy subscription work?</div>
                            <div class="faq-answer">Choose your meals monthly, and we'll deliver them to your door. You can customize, skip, or cancel anytime with full flexibility.</div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">Can I reschedule or skip a month?</div>
                            <div class="faq-answer">Absolutely! You have full control over your subscription and can skip or reschedule deliveries anytime through your account dashboard.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media Section -->
<section class="stirjoy-social">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="section-title">Looks good!</h2>
                <div class="social-posts">
                    <div class="social-post-grid">
                        <div class="social-post-item">
                            <div class="social-post-placeholder"></div>
                        </div>
                        <div class="social-post-item">
                            <div class="social-post-placeholder"></div>
                        </div>
                        <div class="social-post-item">
                            <div class="social-post-placeholder"></div>
                        </div>
                        <div class="social-post-item">
                            <div class="social-post-placeholder"></div>
                        </div>
                    </div>
                    <p class="social-follow">Follow along @stirjoy.ca</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bottom CTA Section -->
<section class="stirjoy-cta-bottom">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center">
                <h2 class="cta-title">So... what will you stir into the world with all that extra time and energy?</h2>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="btn btn-cta-bottom">GET STARTED</a>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>

