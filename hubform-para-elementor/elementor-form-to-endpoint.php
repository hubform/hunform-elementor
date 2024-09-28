<?php
/**
 * Plugin Name: Hubform para Elementor
 * Plugin URI: https://hubform.com.br/hubform-para-elementor
 * Description: Envia dados de formulários do Elementor para seu Hubform.
 * Version: 1.0.0
 * Author: Hubform
 * Author URI: https://hubform.com.br
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: hubform-para-elementor
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Elementor_Form_To_Endpoint {

    public function __construct() {
        add_action('elementor_pro/forms/new_record', [$this, 'send_form_data_to_endpoint'], 10, 2);
        add_action('elementor/element/form/section_form_options/after_section_end', [$this, 'add_custom_endpoint_controls']);
    }

    public function send_form_data_to_endpoint($record, $handler) {
        $form_settings = $record->get('form_settings');
        $custom_endpoint = $form_settings['custom_endpoint'] ?? '';

        if (empty($custom_endpoint)) {
            return;
        }

        $raw_fields = $record->get('fields');
        $data = [];
        foreach ($raw_fields as $id => $field) {
            $data[$id] = $field['value'];
        }

        $response = wp_remote_post($custom_endpoint, [
            'body' => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            $handler->add_error_message('Falha ao enviar dados para o Hubform.');
        }
    }

    public function add_custom_endpoint_controls($widget) {
        $widget->start_controls_section(
            'custom_endpoint_section',
            [
                'label' => __('Hubform para Elementor', 'elementor-form-to-endpoint'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $widget->add_control(
            'custom_endpoint',
            [
                'label' => __('URL do seu formulário no Hubform', 'elementor-form-to-endpoint'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => 'https://app.hubform.com.br/f/seuformid',
                'label_block' => true,
            ]
        );

        $widget->end_controls_section();
    }
}

new Elementor_Form_To_Endpoint();