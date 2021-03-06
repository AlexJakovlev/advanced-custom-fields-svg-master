<?php

if( ! class_exists('acf_field_image_svg') ) :

	class acf_field_image_svg extends acf_field {


	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type    function
	*  @date    5/03/2014
	*  @since   5.0.0
	*
	*  @param   n/a
	*  @return  n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'svg';
		$this->label = __("Image_svg",'acf');
		$this->category = 'content';
		$this->defaults = array(
			'return_format' => 'array',
			'preview_size'  => 'thumbnail',
			'library'       => 'all',
			'min_width'     => 0,
			'min_height'    => 0,
			'min_size'      => 0,
			'max_width'     => 0,
			'max_height'    => 0,
			'max_size'      => 0,
			'mime_types'    => 'svg'
		);
		
		// filters
		add_filter('get_media_item_args',               array($this, 'get_media_item_args'));
		add_filter('wp_prepare_attachment_for_js',      array($this, 'wp_prepare_attachment_for_js'), 10, 3);

	}
	
	
	/*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type    function
	*  @date    16/12/2015
	*  @since   5.3.2
	*
	*  @param   $post_id (int)
	*  @return  $post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// localize
		acf_localize_text(array(
			'Select Image'  => __('Select Image', 'acf'),
			'Edit Image'    => __('Edit Image', 'acf'),
			'Update Image'  => __('Update Image', 'acf'),
			'All images'    => __('All images', 'acf'),
		));
	}
	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param   $field - an array holding all the field's data
	*
	*  @type    action
	*  @since   3.6
	*  @date    23/01/13
	*/
	
	function render_field( $field ) {
		
		// vars
		$uploader = acf_get_setting('uploader');
		
		
		// enqueue
		if( $uploader == 'wp' ) {
			acf_enqueue_uploader();
		}
		
		
		// vars
		$url = '';
		$alt = '';
		$div = array(
			'class'                 => 'acf-image-uploader',
			'data-preview_size'     => $field['preview_size'],
			'data-library'          => $field['library'],
			'data-mime_types'       => $field['mime_types'],
			'data-uploader'         => $uploader
		);
		
		// has value?
		$display = 'block';
		$field['select'] = '';
		if( $field['value'] ) {
			$pos_delimuter = strpos($field['value'], ':');
			// update vars
			if ($pos_delimuter) {
				$field['select'] = substr ($field['value'],$pos_delimuter);
				$field['value'] = substr ($field['value'], 0, $pos_delimuter);

			} 

			$url = wp_get_attachment_image_src($field['value'], $field['preview_size']);
			$alt = get_post_meta($field['value'], '_wp_attachment_image_alt', true);
			
			
			// url exists
			if( $url ) $url = $url[0];
			
			
			// url exists
			if( $url ) {
				$div['class'] .= ' has-value';
			}

		}
		// get size of preview value
		$size = acf_get_image_size($field['preview_size']);
		// $handle = fopen("/home/rasmus/file.txt", "r");
		// http://176.36.128.154:50080/test/wp-content/uploads/2019/03/icon.svg
		$file_name_svg = explode('/',$url);
		$file_name_svg = array_pop($file_name_svg);
		$ext = explode('.',$file_name_svg);
		$ext = array_pop($ext);
		$count =  0;

		// var_dump(wp_upload_dir()['path'].'/'.$file_name_svg);
		if ( 'svg' === $ext){
			$handle = file_get_contents($url);
			$xml = simplexml_load_string($handle);
			$xml->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');
			$query = "/svg:svg/svg:symbol/@id";
			$symbols_xml = $xml->xpath($query);
		// var_dump(count($symbols_xml));
			$count = count($symbols_xml);

			if ( $count  > 0 ) {
				$display = 'none';

			}
		}
		?>
		<div <?php acf_esc_attr_e( $div ); ?>>
			<?php acf_hidden_input(array( 'name' => $field['name'], 'value' => $field['value'].$field['select'] )); ?>
			<div class="show-if-value image-wrap" <?php if( $size['width'] ): ?>style="<?php echo esc_attr('max-width: '.$size['width'].'px'); ?>"


				<?php endif; ?>>
				<style>.svg-button.select{ background-color: #5cff2973; }</style>
				<img data-name="image" style='display: <?php echo $display ?>' src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>"/>
				<?php 
				if ($count) {
					echo '<div class="buttons_svg" id="'.$field['name'].'"><input data-name-acf-field="'.$field['name'].'" value="" type="hidden"><div style="color: blue;">Выберите иконку</div>';
					foreach ($symbols_xml as $value) {
						$id = strval($value);
						$data_id = $class = '';
						if ($id === $field['select']) {
							$data_id = $field['select'];
							$class = ' select';
						}
						echo '<button class="svg-button'.$class.'" data-acf-name="'.$field['name'].'" type="button" data-id="'.$id.'"><svg src="'.$url.'" style="height:20px; width:20px;"><use xlink:href="'.$url.'#'.$id.'"></use></svg></button>';
					}
					echo '</div>';
				} else {
			// echo '<img data-name="image" src="'.esc_url($url).'" alt="'.esc_attr($alt).'"/>';
				}

				?>
				<!-- <button class="svg-button" data-acf-name="acf[field_5c5971e234998][2][field_5c59740ca4d83]" type="button" data-id=":cabinet"><svg src="https://utec.ua/wp-content/uploads/2019/02/icon.svg" style="height:20px; width:20px;"><use xlink:href="https://utec.ua/wp-content/uploads/2019/02/icon.svg#:cabinet"></use></svg></button> -->
				<div class="acf-actions -hover" style="top: -30px">
					<?php 
					if( $uploader != 'basic' ): 
						?><a class="acf-icon -pencil dark" data-name="edit" href="#" title="<?php _e('Edit', 'acf'); ?>"></a><?php 
					endif;
					?><a class="acf-icon -cancel dark" data-name="remove" href="#" title="<?php _e('Remove', 'acf'); ?>"></a>
				</div>
			</div>
			<div class="hide-if-value">
				<?php if( $uploader == 'basic' ): ?>

					<?php if( $field['value'] && !is_numeric($field['value']) ): ?>
						<div class="acf-error-message"><p><?php echo acf_esc_html($field['value']); ?></p></div>
					<?php endif; ?>

					<label class="acf-basic-uploader">
						<?php acf_file_input(array( 'name' => $field['name'], 'id' => $field['id'] )); ?>
					</label>

					<?php else: ?>

						<p><?php _e('No image selected','acf'); ?> <a data-name="add" class="acf-button button" href="#"><?php _e('Add Image','acf'); ?></a></p>

					<?php endif; ?>
				</div>
			</div>

			<?php

		}


	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type    action
	*  @since   3.6
	*  @date    23/01/13
	*
	*  @param   $field  - an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// clear numeric settings
		$clear = array(
			'min_width',
			'min_height',
			'min_size',
			'max_width',
			'max_height',
			'max_size'
		);
		
		foreach( $clear as $k ) {
			
			if( empty($field[$k]) ) {
				
				$field[$k] = '';
				
			}
			
		}
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'         => __('Return Value','acf'),
			'instructions'  => __('Specify the returned value on front end','acf'),
			'type'          => 'radio',
			'name'          => 'return_format',
			'layout'        => 'horizontal',
			'choices'       => array(
				'array'         => __("Image Array",'acf'),
				'url'           => __("Image URL",'acf'),
				'id'            => __("Image ID",'acf')
			)
		));
		
		
		// preview_size
		acf_render_field_setting( $field, array(
			'label'         => __('Preview Size','acf'),
			'instructions'  => __('Shown when entering data','acf'),
			'type'          => 'select',
			'name'          => 'preview_size',
			'choices'       => acf_get_image_sizes()
		));
		
		
		// library
		acf_render_field_setting( $field, array(
			'label'         => __('Library','acf'),
			'instructions'  => __('Limit the media library choice','acf'),
			'type'          => 'radio',
			'name'          => 'library',
			'layout'        => 'horizontal',
			'choices'       => array(
				'all'           => __('All', 'acf'),
				'uploadedTo'    => __('Uploaded to post', 'acf')
			)
		));
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'         => __('Minimum','acf'),
			'instructions'  => __('Restrict which images can be uploaded','acf'),
			'type'          => 'text',
			'name'          => 'min_width',
			'prepend'       => __('Width', 'acf'),
			'append'        => 'px',
		));
		
		acf_render_field_setting( $field, array(
			'label'         => '',
			'type'          => 'text',
			'name'          => 'min_height',
			'prepend'       => __('Height', 'acf'),
			'append'        => 'px',
			'_append'       => 'min_width'
		));
		
		acf_render_field_setting( $field, array(
			'label'         => '',
			'type'          => 'text',
			'name'          => 'min_size',
			'prepend'       => __('File size', 'acf'),
			'append'        => 'MB',
			'_append'       => 'min_width'
		)); 
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'         => __('Maximum','acf'),
			'instructions'  => __('Restrict which images can be uploaded','acf'),
			'type'          => 'text',
			'name'          => 'max_width',
			'prepend'       => __('Width', 'acf'),
			'append'        => 'px',
		));
		
		acf_render_field_setting( $field, array(
			'label'         => '',
			'type'          => 'text',
			'name'          => 'max_height',
			'prepend'       => __('Height', 'acf'),
			'append'        => 'px',
			'_append'       => 'max_width'
		));
		
		acf_render_field_setting( $field, array(
			'label'         => '',
			'type'          => 'text',
			'name'          => 'max_size',
			'prepend'       => __('File size', 'acf'),
			'append'        => 'MB',
			'_append'       => 'max_width'
		)); 
		
		
		// allowed type
		acf_render_field_setting( $field, array(
			'label'         => __('Allowed file types','acf'),
			'instructions'  => __('Comma separated list. Leave blank for all types','acf'),
			'type'          => 'text',
			'name'          => 'mime_types',
		));
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type    filter
	*  @since   3.6
	*  @date    23/01/13
	*
	*  @param   $value (mixed) the value which was loaded from the database
	*  @param   $post_id (mixed) the $post_id from which the value was loaded
	*  @param   $field (array) the field array holding all the field options
	*
	*  @return  $value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) return false;
		
		
		// bail early if not numeric (error message)
		if( !is_numeric($value) ) return false;
		
		
		// convert to int
		$value = intval($value);
		
		
		// format
		if( $field['return_format'] == 'url' ) {

			return wp_get_attachment_url( $value );
			
		} elseif( $field['return_format'] == 'array' ) {
			
			return acf_get_attachment( $value );
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  get_media_item_args
	*
	*  description
	*
	*  @type    function
	*  @date    27/01/13
	*  @since   3.6.0
	*
	*  @param   $vars (array)
	*  @return  $vars
	*/
	
	function get_media_item_args( $vars ) {

		$vars['send'] = true;
		return($vars);
		
	}

	
	/*
	*  wp_prepare_attachment_for_js
	*
	*  this filter allows ACF to add in extra data to an attachment JS object
	*  This sneaky hook adds the missing sizes to each attachment in the 3.5 uploader. 
	*  It would be a lot easier to add all the sizes to the 'image_size_names_choose' filter but 
	*  then it will show up on the normal the_content editor
	*
	*  @type    function
	*  @since:  3.5.7
	*  @date    13/01/13
	*
	*  @param   {int}   $post_id
	*  @return  {int}   $post_id
	*/
	
	function wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
		
		// only for image
		if( $response['type'] != 'image' ) {

			return $response;
			
		}
		
		
		// make sure sizes exist. Perhaps they dont?
		if( !isset($meta['sizes']) ) {

			return $response;
			
		}
		
		
		$attachment_url = $response['url'];
		$base_url = str_replace( wp_basename( $attachment_url ), '', $attachment_url );
		
		if( isset($meta['sizes']) && is_array($meta['sizes']) ) {

			foreach( $meta['sizes'] as $k => $v ) {

				if( !isset($response['sizes'][ $k ]) ) {

					$response['sizes'][ $k ] = array(
						'height'      => $v['height'],
						'width'       => $v['width'],
						'url'         => $base_url .  $v['file'],
						'orientation' => $v['height'] > $v['width'] ? 'portrait' : 'landscape',
					);
				}
				
			}
			
		}

		return $response;
	}
	
	
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type    filter
	*  @since   3.6
	*  @date    23/01/13
	*
	*  @param   $value - the value which will be saved in the database
	*  @param   $post_id - the $post_id of which the value will be saved
	*  @param   $field - the field array holding all the field options
	*
	*  @return  $value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		// var_dump(acf_get_field_type('file')->update_value( $value, $post_id, $field ));
		// flush();
		return  $value;
		
	}
	
	
	/*
	*  validate_value
	*
	*  This function will validate a basic file input
	*
	*  @type    function
	*  @date    11/02/2014
	*  @since   5.0.0
	*
	*  @param   $post_id (int)
	*  @return  $post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		$pos=strpos($value,':');
		if ($pos) { $value = substr($value, 0,$pos);}
		
		return acf_get_field_type('file')->validate_value( $valid, $value, $field, $input );
		
	}
	
}


// initialize
acf_register_field_type( 'acf_field_image_svg' );

endif; // class_exists check

?>