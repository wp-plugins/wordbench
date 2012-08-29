<?php

function wordbench_post_type_init() {
	$labels = array(
		'name'               => 'Post Types',
		'menu_name'          => 'Post Types',
		'singular_name'      => 'Post Type',
		'name_admin_bar'     => 'Post Type',
		'add_new'            => 'Add New',
		'add_new_item'       => 'Add New Post Type',
		'new_item'           => 'New Post Type',
		'edit_item'          => 'Edit Post Type',
		'view_item'          => 'View Post Type',
		'all_items'          => 'All Post Types',
		'search_items'       => 'Search Post Types',
		'not_found'          => 'No post types found',
		'not_found_in_trash' => 'No post types found in Trash',
		'parent_item_colon'  => 'Parent Post Type:'
	);

	$args = array(
		'labels'              => $labels,
		'description'         => "A custom post type for managing your custom post types...There is no spoon.",
		'public'              => true,
		'publicly_queryable'  => false, // Default for 'public' => false
		'exclude_from_search' => true,  // Default for 'public' => false
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => false, // Default for 'public' => false
		'capability_type'     => 'post_type',
		'map_meta_cap'        => true,
		'hierarchical'        => true,
		'has_archive'         => false,
		'can_export'          => false,
		'rewrite'             => false,
		'query_var'           => false,
		'supports'            => array( 'title', 'editor', 'page-attributes' )
	);
	
	register_post_type( 'post_type', $args );
	
	$role = get_role( 'administrator' );
	$cap  = get_post_type_object( 'post_type' )->cap;
	
	if ( ! $role->has_cap( $cap->edit_posts ) ) {
		global $wp_roles;
		foreach ( $wp_roles->get_names as $role_name ) {
			$role = get_role( $role_name );
			foreach ( (array) $cap as $cap_key => $cap_value ) {
				if ( $role->has_cap( $cap_key ) ) {
					$role->add_cap( $cap_value );
				}
			}
		}
	}
	
	$post_types = get_posts( array(
		'post_type'      => 'post_type',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'post_title',
		'order'          => 'ASC' 
	) );
	
	foreach ( $post_types as $post ) {
		WB_Post_Type::register( $post );
	}
}

function wordbench_post_type_meta_boxes() {
	add_meta_box( 'wb-post-type-caps', 'Capabilities',
		'wordbench_post_type_meta_caps', 'post_type', 'advanced' );
	add_meta_box( 'wb-post-type-labels', 'Labels',
		'wordbench_post_type_meta_labels', 'post_type', 'normal' );
	add_meta_box( 'wb-post-type-fields', 'Fields',
		'wordbench_post_type_meta_fields', 'post_type', 'normal' );
	add_meta_box( 'wb-post-type-settings', 'Settings',
		'wordbench_post_type_meta_settings', 'post_type', 'side' );
	add_meta_box( 'wb-post-type-supports', 'Supports',
		'wordbench_post_type_meta_supports', 'post_type', 'side' );
	
	$post_types = WB_Post_Type::fetch_all();
	
	foreach ( $post_types as $post_type ) {
		add_meta_box( 'wb-post-type-meta-box',
			$post_type->get_label( 'edit_item' ),
			'wordbench_post_type_meta_edit',
			$post_type->get_name(),
			'advanced', 'default',
			$post_type->get_fields() );
	}
}

