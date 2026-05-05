<?php
/**
 * Plugin Name: Features CPT
 * Description: Registra el CPT "caracteristica" para headless, con meta box y campos REST.
 * Version: 1.0.0
 * Author: SW
 * License: GPL2+
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registra el Custom Post Type de caracteristicas.
 */
function features_cpt_register_post_type()
{
    $labels = array(
        'name'               => 'Caracteristicas',
        'singular_name'      => 'Caracteristica',
        'menu_name'          => 'Caracteristicas',
        'add_new'            => 'Anadir nueva',
        'add_new_item'       => 'Anadir nueva caracteristica',
        'edit_item'          => 'Editar caracteristica',
        'new_item'           => 'Nueva caracteristica',
        'view_item'          => 'Ver caracteristica',
        'all_items'          => 'Todas las caracteristicas',
        'search_items'       => 'Buscar caracteristicas',
        'not_found'          => 'No se encontraron caracteristicas',
        'not_found_in_trash' => 'No hay caracteristicas en la papelera',
    );

    $args = array(
        'labels'        => $labels,
        'public'        => true,
        'has_archive'   => false,
        'show_in_rest'  => true,
        'show_in_graphql'     => true,
        'graphql_single_name' => 'Caracteristica',
        'graphql_plural_name' => 'Caracteristicas',
        'menu_icon'     => 'dashicons-layout',
        'supports'      => array('title', 'editor', 'thumbnail', 'excerpt'),
        'taxonomies'    => array('category', 'post_tag'),
        'rewrite'       => array('slug' => 'caracteristicas'),
        'menu_position' => 20,
    );

    register_post_type('caracteristica', $args);
}
add_action('init', 'features_cpt_register_post_type');

/**
 * Agrega el meta box para la URL.
 */
