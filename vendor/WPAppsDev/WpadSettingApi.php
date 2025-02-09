<?php

namespace WPAppsDev;

/**
 * Settings API Class
 *
 * @version 1.0
 */
class WpadSettingApi {
	/**
	 * Settings sections array.
	 *
	 * @var array
	 */
	private $settings_sections = [];

	/**
	 * Settings fields array.
	 *
	 * @var array
	 */
	private $settings_fields = [];

	/**
	 * Singleton instance.
	 *
	 * @var object
	 */
	private static $_instance;

	public function __construct() {
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'thickbox' );

		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'thickbox' );
		// wp_register_script( 'chosen', plugin_dir_url( __FILE__ ) . '/assets/js/chosen.js', array( 'jquery' ) );
		wp_enqueue_script( 'wpad-settings', plugin_dir_url( __FILE__ ) . '/assets/js/settings.js' );
	}

	/**
	 * Set settings sections.
	 *
	 * @param array $sections setting sections array
	 */
	public function set_sections( $sections ) {
		$this->settings_sections = $sections;

		return $this;
	}

	/**
	 * Add a single section.
	 *
	 * @param array $section
	 */
	public function add_section( $section ) {
		$this->settings_sections[] = $section;

		return $this;
	}

	/**
	 * Set settings fields.
	 *
	 * @param array $fields settings fields array
	 */
	public function set_fields( $fields ) {
		$this->settings_fields = $fields;

		return $this;
	}

	public function add_field( $section, $field ) {
		$defaults = [
			'name'  => '',
			'label' => '',
			'desc'  => '',
			'type'  => 'text',
		];

		$arg                               = wp_parse_args( $field, $defaults );
		$this->settings_fields[$section][] = $arg;

		return $this;
	}

	/**
	 * Initialize and registers the settings sections and fileds to WordPress
	 *
	 * Usually this should be called at `admin_init` hook.
	 *
	 * This function gets the initiated settings sections and fields. Then
	 * registers them to WordPress and ready for use.
	 */
	public function admin_init() {
		// register settings sections
		foreach ( $this->settings_sections as $section ) {
			if ( false == get_option( $section['id'] ) ) {
				add_option( $section['id'] );
			}

			if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
				$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
				$callback        = create_function( '', 'echo "' . str_replace( '"', '\"', $section['desc'] ) . '";' );
			} else {
				$callback = '__return_false';
			}

			// add_settings_section( $id, $title, $callback, $page );
			add_settings_section( $section['id'] . '_section', $section['title'], $callback, $section['id'] . '_options' );
		}

		// register settings fields
		foreach ( $this->settings_fields as $section => $field ) {
			foreach ( $field as $option ) {
				$type = isset( $option['type'] ) ? $option['type'] : 'text';

				$args = [
					'id'                => $option['name'],
					'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
					'name'              => $option['label'],
					'section'           => $section,
					'type'              => $type,
					'size'              => isset( $option['size'] ) ? $option['size'] : null,
					'options'           => isset( $option['options'] ) ? $option['options'] : '',
					'class'             => isset( $option['class'] ) ? $option['class'] : '',
					'std'               => isset( $option['default'] ) ? $option['default'] : '',
					'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
					'readonly'          => isset( $option['readonly'] ) ? $option['readonly'] : false,
				];

				if ( 'button' == $type ) {
					$args['url']   = isset( $option['url'] ) ? $option['url'] : '';
					$args['value'] = isset( $option['value'] ) ? $option['value'] : '';
				}

				// add_settings_field( $id, $title, $callback, $page, $section, $args );
				add_settings_field( $option['name'], $option['label'], [ $this, 'callback_' . $type ], $section . '_options', $section . '_section', $args );
			}
		}

		// creates our settings in the options table
		foreach ( $this->settings_sections as $section ) {
			// register_setting( $option_group, $option_name, $sanitize_callback );
			register_setting( $section['id'] . '_section', $section['id'], [ $this, 'sanitize_options' ] );
		}
	}

	/**
	 * Show navigations as tab
	 *
	 * Shows all the settings section labels as tab
	 */
	public function show_navigation() {
		$active_tab = '';

		if ( isset( $_GET[ 'tab' ] ) ) {
			$active_tab = $_GET[ 'tab' ];
		}
		$count = 0;

		$html = '<header class="header-wrap">';
		$html .= '<div class="header-nav-wrap">';

		foreach ( $this->settings_sections as $tab ) {
			if ( $active_tab === $tab['id'] || ( 0 == $count && '' == $active_tab ) ) {
				$active_class = 'nav-tab-active';
			} else {
				$active_class = '';
			}
			$link_args = array_merge( $_GET, [ 'tab' => $tab['id'] ] );
			$page_link = add_query_arg( [ $link_args ], admin_url( 'admin.php' ) );
			$title     = isset( $tab['menu_title'] ) ? $tab['menu_title'] : $tab['title'];
			$html .= sprintf( '<a href="%1$s" class="nav-tab-menu %2$s" id="">%3$s</a>', $page_link, $active_class, $title );
			++$count;
		}
		$html .= '</div>';
		$html .= '</header>';

		echo $html;
	}

	/**
	 * Show the section settings forms
	 *
	 * This function displays every sections in a different form
	 */
	public function show_forms() {
		if ( isset( $_GET['tab'] ) && ! empty( $_GET['tab'] ) ) {
			$active_tab = $_GET['tab'];
		} else {
			$active_tab = $this->settings_sections[0]['id'];
		}
		$key  = array_search( $active_tab, array_column( $this->settings_sections, 'id' ) );
		$form = $this->settings_sections[ $key ]; ?>
			<div class="form-wrap">
				<form method="post" action="options.php">
					<?php do_action( 'wpappsdev_form_top_' . $form['id'], $form ); ?>
					<?php settings_fields( $form['id'] . '_section' ); ?>
					<?php $this->do_settings_sections( $form['id'] . '_options' ); ?>
					<?php do_action( 'wpappsdev_form_bottom_' . $form['id'], $form ); ?>
					<?php submit_button(); ?>
				</form>
			</div>
		<?php
		$this->script();
	}

	public function do_settings_sections( $page ) {
		global $wp_settings_sections, $wp_settings_fields;

		if ( ! isset( $wp_settings_sections[ $page ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_sections[ $page ] as $section ) {
			if ( $section['title'] ) {
				echo "<h2>{$section['title']}</h2>\n";
			}

			if ( $section['callback'] ) {
				call_user_func( $section['callback'], $section );
			}

			if ( ! isset( $wp_settings_fields ) || ! isset( $wp_settings_fields[ $page ] ) || ! isset( $wp_settings_fields[ $page ][ $section['id'] ] ) ) {
				continue;
			}
			echo '<table class="form-table" role="presentation">';
			$this->do_settings_fields( $page, $section['id'] );
			echo '</table>';
		}
	}

	public function do_settings_fields( $page, $section ) {
		global $wp_settings_fields;

		if ( ! isset( $wp_settings_fields[ $page ][ $section ] ) ) {
			return;
		}

		foreach ( (array) $wp_settings_fields[ $page ][ $section ] as $field ) {
			$class      = '';
			$class_args = [];

			if ( 'html' === $field['args']['type'] ) {
				$class_args[] = 'settings_section_heading';
			}

			if ( ! empty( $field['args']['class'] ) ) {
				$class_args[] = $field['args']['class'];
			}

			if ( ! empty( $class_args ) ) {
				$class = ' class="' . esc_attr( implode( ' ', $class_args ) ) . '"';
			}

			echo "<tr{$class}>";

			if ( 'html' === $field['args']['type'] ) {
				echo '<th colspan="2" scope="row"><b>' . $field['title'] . '</b></th>';
			} else {
				if ( ! empty( $field['args']['label_for'] ) ) {
					echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
				} else {
					echo '<th scope="row">' . $field['title'] . '</th>';
				}

				echo '<td>';
				call_user_func( $field['callback'], $field['args'] );
				echo '</td>';
			}
			echo '</tr>';
		}
	}

	public function callback_image( $args ) {
		include __DIR__ . '/fields/image-field1.php';
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_button( $args ) {
		$value = isset( $args['value'] ) ? $args['value'] : '';
		$url   = ! empty( $args['url'] ) ? $args['url'] : '#';

		// <a href="#" class="button button-primary mplus-acbtn" data-user-id="1">Update Value</a>
		$html = sprintf( '<a href="%1$s" class="button button-primary" id="%2$s" >%3$s</a>', $url, $args['id'], $value );
		// $html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_text( $args ) {
		$value    = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size     = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$readonly = $args['readonly'] ? 'readonly' : '';

		$html = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" %5$s/>', $size, $args['section'], $args['id'], $value, $readonly );
		$html .= sprintf( '<label class="description"> %s</label>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_checkbox( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

		$html = sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
		$html .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on"%4$s />', $args['section'], $args['id'], $value, checked( $value, 'on', false ) );
		$html .= sprintf( '<label for="wpuf-%1$s[%2$s]"> %3$s</label>', $args['section'], $args['id'], $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_multicheck( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );

		$html = '';
		$html .= '<div class="multicheck-fields">';

		foreach ( $args['options'] as $key => $label ) {
			$checked = isset( $value[$key] ) ? $value[$key] : '0';
			$html .= '<span>';
			$html .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
			$html .= sprintf( '<label for="wpuf-%1$s[%2$s][%4$s]"> %3$s</label><br>', $args['section'], $args['id'], $label, $key );
			$html .= '</span>';
		}
		$html .= '</div>';
		$html .= sprintf( '<label class="description"> %s</label>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_radio( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );

		$html = '';

		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s"%4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
			$html .= sprintf( '<label for="wpuf-%1$s[%2$s][%4$s]"> %3$s</label><br>', $args['section'], $args['id'], $label, $key );
		}
		$html .= sprintf( '<span class="description"> %s</label>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_select( $args ) {
		$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$value_array = explode( '-', $value );
		// tj_print( explode( '-', $value));

		$html = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );

		foreach ( $args['options'] as $key => $label ) {
			$key_array = explode( '-', $key );
			// echo $key;
			if ( count( $key_array ) == 2 ) {
				if ( $key_array[0] == $value_array[0] ) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				// tj_print('nap');
			} else {
				if ( $key == $value ) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
			}

			$html .= sprintf( '<option value="%s" %s>%s</option>', $key, $selected, $label );
		}
		$html .= sprintf( '</select>' );
		$html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_textarea( $args ) {
		$value = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]">%4$s</textarea>', $size, $args['section'], $args['id'], $value );
		$html .= sprintf( '<br><span class="description"> %s</span>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_html( $args ) {
		echo $args['desc'];
	}

	/**
	 * Displays a rich text textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_wysiwyg( $args ) {
		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';

		echo '<div style="width: ' . $size . ';">';

		wp_editor( $value, $args['section'] . '-' . $args['id'] . '', [ 'teeny' => true, 'textarea_name' => $args['section'] . '[' . $args['id'] . ']', 'textarea_rows' => 10 ] );

		echo '</div>';

		echo sprintf( '<br><span class="description"> %s</span>', $args['desc'] );
	}

	/**
	 * Displays a file upload field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_file( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$id    = $args['section'] . '[' . $args['id'] . ']';
		$js_id = $args['section'] . '\\\\[' . $args['id'] . '\\\\]';
		$html  = sprintf( '<input type="text" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html .= '<input type="button" class="button wpsf-browse" id="' . $id . '_button" value="Browse" />
		<script type="text/javascript">
		jQuery(document).ready(function($){
			$("#' . $js_id . '_button").click(function() {
				tb_show("", "media-upload.php?post_id=0&amp;type=image&amp;TB_iframe=true");
				window.original_send_to_editor = window.send_to_editor;
				window.send_to_editor = function(html) {
					var url = $(html).attr(\'href\');
					if ( !url ) {
						url = $(html).attr(\'src\');
					};
					$("#' . $js_id . '").val(url);
					tb_remove();
					window.send_to_editor = window.original_send_to_editor;
				};
				return false;
			});
		});
		</script>';
		$html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a password field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_password( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html .= sprintf( '<span class="description"> %s</span>', $args['desc'] );

		echo $html;
	}

	/**
	 * Displays a color picker field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_color( $args ) {
		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
		$html .= sprintf( '<span class="description" style="display:block;"> %s</span>', $args['desc'] );

		echo $html;
	}

	/**
	 * Sanitize callback for Settings API
	 */
	public function sanitize_options( $options ) {
		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );

			// If callback is set, call it
			if ( $sanitize_callback ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );

				continue;
			}
		}

		return $options;
	}

	/**
	 * Get sanitization callback for given option slug
	 *
	 * @param string $slug option slug
	 *
	 * @return mixed string or bool false
	 */
	public function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}

		// Iterate over registered fields and see if we can find proper callback
		foreach ( $this->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option['name'] != $slug ) {
					continue;
				}

				// Return the callback name
				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}

		return false;
	}

	/**
	 * Get the value of a settings field
	 *
	 * @param string $option  settings field name
	 * @param string $section the section name this field belongs to
	 * @param string $default default text if it's not found
	 *
	 * @return string
	 */
	public function get_option( $option, $section, $default = '' ) {
		$options = get_option( $section );

		if ( isset( $options[$option] ) ) {
			return $options[$option];
		}

		return $default;
	}

	/**
	 * Get allowed html for wp_kses.
	 *
	 * @return array
	 */
	public function wpadpcbu_allowed_html() {
		return [
			'img' => [
				'width'   => [],
				'height'  => [],
				'src'     => [],
				'class'   => [],
				'srcset'  => [],
				'sizes'   => [],
				'loading' => [],
			],
			'option' => [
				'value'    => [],
				'selected' => [],
			],
		];
	}

	/**
	 * Tabbable JavaScript codes & Initiate Color Picker
	 *
	 * This code uses localstorage for displaying active tabs
	 */
	public function script() {
		?>
		<script>
			jQuery(document).ready(function($) {
				//Initiate Color Picker
				$('.wp-color-picker-field').wpColorPicker();
				// Switches option sections
				// $('.group').hide();
				// var activetab = '';
				// if (typeof(localStorage) != 'undefined' ) {
				// 	activetab = localStorage.getItem("activetab");
				// }
				// if (activetab != '' && $(activetab).length ) {
				// 	$(activetab).fadeIn();
				// } else {
				// 	$('.group:first').fadeIn();
				// }
				// $('.group .collapsed').each(function(){
				// 	$(this).find('input:checked').parent().parent().parent().nextAll().each(
				// 	function(){
				// 		if ($(this).hasClass('last')) {
				// 			$(this).removeClass('hidden');
				// 			return false;
				// 		}
				// 		$(this).filter('.hidden').removeClass('hidden');
				// 	});
				// });

				// if (activetab != '' && $(activetab + '-tab').length ) {
				// 	$(activetab + '-tab').addClass('nav-tab-active');
				// }
				// else {
				// 	$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
				// }
				// $('.nav-tab-wrapper a').click(function(evt) {
				// 	$('.nav-tab-wrapper a').removeClass('nav-tab-active');
				// 	$(this).addClass('nav-tab-active').blur();
				// 	var clicked_group = $(this).attr('href');
				// 	if (typeof(localStorage) != 'undefined' ) {
				// 		localStorage.setItem("activetab", $(this).attr('href'));
				// 	}
				// 	$('.group').hide();
				// 	$(clicked_group).fadeIn();
				// 	evt.preventDefault();
				// });
			});
		</script>

		<style type="text/css">
			/** WordPress 3.8 Fix **/
			/*.form-table th { padding: 20px 10px; }
			#wpbody-content .metabox-holder { padding-top: 5px; }
			.form-wrap p, p.description, p.help, span.description {
			  font-size: 13px;
			  font-style: italic;
			  display: block;
			}
			.wp-picker-container input[type=text].wp-color-picker {
				text-align: left!important;
			}*/

			/* .settings-body {
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
				-ms-interpolation-mode: nearest-neighbor;
				image-rendering: optimizeQuality;
				text-rendering: optimizeLegibility;
				display: flex;
				color: #121116;
				font-size: .875rem;
				line-height: 1.5;
			} */
.settings-body {
    border: 1px solid #c8d7e1;
    display: flex;
    position: relative;
    background: #fff;
    padding: 20px 20px 100px;
	min-height: 600px;
}
header.header-wrap {
    width: 340px;
    padding: 14px 16px 30px 24px;
    overflow: hidden;
    background: #fafbff;
    box-shadow: 0 4px 4px rgba(0,0,0,.25);
    box-sizing: border-box;
    margin-right: 12px;
    border: 1px solid #c8d7e1;
    border-top-color: hsla(0,3.9%,80%,.5215686274509804);
    border-bottom: none;
}
.form-wrap{
    flex: 3;
    padding: 0 6px 0 3%!important;
    position: relative;
}
.form-wrap:before {
    top: 0;
    left: 0;
    width: 1px;
    height: 100%;
    content: "";
    position: absolute;
    background: #e5e5e5;
}
tr.settings_section_heading {
    border-left: 4px solid #7ad03a;
    border-bottom: 1px solid #e7e7e7;
}

tr.settings_section_heading th {
    font-size: 1rem;
    padding: 10px;
}
			a.nav-tab-menu {
				position: relative;
				display: block;
				padding: 16px 44px 18px 20px;
				text-decoration: none;
				color: #121116;
				border-top: 1px solid #E0E4E9;
				border-left: 2px solid transparent;
				overflow: hidden;
				transition: all 100ms ease-out;
				-webkit-transition: all 100ms ease-out;
				font-size: .8125rem;
				line-height: 1.46154;
				font-weight: bold;
				text-transform: uppercase;
			}
			a.nav-tab-menu:hover,
			a.nav-tab-menu.nav-tab-active {
				color: #121116;
				background: #fff;
				border-left: 2px solid #F56640;
			}
			a.nav-tab-menu:focus,
			a.nav-tab-menu.nav-tab-active:focus{
				box-shadow: none;
			}
			/* .form-wrap {
				position: relative;
				background: #fff;
				padding-left: 25px;
				width: 80%;
				display: inline-block;
			} */

			.section-header-wrap {
				position: relative;
				border-bottom: 1px solid #E0E4E9;
				padding-bottom: 24px;
			}
			.section-header-wrap:before {
				content: '';
				position: absolute;
				display: block;
				width: 48px;
				height: 2px;
				bottom: -1px;
				left: 0;
				background: #F56640;
			}
			h2.section-title {
				font-size: 1.625rem;
				line-height: 48px;
				font-weight: 600;
				letter-spacing: 0.01em;
				margin: 0px;
			}
			.form-table td {
				/* display: inline-table; */
			}
			.multicheck-fields {
				display: inline-flex;
			}
			.multicheck-fields span {
				display: -webkit-inline-box;
				margin-right: 10px;
			}
			div.wpappsdev_display_image img {
				max-height: 200px;
				max-width: 100%;
				height: auto;
				width: auto !important;
			}
		</style>
		<?php
	}
}
