<?php
/**/
if( ! class_exists( 'LPR_Payment_Gateway_Offline_Payment' ) ) {
    /**
     * Class LPR_Payment_Gateway_Offline_Payment
     *
     * Supports user pay offline
     */
    class LPR_Payment_Gateway_Offline_Payment {
        /**
         * Prevent duplicate init hooks
         *
         * @var bool
         */
        protected static $_loaded = false;

        /**
         * Unique slug
         *
         * @var string
         */
        public $slug = 'offline-payment';

        /**
         * @var string
         */
        public $name = 'Offline payment';

        /**
         * @var string
         */
        protected $_default_text = '';

        /**
         * Constructor
         */
        function __construct(){
            if( self::$_loaded ) return;

            add_filter('learn_press_payment_method', array( $this, 'register' ) );
            add_action('learn_press_section_payment_' . $this->slug, array($this, 'output_settings'));
            add_action('learn_press_save_payment_' . $this->slug, array($this, 'save_settings'));

            add_action('learn_press_payment_gateway_form_' . $this->slug, array( $this, 'payment_form' ) );
            add_filter('learn_press_take_course_' . $this->slug, array($this, 'take_course'));

            add_filter( 'learn_press_payment_method_from_slug_' . $this->slug, array( $this, 'method_name' ) );
            add_filter( 'learn_press_payment_gateway_available_' . $this->slug, array( $this, 'method_available' ), 10, 2 );

            add_filter('learn_press_take_course_' . $this->slug, array( $this, 'take_course' ) );

            $this->_default_text = __( 'Process transactions offline via check or cash', 'learn_press' );

            self::$_loaded = true;
        }

        /**
         * Add this method to the list
         *
         * @param $methods
         * @return mixed
         */
        function register( $methods ){
            $methods[$this->slug] = $this->name;
            return $methods;
        }

        /**
         * Get the name of offline payment
         *
         * @return string
         */
        function method_name(){
            return $this->name;
        }

        /**
         * This method is available or not
         *
         * @return boolean
         */
        function method_available(){
            $settings = LPR_Settings::instance('payment');
            return $settings->get($this->slug . '.enable') ? true : false;
        }

        /**
         * Prints settings in admin
         */
        function output_settings(){
            $settings = LPR_Admin_Settings::instance('payment');
            ?>
            <tr>
                <th scope="row"><label><?php _e('Enable', 'learn_press'); ?></label></th>
                <td>
                    <input type="checkbox" name="lpr_settings[<?php echo $this->slug;?>][enable]" <?php checked( $settings->get($this->slug . '.enable') ? 1 : 0, 1); ?> />
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php _e('Text', 'learn_press'); ?></label></th>
                <td>
                    <input type="text" class="regular-text" name="lpr_settings[<?php echo $this->slug;?>][text]" value="<?php echo esc_attr($settings->get($this->slug . '.text', $this->_default_text )); ?>" />
                </td>
            </tr>
            <?php
        }

        /**
         * Update addon settings
         */
        function save_settings(){
            $settings = LPR_Admin_Settings::instance('payment');
            $post_data = ! empty( $_POST['lpr_settings'] ) ? $_POST['lpr_settings'][$this->slug] : array();
            $settings->set( $this->slug, $post_data );
            $settings->update();
        }

        /**
         * Displays the text when user select this method
         */
        function payment_form(){
            $settings = LPR_Settings::instance('payment');
            printf( '<p>%s</p>', $settings->get( $this->slug . '.text', $this->_default_text ) );
        }

        function take_course(){
            if ( $transaction_object = learn_press_generate_transaction_object() ) {
                $user = learn_press_get_current_user();

                $order_id = learn_press_add_transaction(
                    array(
                        'order_id' => 0,
                        'method' => $this->slug,
                        'method_id' => 0,
                        'status' => 'Pending',
                        'user_id' => $user->ID,
                        'transaction_object' => $transaction_object
                    )
                );
                learn_press_add_message( 'success', __( 'Thank you! Your order has been completed!' ) );
                learn_press_send_json(
                    array(
                        'result'    => 'success',
                        'redirect'  => learn_press_get_order_confirm_url( $order_id )
                    )
                );
                exit();
            }else {

            }
        }
    }
}

new LPR_Payment_Gateway_Offline_Payment();