function features_cpt_add_meta_box()
{
    add_meta_box(
        'features_cpt_button_url',
        'Boton de accion',
        'features_cpt_render_meta_box',
        'caracteristica',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'features_cpt_add_meta_box');

/**
 * Render del meta box.
 */
function features_cpt_render_meta_box($post)
{
    wp_nonce_field('features_cpt_save_meta', 'features_cpt_meta_nonce');
    $button_url = get_post_meta($post->ID, '_boton_url', true);
    if ($button_url === '') {
        $button_url = get_post_meta($post->ID, '_url_saber_mas', true);
    }
    $button_label = get_post_meta($post->ID, '_boton_texto', true);
    if ($button_label === '') {
        $button_label = 'Quiero saber mas';
    }
    $button_bg_color = get_post_meta($post->ID, '_boton_color_fondo', true);
    if ($button_bg_color === '') {
        $button_bg_color = '#2563eb';
    }
    $button_text_color = get_post_meta($post->ID, '_boton_color_texto', true);
    if ($button_text_color === '') {
        $button_text_color = '#ffffff';
    }
    ?>
    <p>
        <label for="features_cpt_button_url"><strong>Enlace del boton</strong></label>
        <input
            type="url"
            id="features_cpt_button_url"
            name="features_cpt_button_url"
            value="<?php echo esc_attr($button_url); ?>"
            placeholder="https://ejemplo.com"
            style="width:100%;margin-top:6px;"
        />
    </p>
    <p>
        <label for="features_cpt_button_label"><strong>Texto del boton</strong></label>
        <input
            type="text"
            id="features_cpt_button_label"
            name="features_cpt_button_label"
            value="<?php echo esc_attr($button_label); ?>"
            placeholder="Quiero saber mas"
            style="width:100%;margin-top:6px;"
        />
    </p>
    <p>
        <label for="features_cpt_button_bg_color"><strong>Color de fondo</strong></label>
        <input
            type="color"
            id="features_cpt_button_bg_color"
            name="features_cpt_button_bg_color"
            value="<?php echo esc_attr($button_bg_color); ?>"
            style="width:100%;margin-top:6px;"
        />
    </p>
    <p>
        <label for="features_cpt_button_text_color"><strong>Color del texto</strong></label>
        <input
            type="color"
            id="features_cpt_button_text_color"
            name="features_cpt_button_text_color"
            value="<?php echo esc_attr($button_text_color); ?>"
            style="width:100%;margin-top:6px;"
        />
    </p>
    <p style="margin-top:8px;color:#666;font-size:12px;">
        Puedes pegar dominio sin protocolo (ej. ejemplo.com); se guardara como https://ejemplo.com.
    </p>
    <?php
}

/**
 * Guardado seguro del campo personalizado.
 */
function features_cpt_save_meta_box($post_id)
{
    if (
        !isset($_POST['features_cpt_meta_nonce']) ||
        !wp_verify_nonce($_POST['features_cpt_meta_nonce'], 'features_cpt_save_meta')
    ) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!isset($_POST['post_type']) || $_POST['post_type'] !== 'caracteristica') {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $raw_url = isset($_POST['features_cpt_button_url'])
        ? trim((string) wp_unslash($_POST['features_cpt_button_url']))
        : '';

    // Si el usuario no agrega protocolo, asumimos HTTPS para evitar que se borre al sanitizar.
    if ($raw_url !== '' && !preg_match('#^https?://#i', $raw_url)) {
        $raw_url = 'https://' . $raw_url;
    }

    $url = esc_url_raw($raw_url);

    if (!empty($url)) {
        update_post_meta($post_id, '_boton_url', $url);
        update_post_meta($post_id, '_url_saber_mas', $url); // compatibilidad con implementaciones previas.
    } else {
        delete_post_meta($post_id, '_boton_url');
        delete_post_meta($post_id, '_url_saber_mas');
    }

    $button_label = isset($_POST['features_cpt_button_label'])
        ? sanitize_text_field(wp_unslash($_POST['features_cpt_button_label']))
        : '';
    if ($button_label === '') {
        $button_label = 'Quiero saber mas';
    }
    update_post_meta($post_id, '_boton_texto', $button_label);

    $button_bg_color = isset($_POST['features_cpt_button_bg_color'])
        ? sanitize_hex_color(wp_unslash($_POST['features_cpt_button_bg_color']))
        : '';
    if (!empty($button_bg_color)) {
        update_post_meta($post_id, '_boton_color_fondo', $button_bg_color);
    } else {
        delete_post_meta($post_id, '_boton_color_fondo');
    }

    $button_text_color = isset($_POST['features_cpt_button_text_color'])
        ? sanitize_hex_color(wp_unslash($_POST['features_cpt_button_text_color']))
        : '';
    if (!empty($button_text_color)) {
        update_post_meta($post_id, '_boton_color_texto', $button_text_color);
    } else {
        delete_post_meta($post_id, '_boton_color_texto');
    }
}
add_action('save_post', 'features_cpt_save_meta_box');

/**
 * Expone campos para consumo headless via REST API.
 */
function features_cpt_register_rest_fields()
{
    // El titulo del post es el titulo del tab en frontend headless.
    register_rest_field(
        'caracteristica',
        'tab_title',
        array(
            'get_callback' => function ($post_arr) {
                return get_the_title((int) $post_arr['id']);
            },
            'schema'       => array(
                'description' => 'Titulo a usar como nombre de la pestana/tab.',
                'type'        => 'string',
                'context'     => array('view', 'edit'),
            ),
        )
    );

    register_rest_field(
        'caracteristica',
        'button_url',
        array(
            'get_callback'    => function ($post_arr) {
                $value = get_post_meta($post_arr['id'], '_boton_url', true);
                if (!is_string($value) || $value === '') {
                    $value = get_post_meta($post_arr['id'], '_url_saber_mas', true);
                }
                return is_string($value) ? $value : '';
            },
            'update_callback' => function ($value, $post_obj) {
                $sanitized = is_string($value) ? esc_url_raw($value) : '';
                if ($sanitized === '') {
                    delete_post_meta($post_obj->ID, '_boton_url');
                    delete_post_meta($post_obj->ID, '_url_saber_mas');
                    return true;
                }
                update_post_meta($post_obj->ID, '_boton_url', $sanitized);
                return (bool) update_post_meta($post_obj->ID, '_url_saber_mas', $sanitized);
            },
            'schema'          => array(
                'description' => 'URL del boton de accion.',
                'type'        => 'string',
                'format'      => 'uri',
                'context'     => array('view', 'edit'),
            ),
        )
    );

    register_rest_field(
        'caracteristica',
        'button_label',
        array(
            'get_callback'    => function ($post_arr) {
                $value = get_post_meta($post_arr['id'], '_boton_texto', true);
                return is_string($value) && $value !== '' ? $value : 'Quiero saber mas';
            },
            'update_callback' => function ($value, $post_obj) {
                $sanitized = is_string($value) ? sanitize_text_field($value) : '';
                if ($sanitized === '') {
                    $sanitized = 'Quiero saber mas';
                }
                return (bool) update_post_meta($post_obj->ID, '_boton_texto', $sanitized);
            },
            'schema'          => array(
                'description' => 'Texto visible del boton.',
                'type'        => 'string',
                'context'     => array('view', 'edit'),
            ),
        )
    );

    register_rest_field(
        'caracteristica',
        'button_bg_color',
        array(
            'get_callback'    => function ($post_arr) {
                $value = get_post_meta($post_arr['id'], '_boton_color_fondo', true);
                return is_string($value) && $value !== '' ? $value : '#2563eb';
            },
            'update_callback' => function ($value, $post_obj) {
                $sanitized = is_string($value) ? sanitize_hex_color($value) : '';
                if ($sanitized === '') {
                    delete_post_meta($post_obj->ID, '_boton_color_fondo');
                    return true;
                }
                return (bool) update_post_meta($post_obj->ID, '_boton_color_fondo', $sanitized);
            },
            'schema'          => array(
                'description' => 'Color de fondo del boton en formato hexadecimal.',
                'type'        => 'string',
                'context'     => array('view', 'edit'),
            ),
        )
    );

    register_rest_field(
        'caracteristica',
        'button_text_color',
        array(
            'get_callback'    => function ($post_arr) {
                $value = get_post_meta($post_arr['id'], '_boton_color_texto', true);
                return is_string($value) && $value !== '' ? $value : '#ffffff';
            },
            'update_callback' => function ($value, $post_obj) {
                $sanitized = is_string($value) ? sanitize_hex_color($value) : '';
                if ($sanitized === '') {
                    delete_post_meta($post_obj->ID, '_boton_color_texto');
                    return true;
                }
                return (bool) update_post_meta($post_obj->ID, '_boton_color_texto', $sanitized);
            },
            'schema'          => array(
                'description' => 'Color del texto del boton en formato hexadecimal.',
                'type'        => 'string',
                'context'     => array('view', 'edit'),
            ),
        )
    );

    register_rest_field(
        'caracteristica',
        'url_saber_mas',
        array(
            'get_callback' => function ($post_arr) {
                $value = get_post_meta($post_arr['id'], '_boton_url', true);
                if (!is_string($value) || $value === '') {
                    $value = get_post_meta($post_arr['id'], '_url_saber_mas', true);
                }
                return is_string($value) ? $value : '';
            },
            'schema'       => array(
                'description' => 'Alias legado de button_url.',
                'type'        => 'string',
                'format'      => 'uri',
                'context'     => array('view', 'edit'),
            ),
        )
    );
}
add_action('rest_api_init', 'features_cpt_register_rest_fields');

/**
 * Expone campos personalizados en WPGraphQL.
 */
function features_cpt_register_graphql_fields()
{
    if (!function_exists('register_graphql_field')) {
        return;
    }

    register_graphql_field(
        'Caracteristica',
        'buttonUrl',
        array(
            'type'        => 'String',
            'description' => 'URL del boton de accion.',
            'resolve'     => function ($post) {
                $post_id = isset($post->ID) ? (int) $post->ID : 0;
                $value = get_post_meta($post_id, '_boton_url', true);
                if (!is_string($value) || $value === '') {
                    $value = get_post_meta($post_id, '_url_saber_mas', true);
                }
                return is_string($value) ? $value : '';
            },
        )
    );

    register_graphql_field(
        'Caracteristica',
        'buttonLabel',
        array(
            'type'        => 'String',
            'description' => 'Texto visible del boton.',
            'resolve'     => function ($post) {
                $post_id = isset($post->ID) ? (int) $post->ID : 0;
                $value = get_post_meta($post_id, '_boton_texto', true);
                return is_string($value) && $value !== '' ? $value : 'Quiero saber mas';
            },
        )
    );

    register_graphql_field(
        'Caracteristica',
        'buttonBgColor',
        array(
            'type'        => 'String',
            'description' => 'Color de fondo del boton (hex).',
            'resolve'     => function ($post) {
                $post_id = isset($post->ID) ? (int) $post->ID : 0;
                $value = get_post_meta($post_id, '_boton_color_fondo', true);
                return is_string($value) && $value !== '' ? $value : '#2563eb';
            },
        )
    );

    register_graphql_field(
        'Caracteristica',
        'buttonTextColor',
        array(
            'type'        => 'String',
            'description' => 'Color del texto del boton (hex).',
            'resolve'     => function ($post) {
                $post_id = isset($post->ID) ? (int) $post->ID : 0;
                $value = get_post_meta($post_id, '_boton_color_texto', true);
                return is_string($value) && $value !== '' ? $value : '#ffffff';
            },
        )
    );
}
add_action('graphql_register_types', 'features_cpt_register_graphql_fields');

/**
 * Compatibilidad con Polylang / Polylang Pro para CPT traducible.
 */
function features_cpt_polylang_register_post_type($post_types, $is_settings)
{
    if ($is_settings) {
        // Mostrar en Idiomas > Ajustes > Tipos de contenido personalizados.
        $post_types['caracteristica'] = 'caracteristica';
    } else {
        // Activar traduccion del CPT en tiempo de ejecucion.
        $post_types[] = 'caracteristica';
    }

    return $post_types;
}
add_filter('pll_get_post_types', 'features_cpt_polylang_register_post_type', 10, 2);

/**
 * Asigna idioma por defecto si una caracteristica se guarda sin idioma en Polylang.
 */
function features_cpt_polylang_set_default_language_on_save($post_id, $post, $update)
{
    if ($post->post_type !== 'caracteristica') {
        return;
    }

    if (!function_exists('pll_get_post_language') || !function_exists('pll_set_post_language') || !function_exists('pll_default_language')) {
        return;
    }

    $current_lang = pll_get_post_language($post_id, 'slug');
    if (!empty($current_lang)) {
        return;
    }

    $default_lang = pll_default_language('slug');
    if (!empty($default_lang)) {
        pll_set_post_language($post_id, $default_lang);
    }
}
add_action('save_post', 'features_cpt_polylang_set_default_language_on_save', 20, 3);
