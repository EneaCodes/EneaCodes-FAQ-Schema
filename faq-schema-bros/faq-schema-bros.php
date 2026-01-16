<?php
/**
 * Plugin Name: FAQ Schema Bros
 * Plugin URI: https://github.com/EneaCodes/
 * Description: Add beautiful FAQ sections with automatic Schema.org markup for better SEO and Google rich snippets
 * Version: 1.0.0
 * Author: EneaCodes
 * Author URI: https://github.com/EneaCodes/
 * License: GPL v2 or later
 * Text Domain: faq-schema-bros
 */

if (!defined('ABSPATH')) {
    exit;
}

class FAQ_Schema_Bros {
    
    public function __construct() {
        // Add meta box for FAQ
        add_action('add_meta_boxes', array($this, 'add_faq_meta_box'));
        add_action('save_post', array($this, 'save_faq_data'));
        
        // Add FAQ to content
        add_filter('the_content', array($this, 'add_faq_to_content'), 15);
        
        // Add styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_styles'));
        add_action('admin_head', array($this, 'admin_styles'));
        add_action('admin_footer', array($this, 'admin_scripts'));
        
        // Add Schema.org markup to head
        add_action('wp_head', array($this, 'add_faq_schema_markup'));
        
        // Load text domain for translations (fixed - removed load_plugin_textdomain warning)
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    // Load plugin text domain - FIXED VERSION
    public function load_textdomain() {
        // WordPress.org hosted plugins handle translations automatically
        // For non-WordPress.org installations, we can still support translations
        // This is the modern way to handle plugin translations
        $locale = apply_filters('faq_schema_bros_locale', determine_locale(), 'faq-schema-bros');
        $mofile = WP_LANG_DIR . '/plugins/faq-schema-bros-' . $locale . '.mo';
        
        if (file_exists($mofile)) {
            load_textdomain('faq-schema-bros', $mofile);
        }
    }
    
    // Add Meta Box
    public function add_faq_meta_box() {
        add_meta_box(
            'faq_schema_box',
            '❓ FAQ Schema Bros (for Google Rich Snippets)',
            array($this, 'render_faq_meta_box'),
            'post',
            'normal',
            'high'
        );
    }
    
    // Render FAQ Meta Box
    public function render_faq_meta_box($post) {
        wp_nonce_field('faq_schema_nonce', 'faq_schema_nonce');
        
        // Get saved FAQs
        $faqs = get_post_meta($post->ID, '_faq_items', true);
        if (!is_array($faqs)) {
            $faqs = array();
        }
        
        // Get display position
        $position = get_post_meta($post->ID, '_faq_position', true);
        if (empty($position)) {
            $position = 'bottom';
        }
        
        ?>
        <div class="faq-schema-wrapper">
            
            <!-- Header with Stats -->
            <div class="faq-header">
                <div class="faq-header-left">
                    <div class="faq-icon-badge">❓</div>
                    <div>
                        <h3>FAQ Schema Bros</h3>
                        <p>Boost SEO with Google Rich Snippets</p>
                    </div>
                </div>
                <div class="faq-header-right">
                    <div class="faq-stat">
                        <span class="faq-stat-number"><?php echo esc_html(count($faqs)); ?></span>
                        <span class="faq-stat-label">FAQs</span>
                    </div>
                </div>
            </div>
            
            <!-- Benefits Cards -->
            <div class="faq-benefits">
                <div class="faq-benefit-card">
                    <div class="benefit-icon">🎯</div>
                    <div class="benefit-text">
                        <strong>Rich Snippets</strong>
                        <span>Appear in Google with dropdown FAQs</span>
                    </div>
                </div>
                <div class="faq-benefit-card">
                    <div class="benefit-icon">📈</div>
                    <div class="benefit-text">
                        <strong>Higher CTR</strong>
                        <span>3x more clicks from search results</span>
                    </div>
                </div>
                <div class="faq-benefit-card">
                    <div class="benefit-icon">🗣️</div>
                    <div class="benefit-text">
                        <strong>Voice Search</strong>
                        <span>Optimized for Alexa & Google Assistant</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Tips -->
            <div class="faq-tips-section">
                <div class="faq-tips-header">💡 Quick Tips for Posts</div>
                <div class="faq-tips-grid">
			<span class="faq-tip-tag">Is parking available?</span>
			<span class="faq-tip-tag">Sandy or pebbles?</span>
			<span class="faq-tip-tag">Shallow water?</span>
			<span class="faq-tip-tag">Windy or calm?</span>
			<span class="faq-tip-tag">Sunbeds & umbrellas?</span>
			<span class="faq-tip-tag">Free or paid?</span>
			<span class="faq-tip-tag">Good for families?</span>
			<span class="faq-tip-tag">Crowded in summer?</span>
			<span class="faq-tip-tag">Beach bar / food nearby?</span>
			<span class="faq-tip-tag">WC / showers available?</span>
			<span class="faq-tip-tag">Good for snorkeling?</span>
			<span class="faq-tip-tag">Best time to visit?</span>
			<span class="faq-tip-tag">Good for sunset?</span>
			<span class="faq-tip-tag">How to get there?</span>
			<span class="faq-tip-tag">Natural shade?</span>
			<span class="faq-tip-tag">Lifeguard present?</span>
                </div>
            </div>
            
            <!-- Display Settings -->
            <div class="faq-settings-card">
                <div class="faq-setting-row">
                    <div class="faq-setting-label">
                        <span class="setting-icon">📍</span>
                        <div>
                            <strong>Display Position</strong>
                            <p>Where should FAQs appear on your post?</p>
                        </div>
                    </div>
                    <select name="faq_position" class="faq-select">
                        <option value="bottom" <?php selected($position, 'bottom'); ?>>✅ Bottom (Recommended)</option>
                        <option value="top" <?php selected($position, 'top'); ?>>⬆️ Top of Post</option>
                        <option value="hidden" <?php selected($position, 'hidden'); ?>>👁️ Hidden (Not Recommended)</option>
                    </select>
                </div>
                <div class="faq-recommendation">
                    <strong>⚠️ Important:</strong> Use "Bottom" for best SEO. Google prefers visible FAQs that help users. Hidden schema may be ignored or penalized.
                </div>
            </div>
            
            <!-- FAQ List -->
            <div class="faq-list-header">
                <h4>Your FAQs</h4>
                <button type="button" class="faq-add-btn-top" id="add-faq-btn-top">
                    <span class="btn-icon">➕</span>
                    Add FAQ
                </button>
            </div>
            
            <div class="faq-list" id="faq-list">
                <?php
                if (!empty($faqs)) {
                    foreach ($faqs as $index => $faq) {
                        $this->render_faq_item($index, $faq);
                    }
                } else {
                    // Show one empty FAQ by default
                    $this->render_faq_item(0, array('question' => '', 'answer' => ''));
                }
                ?>
            </div>
            
            <!-- Add FAQ Button -->
            <button type="button" class="faq-add-button" id="add-faq-btn">
                <span class="btn-icon">➕</span>
                Add Another FAQ
            </button>
            
        </div>
        <?php
    }
    
    // Render Single FAQ Item
    private function render_faq_item($index, $faq) {
        $question = isset($faq['question']) ? $faq['question'] : '';
        $answer   = isset($faq['answer'])   ? $faq['answer']   : '';
        ?>
        <div class="faq-item-modern" data-index="<?php echo esc_attr($index); ?>">
            <div class="faq-item-header-modern">
                <div class="faq-drag-handle" title="Drag to reorder">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                </div>
                <div class="faq-item-number">#<?php echo esc_html($index + 1); ?></div>
                <div class="faq-item-title">
                    <?php echo $question ? esc_html($question) : 'New FAQ'; ?>
                </div>
                <button type="button" class="faq-delete-btn" title="Delete FAQ">
                    <span>🗑️</span>
                </button>
            </div>
            
            <div class="faq-item-body">
                <div class="faq-input-group">
                    <label class="faq-label">
                        <span class="label-icon">❓</span>
                        Question
                    </label>
                    <input type="text" 
                           name="faq_items[<?php echo esc_attr($index); ?>][question]" 
                           value="<?php echo esc_attr($question); ?>" 
                           placeholder="e.g., Is parking available at this location?"
                           class="faq-input">
                </div>
                
                <div class="faq-input-group">
                    <label class="faq-label">
                        <span class="label-icon">💬</span>
                        Answer
                    </label>
                    <textarea name="faq_items[<?php echo esc_attr($index); ?>][answer]" 
                              rows="3" 
                              class="faq-textarea"
                              placeholder="e.g., Yes, there is free parking available nearby."><?php echo esc_textarea($answer); ?></textarea>
                    <div class="faq-char-count">
                        <span class="char-current">0</span> / <span class="char-max">500</span> characters
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    // Save FAQ Data - FIXED VERSION with proper sanitization and validation
    public function save_faq_data($post_id) {
        // 1. Nonce verification
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verification requires unslashed raw input.
        if (!isset($_POST['faq_schema_nonce']) || !wp_verify_nonce(wp_unslash($_POST['faq_schema_nonce']), 'faq_schema_nonce')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // 2. Save FAQ items with proper validation and unslashing
        // Use filter_input to safely retrieve the array input without triggering InputNotSanitized warnings
        $faq_items_raw = filter_input(INPUT_POST, 'faq_items', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        
        $faq_items = array();
        if (!empty($faq_items_raw)) {
            // Unslash the array (filter_input returns raw data, potentially slashed depending on PHP version)
            $faq_items_unslashed = wp_unslash($faq_items_raw);
            
            // Limit to prevent abuse (max 50 FAQs)
            $faq_items_unslashed = array_slice($faq_items_unslashed, 0, 50);
            
            foreach ($faq_items_unslashed as $item) {
                // Validate structure
                if (!is_array($item) || !isset($item['question'], $item['answer'])) {
                    continue;
                }
                
                // Sanitize and validate
                $question = sanitize_text_field($item['question']);
                $answer = wp_kses_post($item['answer']); // Allow safe HTML
                
                // Trim and check content
                $question = trim($question);
                $answer = trim($answer);
                
                if (!empty($question) && !empty($answer)) {
                    // Limit lengths for database safety
                    $question = substr($question, 0, 200);
                    $answer = substr($answer, 0, 2000);
                    
                    $faq_items[] = array(
                        'question' => $question,
                        'answer' => $answer
                    );
                }
            }
        }
        
        update_post_meta($post_id, '_faq_items', $faq_items);
        
        // 3. Save position with validation
        // Use filter_input to safely retrieve and sanitize the single value without triggering warnings
        $position = 'bottom';
        $raw_position = filter_input(INPUT_POST, 'faq_position', FILTER_SANITIZE_SPECIAL_CHARS);
        
        if (in_array($raw_position, array('bottom', 'top', 'hidden'), true)) {
            $position = $raw_position;
        }
        
        update_post_meta($post_id, '_faq_position', $position);
    }
    
    // Add FAQ to Content
    public function add_faq_to_content($content) {
        if (!is_single() || !is_main_query()) {
            return $content;
        }
        
        global $post;
        
        $faqs = get_post_meta($post->ID, '_faq_items', true);
        $position = get_post_meta($post->ID, '_faq_position', true);
        
        if (empty($faqs) || !is_array($faqs) || $position === 'hidden') {
            return $content;
        }
        
        ob_start();
        ?>
        <div class="faq-schema-section">
            <h2 class="faq-schema-title">❓ Frequently Asked Questions</h2>
            <div class="faq-schema-list">
                <?php foreach ($faqs as $index => $faq): ?>
                <div class="faq-schema-item" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">
                    <div class="faq-question" itemprop="name">
                        <span class="faq-icon">❓</span>
                        <?php echo esc_html($faq['question']); ?>
                        <span class="faq-toggle">▼</span>
                    </div>
                    <div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">
                        <div itemprop="text">
                            <?php echo wp_kses_post(wpautop($faq['answer'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        $faq_html = ob_get_clean();
        
        // Add based on position
        if ($position === 'top') {
            return $faq_html . $content;
        } else {
            return $content . $faq_html;
        }
    }
    
    // Add Schema.org Markup to Head
    public function add_faq_schema_markup() {
        if (!is_single()) {
            return;
        }
        
        global $post;
        
        $faqs = get_post_meta($post->ID, '_faq_items', true);
        
        if (empty($faqs) || !is_array($faqs)) {
            return;
        }
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array()
        );
        
        foreach ($faqs as $faq) {
            // SECURITY FIX: Strip all HTML tags from JSON-LD output
            $schema['mainEntity'][] = array(
                '@type' => 'Question',
                'name' => wp_strip_all_tags($faq['question']),
                'acceptedAnswer' => array(
                    '@type' => 'Answer',
                    'text' => wp_strip_all_tags($faq['answer'])
                )
            );
        }
        
        // SECURITY FIX: Use wp_json_encode with security flags
        echo '<script type="application/ld+json">' . 
             wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . 
             '</script>' . "\n";
    }
    
    // Frontend Styles (unchanged, just the function name)
    public function enqueue_frontend_styles() {
        if (!is_single()) {
            return;
        }
        
        global $post;
        $faqs = get_post_meta($post->ID, '_faq_items', true);
        
        if (empty($faqs)) {
            return;
        }
        
        wp_add_inline_style('wp-block-library', '
            /* FAQ Section Container */
            .faq-schema-section {
                background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
                border: 2px solid #667eea;
                border-radius: 16px;
                padding: 40px;
                margin: 50px 0;
                box-shadow: 0 8px 30px rgba(102, 126, 234, 0.15);
                position: relative;
                overflow: hidden;
            }
            
            /* Decorative Background Pattern */
            .faq-schema-section::before {
                content: "";
                position: absolute;
                top: -50%;
                right: -50%;
                width: 200%;
                height: 200%;
                background: radial-gradient(circle, rgba(102, 126, 234, 0.05) 1px, transparent 1px);
                background-size: 30px 30px;
                pointer-events: none;
            }
            
            /* Title */
            .faq-schema-title {
                position: relative;
                margin: 0 0 35px 0;
                padding-bottom: 20px;
                color: #1f2937;
                font-size: 2rem;
                font-weight: 700;
                text-align: center;
                border-bottom: 3px solid #667eea;
            }
            
            .faq-schema-title::after {
                content: "";
                position: absolute;
                bottom: -3px;
                left: 50%;
                transform: translateX(-50%);
                width: 100px;
                height: 3px;
                background: linear-gradient(90deg, #667eea, #764ba2);
                border-radius: 3px;
            }
            
            /* FAQ List */
            .faq-schema-list {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 16px;
            }
            
            /* Individual FAQ Item */
            .faq-schema-item {
                background: white;
                border: 2px solid transparent;
                border-radius: 12px;
                overflow: hidden;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            }
            
            .faq-schema-item:hover {
                border-color: #667eea;
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.2);
                transform: translateY(-2px);
            }
            
            .faq-schema-item.active {
                border-color: #667eea;
                box-shadow: 0 8px 24px rgba(102, 126, 234, 0.25);
            }
            
            /* Question */
            .faq-question {
                padding: 20px 24px;
                background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
                font-weight: 600;
                color: #1f2937;
                font-size: 1.1rem;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 14px;
                user-select: none;
                transition: all 0.2s;
                position: relative;
            }
            
            .faq-question:hover {
                background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            }
            
            .faq-schema-item.active .faq-question {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .faq-icon {
                font-size: 1.5rem;
                flex-shrink: 0;
                transition: transform 0.3s;
            }
            
            .faq-schema-item.active .faq-icon {
                transform: scale(1.1);
            }
            
            .faq-toggle {
                margin-left: auto;
                font-size: 1rem;
                color: #667eea;
                transition: transform 0.3s, color 0.2s;
                flex-shrink: 0;
                font-weight: 700;
            }
            
            .faq-schema-item.active .faq-toggle {
                transform: rotate(180deg);
                color: white;
            }
            
            /* Answer */
            .faq-answer {
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), padding 0.4s;
                background: white;
            }
            
            .faq-schema-item.active .faq-answer {
                max-height: 800px;
                padding: 24px;
                border-top: 2px solid #e5e7eb;
            }
            
            .faq-answer p {
                margin: 0 0 12px 0;
                color: #4b5563;
                line-height: 1.8;
                font-size: 1rem;
            }
            
            .faq-answer p:last-child {
                margin-bottom: 0;
            }
            
            /* Number Badge */
            .faq-question::before {
                content: counter(faq-counter);
                counter-increment: faq-counter;
                display: flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                background: #667eea;
                color: white;
                border-radius: 50%;
                font-size: 0.875rem;
                font-weight: 700;
                flex-shrink: 0;
                transition: all 0.2s;
            }
            
            .faq-schema-item.active .faq-question::before {
                background: white;
                color: #667eea;
                transform: scale(1.1);
            }
            
            .faq-schema-list {
                counter-reset: faq-counter;
            }
            
            /* Mobile Responsive */
            @media (max-width: 768px) {
                .faq-schema-section {
                    padding: 25px 20px;
                    margin: 30px 0;
                }
                
                .faq-schema-title {
                    font-size: 1.5rem;
                    margin-bottom: 25px;
                }
                
                .faq-question {
                    padding: 16px 18px;
                    font-size: 1rem;
                    gap: 10px;
                }
                
                .faq-icon {
                    font-size: 1.25rem;
                }
                
                .faq-question::before {
                    width: 28px;
                    height: 28px;
                    font-size: 0.75rem;
                }
                
                .faq-schema-item.active .faq-answer {
                    padding: 18px;
                }
                
                .faq-answer p {
                    font-size: 0.95rem;
                }
            }
        ');
        
        // Add inline JavaScript for accordion
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // Toggle FAQ on click
                $(".faq-question").on("click", function(e) {
                    e.preventDefault();
                    var item = $(this).closest(".faq-schema-item");
                    item.toggleClass("active");
                });
                
                // Open first FAQ by default
                $(".faq-schema-item:first").addClass("active");
            });
        ');
    }
    
    // Admin Styles
    public function admin_styles() {
        $screen = get_current_screen();
        if ($screen->post_type !== 'post' || !current_user_can('edit_posts')) {
            return;
        }
        ?>
        <style>
            /* Main Wrapper */
            .faq-schema-wrapper {
                background: #f7f8fa;
                padding: 0;
                margin: -6px -12px -12px;
            }
            
            /* Header */
            .faq-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 25px 30px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                color: white;
            }
            
            .faq-header-left {
                display: flex;
                align-items: center;
                gap: 15px;
            }
            
            .faq-icon-badge {
                width: 50px;
                height: 50px;
                background: rgba(255, 255, 255, 0.2);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                backdrop-filter: blur(10px);
            }
            
            .faq-header h3 {
                margin: 0;
                font-size: 20px;
                font-weight: 600;
                color: white;
            }
            
            .faq-header p {
                margin: 5px 0 0 0;
                opacity: 0.9;
                font-size: 13px;
            }
            
            .faq-stat {
                text-align: center;
                background: rgba(255, 255, 255, 0.15);
                padding: 12px 20px;
                border-radius: 8px;
                backdrop-filter: blur(10px);
            }
            
            .faq-stat-number {
                display: block;
                font-size: 28px;
                font-weight: 700;
                line-height: 1;
            }
            
            .faq-stat-label {
                display: block;
                font-size: 11px;
                opacity: 0.9;
                margin-top: 5px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            /* Benefits Cards */
            .faq-benefits {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 15px;
                padding: 20px 30px;
                background: white;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .faq-benefit-card {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 15px;
                background: #f9fafb;
                border-radius: 8px;
                border-left: 3px solid #667eea;
            }
            
            .benefit-icon {
                font-size: 24px;
                line-height: 1;
            }
            
            .benefit-text strong {
                display: block;
                color: #1f2937;
                font-size: 13px;
                margin-bottom: 3px;
            }
            
            .benefit-text span {
                display: block;
                color: #6b7280;
                font-size: 12px;
                line-height: 1.4;
            }
            
            /* Tips Section */
            .faq-tips-section {
                padding: 20px 30px;
                background: #fef3c7;
                border-bottom: 1px solid #fbbf24;
            }
            
            .faq-tips-header {
                font-weight: 600;
                color: #92400e;
                margin-bottom: 12px;
                font-size: 13px;
            }
            
            .faq-tips-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .faq-tip-tag {
                background: white;
                padding: 6px 12px;
                border-radius: 20px;
                font-size: 12px;
                color: #78350f;
                border: 1px solid #fcd34d;
                cursor: pointer;
                transition: all 0.2s;
            }
            
            .faq-tip-tag:hover {
                background: #667eea;
                color: white;
                border-color: #667eea;
            }
            
            /* Settings Card */
            .faq-settings-card {
                background: white;
                padding: 20px 30px;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .faq-setting-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 20px;
            }
            
            .faq-setting-label {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                flex: 1;
            }
            
            .setting-icon {
                font-size: 20px;
                line-height: 1;
            }
            
            .faq-setting-label strong {
                display: block;
                color: #1f2937;
                font-size: 14px;
                margin-bottom: 3px;
            }
            
            .faq-setting-label p {
                margin: 0;
                color: #6b7280;
                font-size: 12px;
            }
            
            .faq-select {
                padding: 8px 12px;
                border: 2px solid #e5e7eb;
                border-radius: 6px;
                font-size: 13px;
                min-width: 200px;
                background: white;
                cursor: pointer;
            }
            
            .faq-select:focus {
                border-color: #667eea;
                outline: none;
            }
            
            .faq-recommendation {
                margin-top: 15px;
                padding: 12px;
                background: #fef2f2;
                border-left: 3px solid #ef4444;
                border-radius: 6px;
                font-size: 12px;
                color: #991b1b;
            }
            
            .faq-recommendation strong {
                font-weight: 600;
            }
            
            /* List Header */
            .faq-list-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 30px 15px;
                background: #f7f8fa;
            }
            
            .faq-list-header h4 {
                margin: 0;
                color: #1f2937;
                font-size: 16px;
                font-weight: 600;
            }
            
            .faq-add-btn-top {
                background: #667eea;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 6px;
                font-size: 13px;
                font-weight: 500;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
                transition: background 0.2s;
            }
            
            .faq-add-btn-top:hover {
                background: #5568d3;
            }
            
            /* FAQ List */
            .faq-list {
                padding: 0 30px 20px;
                background: #f7f8fa;
                min-height: 50px; /* Ensure drop zone exists if empty */
            }
            
            /* Modern FAQ Item */
            .faq-item-modern {
                background: white;
                border: 2px solid #e5e7eb;
                border-radius: 10px;
                margin-bottom: 15px;
                overflow: hidden;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            
            .faq-item-modern:hover {
                border-color: #cbd5e1;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }
            
            /* Sortable Placeholder Style */
            .faq-sortable-placeholder {
                background: #f0f4ff;
                border: 2px dashed #667eea;
                border-radius: 10px;
                margin-bottom: 15px;
                height: 100px;
                visibility: visible !important;
                box-shadow: inset 0 0 10px rgba(102, 126, 234, 0.1);
            }

            /* Item Being Dragged (Helper) */
            .faq-item-modern.ui-sortable-helper {
                background: white;
                box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
                transform: rotate(2deg);
                opacity: 0.95;
                border-color: #667eea;
                cursor: grabbing;
            }
            
            .faq-item-header-modern {
                background: #f9fafb;
                padding: 12px 20px;
                display: flex;
                align-items: center;
                gap: 12px;
                border-bottom: 1px solid #e5e7eb;
                cursor: default; /* Default cursor, handle will override */
            }
            
            /* Drag Handle Icon */
            .faq-drag-handle {
                color: #9ca3af;
                cursor: grab;
                padding: 4px;
                border-radius: 4px;
                transition: all 0.2s;
            }
            
            .faq-drag-handle:hover {
                color: #667eea;
                background: #eff6ff;
            }
            
            .faq-drag-handle:active {
                cursor: grabbing;
            }

            /* SVG Icon adjustments */
            .faq-drag-handle svg {
                display: block;
            }
            
            .faq-item-number {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                width: 28px;
                height: 28px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: 600;
                box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
            }
            
            .faq-item-title {
                flex: 1;
                font-weight: 500;
                color: #1f2937;
                font-size: 14px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .faq-delete-btn {
                background: transparent;
                border: none;
                padding: 6px 10px;
                cursor: pointer;
                border-radius: 6px;
                font-size: 18px;
                line-height: 1;
                transition: background 0.2s, transform 0.2s;
            }
            
            .faq-delete-btn:hover {
                background: #fee2e2;
                transform: scale(1.1);
            }
            
            .faq-item-body {
                padding: 20px;
            }
            
            .faq-input-group {
                margin-bottom: 20px;
            }
            
            .faq-input-group:last-child {
                margin-bottom: 0;
            }
            
            .faq-label {
                display: flex;
                align-items: center;
                gap: 6px;
                margin-bottom: 8px;
                font-weight: 600;
                color: #374151;
                font-size: 13px;
            }
            
            .label-icon {
                font-size: 16px;
            }
            
            .faq-input,
            .faq-textarea {
                width: 100%;
                padding: 10px 12px;
                border: 2px solid #e5e7eb;
                border-radius: 6px;
                font-size: 14px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                transition: border-color 0.2s, box-shadow 0.2s;
            }
            
            .faq-input:focus,
            .faq-textarea:focus {
                border-color: #667eea;
                outline: none;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .faq-textarea {
                resize: vertical;
                min-height: 80px;
            }
            
            .faq-char-count {
                margin-top: 6px;
                font-size: 11px;
                color: #9ca3af;
                text-align: right;
            }
            
            .char-current {
                font-weight: 600;
                color: #667eea;
            }
            
            /* Add Button */
            .faq-add-button {
                width: calc(100% - 60px);
                margin: 0 30px 30px;
                padding: 14px;
                background: #667eea;
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                transition: background 0.2s, transform 0.1s;
                box-shadow: 0 4px 6px rgba(102, 126, 234, 0.2);
            }
            
            .faq-add-button:hover {
                background: #5568d3;
                transform: translateY(-1px);
                box-shadow: 0 6px 10px rgba(102, 126, 234, 0.3);
            }
            
            .faq-add-button:active {
                transform: translateY(0);
            }
            
            .btn-icon {
                font-size: 16px;
            }
            
            /* Responsive */
            @media (max-width: 1200px) {
                .faq-benefits {
                    grid-template-columns: 1fr;
                }
            }
        </style>
        <?php
    }
    
    // Admin Scripts
    public function admin_scripts() {
        $screen = get_current_screen();
        if ($screen->post_type !== 'post' || !current_user_can('edit_posts')) {
            return;
        }
        
        // FIX: Enqueue jQuery UI Sortable for drag and drop functionality
        wp_enqueue_script('jquery-ui-sortable');
        ?>
        <script>
            jQuery(document).ready(function($) {
                var faqIndex = $('.faq-item-modern').length;
                
                // Initialize Sortable (Drag and Drop)
                $("#faq-list").sortable({
                    handle: ".faq-drag-handle",
                    placeholder: "faq-sortable-placeholder",
                    cursor: "grabbing",
                    tolerance: "pointer",
                    opacity: 0.8,
                    start: function(e, ui) {
                        // Adjust placeholder height to match item being dragged
                        ui.placeholder.height(ui.item.height());
                        // Add a class to the item being dragged for CSS styling
                        ui.item.addClass("faq-sorting");
                    },
                    stop: function(e, ui) {
                        // Remove styling class
                        ui.item.removeClass("faq-sorting");
                        // Update indices and input names
                        updateFaqNumbers();
                    }
                });
                
                // Prevent inputs from initiating drag when trying to select text
                $("#faq-list input, #faq-list textarea").on('mousedown', function(e) {
                    e.stopPropagation();
                });
                
                // Update FAQ title on question input
                $(document).on('input', '.faq-input', function() {
                    var question = $(this).val();
                    var title = question || 'New FAQ';
                    $(this).closest('.faq-item-modern').find('.faq-item-title').text(title);
                });
                
                // Character counter for answers
                $(document).on('input', '.faq-textarea', function() {
                    var current = $(this).val().length;
                    $(this).siblings('.faq-char-count').find('.char-current').text(current);
                    
                    // Warning if over 500 chars
                    if (current > 500) {
                        $(this).siblings('.faq-char-count').css('color', '#dc3545');
                    } else {
                        $(this).siblings('.faq-char-count').css('color', '#9ca3af');
                    }
                });
                
                // Initialize character counters
                $('.faq-textarea').each(function() {
                    var current = $(this).val().length;
                    $(this).siblings('.faq-char-count').find('.char-current').text(current);
                });
                
                // Add new FAQ (both buttons)
                $('#add-faq-btn, #add-faq-btn-top').on('click', function(e) {
                    e.preventDefault();
                    
                    var newFaq = `
                        <div class="faq-item-modern" data-index="${faqIndex}">
                            <div class="faq-item-header-modern">
                                <div class="faq-drag-handle" title="Drag to reorder">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="8" y1="6" x2="21" y2="6"></line>
                                        <line x1="8" y1="12" x2="21" y2="12"></line>
                                        <line x1="8" y1="18" x2="21" y2="18"></line>
                                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                    </svg>
                                </div>
                                <div class="faq-item-number">#${faqIndex + 1}</div>
                                <div class="faq-item-title">New FAQ</div>
                                <button type="button" class="faq-delete-btn" title="Delete FAQ">
                                    <span>🗑️</span>
                                </button>
                            </div>
                            
                            <div class="faq-item-body">
                                <div class="faq-input-group">
                                    <label class="faq-label">
                                        <span class="label-icon">❓</span>
                                        Question
                                    </label>
                                    <input type="text" 
                                           name="faq_items[${faqIndex}][question]" 
                                           value="" 
                                           placeholder="e.g., Is parking available at this beach?"
                                           class="faq-input">
                                </div>
                                
                                <div class="faq-input-group">
                                    <label class="faq-label">
                                        <span class="label-icon">💬</span>
                                        Answer
                                    </label>
                                    <textarea name="faq_items[${faqIndex}][answer]" 
                                              rows="3" 
                                              class="faq-textarea"
                                              placeholder="e.g., Yes, there is free parking available about 100 meters from the beach entrance."></textarea>
                                    <div class="faq-char-count">
                                        <span class="char-current">0</span> / <span class="char-max">500</span> characters
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#faq-list').append(newFaq);
                    faqIndex++;
                    updateFaqNumbers();
                    
                    // Scroll to new FAQ
                    var newItem = $('.faq-item-modern').last();
                    $('html, body').animate({
                        scrollTop: newItem.offset().top - 100
                    }, 300);
                    
                    // Focus on question input
                    newItem.find('.faq-input').focus();
                });
                
                // Remove FAQ
                $(document).on('click', '.faq-delete-btn', function(e) {
                    e.preventDefault();
                    
                    if ($('.faq-item-modern').length > 1) {
                        if (confirm('Are you sure you want to delete this FAQ?')) {
                            $(this).closest('.faq-item-modern').fadeOut(300, function() {
                                $(this).remove();
                                updateFaqNumbers();
                                updateFaqCount();
                            });
                        }
                    } else {
                        alert('You must have at least one FAQ item. Clear the content if you don\'t want to use FAQs.');
                    }
                });
                
                // Update FAQ numbers and indices
                function updateFaqNumbers() {
                    $('.faq-item-modern').each(function(index) {
                        $(this).find('.faq-item-number').text('#' + (index + 1));
                        $(this).attr('data-index', index);
                        
                        // Update input names
                        $(this).find('input[name*="[question]"]').attr('name', 'faq_items[' + index + '][question]');
                        $(this).find('textarea[name*="[answer]"]').attr('name', 'faq_items[' + index + '][answer]');
                    });
                }
                
                // Update FAQ count in header
                function updateFaqCount() {
                    var count = $('.faq-item-modern').length;
                    $('.faq-stat-number').text(count);
                }
                
                // Copy question example to input on click
                $(document).on('click', '.faq-tip-tag', function(e) {
                    e.preventDefault();
                    var question = $(this).text();
                    
                    // Find first empty FAQ or create new one
                    var emptyFaq = $('.faq-input').filter(function() {
                        return $(this).val() === '';
                    }).first();
                    
                    if (emptyFaq.length) {
                        emptyFaq.val(question).trigger('input');
                        $('html, body').animate({
                            scrollTop: emptyFaq.offset().top - 100
                        }, 300);
                        emptyFaq.focus();
                    } else {
                        // Add new FAQ with this question
                        $('#add-faq-btn').click();
                        setTimeout(function() {
                            $('.faq-input').last().val(question).trigger('input').focus();
                        }, 100);
                    }
                });
            });
        </script>
        <?php
    }
}

// Initialize
new FAQ_Schema_Bros();