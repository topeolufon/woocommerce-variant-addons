<?php
/**
Plugin Name: WooCommerce Variant Addons by Tope
Author: Tope Olufon
Author URI: http://www.topeolufon.com/
Description: Add addons to variants
Version: 1.0
*/

add_action( 'woocommerce_product_after_variable_attributes', 'woo_variable_fields', 10, 3 );
add_action( 'woocommerce_save_product_variation', 'save_variation_fields', 10, 2 );
function woo_variable_fields( $loop, $variation_data, $variation ) {
  echo '<div class="variation-custom-fields">';
  ?>
<p class="form-field">
        <label for="addon_<?php echo $variation->ID;?>">
          <?php _e( 'Add-on', 'woocommerce' );?>
        </label>
        <select class="wc-product-search" style="width: 100%;" id="addon_<?php echo $variation->ID;?>" name="addon_<?php echo $variation->ID;?>" 
        data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>" 
        data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo intval( $post->ID ); ?>">
        <option><?php echo get_the_title(get_post_meta($variation->ID, 'addon', true));?></option>
        </select>
</p>
<?php
  echo "</div>"; 
}

/** Save new fields for variations */
function save_variation_fields( $variation_id) {
  if (is_numeric($_POST['addon_'.$variation_id])){
    $addon = stripslashes( $_POST['addon_'.$variation_id]);
    update_post_meta( $variation_id, 'addon', esc_attr( $addon));
    // var_dump($_POST['addon_'.$variation_id]);
  }
}

// Custom Product Variation
add_filter( 'woocommerce_available_variation', 'custom_load_variation_settings_products_fields' );

function custom_load_variation_settings_products_fields( $variations ) {
  $variations['variation_addon'] = get_post_meta( $variations[ 'variation_id' ], 'addon', true );  
  return $variations;
}


add_action( 'woocommerce_before_add_to_cart_button', 'add_cf_before_addtocart_in_single_products', 1, 0 );
function add_cf_before_addtocart_in_single_products()
{
    global $product;
   if ($product->is_type('variable')){
    ?>
    <div class="addons">
    <hr>
    <?php
    $variants = $product->get_available_variations();
    foreach ($variants as $k => $v){
      if(!empty($v["variation_addon"])){
      $variant_id = $v["variation_id"];
      $addon_id = $v["variation_addon"];

      $addon = wc_get_product($addon_id);
      $addon_price= $addon->price;
      $addon_name= $addon->name;
      $addon_parent = $addon->parent_id;
      echo "<div class='addon_list' id='$variant_id' data-addon_id='$addon_id' data-parent_id='$addon_parent'>
      
      <label id='$addon_id' for='$addon_id'>Type the number of $addon_name"."s at $<span class='active_addon_price'>$addon_price</span> you need</label>
      <input class='addon_number' type=number min=0 name='$addon_id'></div>";
      }
    }
    ?>
     <p id="addon_total"></p>
    </div>
<style>
.addon_list{
  display:none;
}
.addon_list label{
  margin-right: 1em;
}

.addon_number{
  width: 100%; 
  height: 1.5em;
}

.active_addon{
  display: block;
}

#addon_total, .custom_grand_total{
  font-weight:600;
}
</style>
<script>
document.addEventListener("DOMContentLoaded", function(){
var add_ons =[];
var variants_data = jQuery(".variations_form").data("product_variations")
jQuery.each(variants_data, function( index, value ) {
  if(this["variation_addon"] != ""){
	add_ons[this["variation_id"]]=this["display_price"];
  }
});

  jQuery(".woocommerce-variation-add-to-cart").before("<p class='custom_grand_total'></p>");
jQuery(".variation_id[name=variation_id]").change(function() {
	var addon_div =  jQuery(this).val();
	jQuery(".addon_list").removeClass("active_addon");
	jQuery("#"+addon_div).addClass("active_addon");
	jQuery("#addon_total, .custom_grand_total").text("");
});

jQuery(".addons").on('change','.active_addon .addon_number', function(){
	var currency = jQuery(".woocommerce-variation-price .woocommerce-Price-amount").text();
	var variant_price = Number(currency.replace(/[^0-9\.-]+/g,""));
	var addon_price = Number(jQuery(".active_addon .active_addon_price").text());
	var addon_amount = Number(jQuery(".active_addon .addon_number").val());
	var addon_total = addon_price * addon_amount;
	jQuery("#addon_total").text("Addons: $"+Number(parseFloat( addon_total)).toFixed(2) );
	jQuery(".custom_grand_total").text("Grand Total : $"+(Number(parseFloat( addon_total+variant_price)).toFixed(2)));
});

jQuery(".single_add_to_cart_button").click(function() {
if (jQuery(".active_addon .addon_number").val()){
	event.preventDefault();
	var url = "/?add-to-cart="+jQuery(".active_addon").attr("data-parent_id")+"&variation_id="+jQuery(".active_addon").attr("data-addon_id")+"&quantity="+jQuery(".active_addon .addon_number").val();
	jQuery.get( url, function() {
		jQuery("form input").attr("readonly", true);
		jQuery(".single_add_to_cart_button").text("Please wait...");
		jQuery(".single_add_to_cart_button").css("background", "grey");
	})
	  .done(function() {
		jQuery(".single_add_to_cart_button").unbind('click').trigger('click'); 
	  })
	  .fail(function() {
		alert( "Sorry, there was an error" );
		location.reload();
	})
}
});
});
</script>
    <?php
    }
    // if( !empty( $pd_number ) )

}
