<?php
/**
 * ==================================
 * Action: pmxi_saved_post
 * ==================================
 *
 * Called after a post is created/updated by WP All Import.
 *
 * @param $post_id int               - The id of the post just created/updated
 * @param $xml_node SimpleXMLElement - An object holding values for the current record
 *
 */
function my_saved_post($post_id, $xml_node)
{
    /*
     * Here you can use standard WordPress functions like get_post_meta() and get_post() to
     * retrieve data, make changes and then save them with update_post() and/or update_post_meta()
     *
     * There are two ways to access the data from the current record in your import file:
     *
     * 1) Custom fields. For example, you could import a value to a custom field called "_temp" and
     *  then retrieve it here. Since it's only temporary, you'd probably want to delete it immediately:
     *
     *     $my_value = get_post_meta($post_id, "_temp", true);
     *     delete_post_meta($post_id,"_temp");
     *
     * 2) The $xml param (a SimpleXMLElement object). This can be complex to work with if you're not
     * used to iterators and/or xpath syntax. It's usually easiest to convert it a nested array using:
     *
     *     $record = json_decode(json_encode((array) $xml_node), 1);
     *
     */
}

add_action('pmxi_saved_post', 'my_saved_post', 10, 2);


// ----------------------------
// Example uses below
// ----------------------------

/**
 * Append data to a custom field. Requires importing the data to be appended into
 * a temporary custom field in the import.
 *
 * @param $id
 */
function custom_field_append($id)
{
    $value = get_post_meta($id, 'your_meta_key', true);
    $temp = get_post_meta($id, '_temp', true);
    update_post_meta($id, 'your_meta_key', $value . $temp);
    delete_post_meta($id, '_temp');
}

add_action('pmxi_saved_post', 'custom_field_append', 10, 1);


/**
 * Conditionally update a custom field based on the value of a different field.
 *
 * @param $id
 */
function conditional_update($id)
{
    $check = get_post_meta($id, '_my_update_check', true);

    if ($check === 'yes') {
        $new_value = get_post_meta($id, '_my_new_value', true);
        update_post_meta($id, '_my_new_value', $new_value);
    }
}

add_action('pmxi_saved_post', 'conditional_update', 10, 1);


/**
 * Conditionally delete a custom field based on the value of a different field.
 *
 * @param $id
 */
function conditional_delete($id)
{
    $check = get_post_meta($id, '_my_delete_check', true);
    if ($check === 'yes') {
        delete_post_meta($id, '_my_field');
    }
}

add_action('pmxi_saved_post', 'conditional_delete', 10, 1);


/*
 * In some cases it's desirable to have the featured image also appear in the product
 * gallery in WooCommerce. This function will prepend the featured image to the gallery.
 */
function copy_featured_img_to_gallery($post_id)
{
	$gallery = explode(",",get_post_meta($post_id, "_product_image_gallery", true));
	array_unshift($gallery, get_post_thumbnail_id( $post_id ));
	$gallery = array_unique($gallery);
	update_post_meta($post_id, "_product_image_gallery", implode(",",$gallery));
}

add_action('pmxi_saved_post', 'copy_featured_img_to_gallery', 10, 2);


/*
 * Need to import data to custom database tables along with your imported items? This should give
 * you a general idea how, but would require modification for your exact needs.
 *
 * This is provided with the hope it will be useful but importing to custom tables is *not* officially supported.
 * This is very advanced usage. It's only advised for developers with experience directly reading/writing MySQL tables.
 */
function custom_database_table_query($id) 
{
    global $wpdb; 
    $value = get_post_meta($id, '_your_temp_field', true);
    $table_name = $wpdb->prefix . "your_table_name";
    $wpdb->insert($table_name, array('post_id' => $id, 'column_name' => $value), array('%s','%s'));
    delete_post_meta($id, '_your_temp_field');
}