function wordbench_post_type_meta_caps( $post, $args = array() ) {
	global $wp_roles;
	
	$roles = $wp_roles->get_names();
	$caps  = (array) get_post_type_object( 'post_type' )->cap;
	
	$post_type_caps = get_post_meta( $post->ID, '_post_type_caps', true );
	
	if ( empty( $post_type_caps ) ) {
		foreach ( array_keys( $roles ) as $role_key ) {
			$role = get_role( $role_key );
			foreach ( array_keys( $caps ) as $cap_key ) {
				$post_type_caps[$role_key][$cap_key] = $role->has_cap( $cap_key );
			}
		}
	}
?>
<table class="wb-form-table">
	<thead>
		<tr>
			<th></th>
			<?php foreach ( $roles as $role_key => $role_title ) : ?>
			<th style="text-align: center;">
				<input id="role-<?php echo $role_key; ?>" type="checkbox">
				<label for="role-<?php echo $role_key; ?>"><?php echo $role_title; ?></label>
			</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $caps as $cap_key => $cap_name ) :
			if ( ! in_array( $cap_key, array( 'read_post', 'edit_post', 'delete_post' ) ) ) :
				$cap_title = wordbench_labelize( $cap_key );
		?>
		<tr>
			<th scope="row">
				<input id="cap-<?php echo $cap_key; ?>" type="checkbox">
				<label for="cap-<?php echo $cap_key; ?>"><?php echo $cap_title; ?></label>
			</th>
			<?php foreach ( $roles as $role_key => $role_title ) :
				$name = "post_type_meta[caps][{$role_key}][{$cap_key}]";
				$checked = $post_type_caps[$role_key][$cap_key] ? ' checked="checked"' : '';
			?>
			<td style="text-align: center;">
				<input type="checkbox" name="<?php echo $name; ?>"<?php echo $checked; ?>>
			</td>
			<?php endforeach; ?>
		</tr>
		<?php endif; endforeach; ?>
	</tbody>
</table>
<?php
}

function wordbench_post_type_meta_labels( $post, $args = array() ) {
	$post_type_labels = get_post_meta( $post->ID, '_post_type_labels', true );
	
	if ( empty( $post_type_labels ) ) $post_type_labels = array();
	
	$labels = WB_Post_Type::get_static_labels();
?>
<script type="text/javascript">
	jQuery(window).ready(function($) {
		$('#wb-gen-labels').click(function(event) {
			var title = $('#title').val();
			
			var plural_title = title;
			var single_title = title;
			
			if ('s' == title.charAt(title.length - 1)) {
				single_title = title.substr(0, title.length - 1);
			} else {
				plural_title = title + 's';
			}
			
			$('#wb-labels-table input[type="text"]').each(function() {
				$(this).val($(this).data('default')
					.replace('%S', single_title)
					.replace('%s', single_title.toLowerCase())
					.replace('%P', plural_title)
					.replace('%p', plural_title.toLowerCase())
				);
			});
		});
	});
</script>
<span class="description">Default labels can be generated from the post type title.</span>
<input id="wb-gen-labels" type="button" value="Generate" class="button">
<table id="wb-labels-table" class="wb-form-table">
	<thead>
		<tr>
			<th class="snap">Label Name</th>
			<th class="snap">Label</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $labels as $key => $label ) :
			$title = wordbench_labelize( $key );
		?>
		<tr>
			<td class="snap">
				<label for="wb-label-<?php echo $key; ?>"><?php echo $title; ?></label>
			</td>
			<td class="snap">
				<input id="wb-label-<?php echo $key; ?>" type="text" size="40"
						name="post_type_meta[labels][<?php echo $key; ?>]"
						value="<?php esc_attr_e( $post_type_labels[$key] ); ?>"
						data-default="<?php esc_attr_e( $label['default'] ); ?>">
			</td>
			<td class="description"><?php echo $label['descrip']; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php
}

