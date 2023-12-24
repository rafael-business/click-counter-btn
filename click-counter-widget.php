<?php

class Click_Counter_Widget extends WP_Widget
{
    public function __construct() {
        parent::__construct(
            'click_counter_widget',
            __( 'Click Counter Widget', 'click-counter-btn' ),
            array( 'description' => __( 'Widget to count clicks on a button', 'click-counter-btn' ) )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        $button_text = ! empty( $instance['button_text'] ) ? $instance['button_text'] : '';
        $button_link = ! empty( $instance['button_link'] ) ? esc_url( $instance['button_link'] ) : '#';

        echo '<div class="click-counter-widget">';
        echo '<a class="button click-counter-btn" href="' . $button_link . '" id="click-counter-btn"><span>' . $button_text . '</span></a>';
        echo '<div class="load"></div>';
        echo '</div>';
        echo '<div id="click-result"></div>';

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $button_text = ! empty( $instance['button_text'] ) ? $instance['button_text'] : '';
        $button_link = ! empty( $instance['button_link'] ) ? esc_url( $instance['button_link'] ) : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'button_text' ); ?>"><?= __( 'Button Text:', 'click-counter-btn' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'button_text' ); ?>" name="<?php echo $this->get_field_name( 'button_text' ); ?>" type="text" value="<?php echo esc_attr( $button_text ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'button_link' ); ?>"><?= __( 'Button Link:', 'click-counter-btn' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'button_link' ); ?>" name="<?php echo $this->get_field_name( 'button_link' ); ?>" type="text" value="<?php echo esc_attr( $button_link ); ?>" />
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['button_text'] = ! empty( $new_instance['button_text'] ) ? sanitize_text_field( $new_instance['button_text'] ) : '';
        $instance['button_link'] = ! empty( $new_instance['button_link'] ) ? esc_url_raw( $new_instance['button_link'] ) : '';

        return $instance;
    }
}
