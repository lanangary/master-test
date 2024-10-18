<?php

namespace JuiceBox\Core;

class GravityForms
{
    public function __construct(){
        add_filter('gform_form_tag', function ($form_tag, $form) {
            $current_url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $form_tag = preg_replace("|action='(.*?)'|", "action='{$current_url}'", $form_tag);
            return $form_tag;
        }, 10, 2);
        add_filter('gform_init_scripts_footer', '__return_true');
        add_filter('gform_pre_render', [$this, 'gform_set_default_placeholders']);
        add_filter('gettext', function( $translated_text, $text, $domain ) {
            switch ( $translated_text ) {
                case 'This iframe contains the logic required to handle Ajax powered Gravity Forms.' :
               		$translated_text = __( '', 'gravityforms' );
                break;
            }
            return $translated_text;
        }, 20, 3 );
        add_filter('gform_notification', [$this, 'use_sendgrid'], 10, 3 );
        add_filter('gform_form_args', function($args){
            $args['display_description'] = true;
            return $args;
        }, 10, 1 );
        add_filter( 'gform_tabindex', '__return_false' );
    }

    public function gform_set_default_placeholders($form){

        $hasRequiredField = false;
        foreach( $form['fields'] as $key => $field )  {
            if(isset($field->isRequired) && $field->isRequired){
                $hasRequiredField = true;
            }

            if(empty($field->placeholder)){
                $form['fields'][$key]->placeholder = $field->label;

                if(isset($field->isRequired) && $field->isRequired){
                    $form['fields'][$key]->placeholder .= ' *';
                }
            }

            if(isset($field->inputs) && is_array($field->inputs) && count($field->inputs)){
                foreach($field->inputs as $key => $input){
                    if(empty($input['placeholder'])){
                        $field->inputs[$key]['placeholder'] = $input['label'];

                        if(isset($field->isRequired) && $field->isRequired && $key === 0){
                            $field->inputs[$key]['placeholder'] .= ' *';
                        }
                    }
                }
            }
        }
        if($hasRequiredField && !isset($_POST['gform_submit'])){
            $form['description'] .= '<span class="gform_required_message">Fields marked with <span class="gfield_required">*</span> are required.</span>';
        }
        return $form;
    }

    public function use_sendgrid($notification, $form, $entry) {
        $local = false;
        if (defined('WP_ENV') && WP_ENV !== 'production') {
            $local = true;
        }

        if (isset($notification['service'])) {
            $notification['service'] = $local ? 'wordpress' : 'sendgrid';
        }
        return $notification;
    }
}