function wordbench_post_type_meta_fields( $post, $args = array() ) {
	$post_type_fields = get_post_meta( $post->ID, '_post_type_fields', true );
	
	if ( empty( $post_type_fields ) ) $post_type_fields = array();
?>
<script type="text/javascript">
	jQuery(window).ready(function($) {
		$('.wb-add-field').click(function(event) {
			var nameCell = $('<td/>', {
				'class': 'snap'
			}).append($('<input/>', {
				'type': 'text',
				'size': '30',
				'name': 'post_type_meta[fields][title][]'
			}));
			
			var typeCell = $('<td/>', {
				'class': 'snap'
			}).append($('<select/>', {
				'name': 'post_type_meta[fields][type][]'
			})
			<?php foreach ( wordbench_post_type_field_types() as $type => $name ) : ?>
			.append($('<option/>', { 'value': '<?php echo $type; ?>' }).text('<?php echo $name; ?>'))
			<?php endforeach; ?>			
			);
			
			var optionCell = $('<td/>').append($('<textarea/>', {
				'name': 'post_type_meta[fields][opts][]'
			}));
			
			var removeCell = $('<td/>', {
				'class': 'snap'
			}).append($('<a/>', {
				'href':  '#',
				'title': 'Remove Field',
				'class': 'wb-remove-field button'
			}).text('- Remove Field'));
			
			var row = $('<tr/>')
				.append(nameCell)
				.append(typeCell)
				.append(optionCell)
				.append(removeCell);
			
			$('#wb-fields-table tbody').append(row);
			
			return false;
		});
		
		$('.wb-remove-field').live('click', function(event) {
			if ( confirm( 'Are you sure you want to remove this field?' ) )
				$(event.target).parents('tr').first().remove();
			
			return false;
		});
	});
</script>
<table id="wb-fields-table" class="wb-form-table">
	<thead>
		<tr>
			<th class="snap">Field Name</th>
			<th class="snap">Type</th>
			<th>
				<span>Options</span>
				<span style="font-size: smaller; font-weight: normal; font-style: italic;">One per line</span>
			</th>
			<th class="snap"></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $post_type_fields as $field ) :
			$opt_count = count( $field['opts'] );
			$row_count = max( min( $opt_count, 5 ), 2 );
		?>
		<tr>
			<td class="snap">
				<input type="text" size="30" name="post_type_meta[fields][title][]"
					value="<?php esc_attr_e( $field['title'] ); ?>">
			</td>
			<td class="snap">
				<select name="post_type_meta[fields][type][]">
					<?php foreach ( wordbench_post_type_field_types() as $type => $name ) : ?>
					<option value="<?php esc_attr_e( $type ); ?>"<?php if ( $type == $field['type'] )
						echo ' selected="selected"'; ?>><?php echo $name; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<textarea name="post_type_meta[fields][opts][]" rows="<?php echo $row_count; ?>"><?php echo implode( PHP_EOL, $field['opts'] ); ?></textarea>
			</td>
			<td class="snap">
				<a href="#" title="Remove Field" class="wb-remove-field button">- Remove Field</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="4">
				<a href="#" title="Add Field" class="wb-add-field button">+ Add Field</a>
			</td>
		</tr>
	</tfoot>
</table>
<?php
}

function wordbench_post_type_meta_settings( $post, $args = array() ) {
	$post_type_settings = get_post_meta( $post->ID, '_post_type_settings', true );
	
	if ( empty( $post_type_settings ) )
		$post_type_settings = WB_Post_Type::get_default_settings();
	
	$settings = WB_Post_Type::get_static_settings();
?>
<ul>
	<?php foreach ( $settings as $key => $setting ) :
		$checked = $post_type_settings[$key] ? ' checked="checked"' : '';
		$title = wordbench_labelize( $key );
	?>
	<li>
		<input id="wb-settings-<?php echo $key; ?>"<?php echo $checked; ?>
			type="checkbox" name="post_type_meta[settings][<?php echo $key; ?>]">
		<label for="wb-settings-<?php echo $key; ?>"
			title="<?php esc_attr_e( $setting['descrip'] ); ?>">
			<?php echo $title; ?></label>
	</li>
	<?php endforeach; ?>
</ul>
<?php
}

function wordbench_post_type_meta_supports( $post, $args = array() ) {
	$post_type_supports = get_post_meta( $post->ID, '_post_type_supports', true );
	
	if ( empty( $post_type_supports ) )
		$post_type_supports = WB_Post_Type::get_default_supports();
	
	$supports = WB_Post_Type::get_static_supports();
?>
<ul>
	<?php foreach ( $supports as $key => $support ) :
		$checked = $post_type_supports[$key] ? ' checked="checked"' : '';
		$title = wordbench_labelize( $key );
	?>
	<li>
		<input id="wb-supports-<?php echo $key; ?>"<?php echo $checked; ?>
			type="checkbox" name="post_type_meta[supports][<?php echo $key; ?>]">
		<label for="wb-supports-<?php echo $key; ?>"
			title="<?php esc_attr_e( $support['descrip'] ); ?>">
			<?php echo $title; ?></label>
	</li>
	<?php endforeach; ?>
</ul>
<?php
}

function wordbench_post_type_meta_edit( $post, $args = array() ) {
?>
<table>
	<tbody>
		<?php foreach ( $args['args'] as $field ) :
			$value = get_post_meta( $post->ID, '_' . $field['name'], true );
		?>
		<tr>
			<td><label for="post_meta-<?php esc_attr_e( $field['name'] ); ?>"><?php echo $field['title']; ?></label></td>
			<td>
				<?php wordbench_the_form_element( $field, array(
					'show_label' => false,
					'prefix'     => 'post_meta',
					'value'      => $value
				) ); ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<?php
}

function wordbench_post_type_save_post( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	
	if ( wp_is_post_revision( $post_id ) ) return;
	
	// this might be redundant
	if ( 'auto-draft' == get_post( $post_id )->post_status ) return;
	
	if ( 'post_type' == $_REQUEST['post_type'] ) {
		if ( ! current_user_can( 'edit_post_type', $post_id ) ) return;
		
		$meta = $_REQUEST['post_type_meta'];
		
		$caps     = array();
		$labels   = array();
		$fields   = array();
		$settings = array();
		$supports = array();
		
		if ( isset( $meta['caps'] ) && is_array( $meta['caps'] ) ) {
			global $wp_roles;
			
			$static_roles = $wp_roles->get_names();
			$static_caps  = (array) get_post_type_object( 'post_type' )->cap;
			
			foreach ( array_keys( $static_roles ) as $role_key ) {
				foreach ( array_keys( $static_caps ) as $cap_key ) {
					$caps[$role_key][$cap_key] = isset( $meta['caps'][$role_key][$cap_key] );
				}
			}
		}
		
		if ( isset( $meta['labels'] ) && is_array( $meta['labels'] ) ) {
			$static_labels = WB_Post_Type::get_static_labels();
			
			foreach ( $static_labels as $key => $label ) {
				if ( ! empty( $meta['labels'][$key] ) )
					$labels[$key] = $meta['labels'][$key];
			}
		}
		
		if ( isset( $meta['fields'] ) && is_array( $meta['fields'] ) ) {
			for ( $i = 0, $n = count( $meta['fields']['title'] ); $i < $n; $i++ ) {
				if ( ! empty( $meta['fields']['title'][$i] ) ) {
					$name = wordbench_sanitize( $meta['fields']['title'][$i] );
					$opts = explode( PHP_EOL, $meta['fields']['opts'][$i] );
					
					foreach ( $opts as $index => $opt ) {
						$opts[$index] = trim( $opt );
						
						if ( empty( $opts[$index] ) )
							unset( $opts[$index] );
					}
					
					$fields[] = array(
						'name'  => $name,
						'title' => $meta['fields']['title'][$i],
						'type'  => $meta['fields']['type'][$i],
						'opts'  => $opts
					);
				}
			}
		}
		
		if ( isset( $meta['settings'] ) && is_array( $meta['settings'] ) ) {
			$static_settings = WB_Post_Type::get_static_settings();
			
			foreach ( $static_settings as $key => $setting ) {
				$settings[$key] = isset( $meta['settings'][$key] );
			}
		}
		
		if ( isset( $meta['supports'] ) && is_array( $meta['supports'] ) ) {
			$static_supports = WB_Post_Type::get_static_supports();
			
			foreach ( $static_supports as $key => $support ) {
				$supports[$key] = isset( $meta['supports'][$key] );
			}
		}
		
		update_post_meta( $post_id, '_post_type_caps',     $caps     );
		update_post_meta( $post_id, '_post_type_labels',   $labels   );
		update_post_meta( $post_id, '_post_type_fields',   $fields   );
		update_post_meta( $post_id, '_post_type_settings', $settings );
		update_post_meta( $post_id, '_post_type_supports', $supports );
		
		global $wp_rewrite;
		
		WB_Post_Type::register( get_post( $post_id ) );
		
		$wp_rewrite->flush_rules();
	} elseif ( @is_array( $_REQUEST['post_meta'] ) ) {
		$post_type = WB_Post_type::fetch( $_REQUEST['post_type'] );
		$post_meta = $_REQUEST['post_meta'];
		
		$fields = $post_type->get_fields();
		
		foreach ( $fields as $field ) {
			$element = wordbench_get_form_element( $field );
			
			$meta_key   = '_' . $field['name'];
			$meta_value = $post_meta[$field['name']];
			
			$meta_value = $element->validate( $meta_value );
			
			update_post_meta( $post_id, $meta_key, $meta_value );
		}
	}
}

function wordbench_post_type_field_types() {
	return array(
		'text'     => 'Inline Text',
		'textarea' => 'Block Text',
		'checkbox' => 'Check Box',
		'radio'    => 'Radio Button',
		'select'   => 'Drop-down Menu'
	);
}

?>