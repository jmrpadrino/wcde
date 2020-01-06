jQuery(document).ready(function(){
    var inputHtml = '';
    var htmlString = '';
    var changed = false;    
    var changed2 = false;
    var basePrice = parseFloat(jQuery('.calculation.price').text().replace('€','').replace(',','.'));
    var newPrice = 0;
    
    inputHtml += '<div id="wc_single_dropdown_brands" class="wc-single-dropdown">';
    inputHtml += '<h2 class="pwc-layer-title wc-title">Select Brand and Model</h2>';
    inputHtml += '<select id="wc_single_dropdown_brand" name="wc_single_dropdown_brand" class="wc-single-dropdown wc-single-dropdown-brand" required>';
    inputHtml += '<option value="">Select Brand</option>';
    
    jQuery.each(wc_vars.parent_terms, function(index, value){
        inputHtml += '<option value="' + value.term_id + '">' + value.name + '</option>';
    })
    
    inputHtml += '</select>';
    inputHtml += '</div>';
    inputHtml += '<div id="wc_single_custom_field" class="wc-single-custom-field">';
    /*
    inputHtml += '<h2 class="pwc-layer-title">Custom Side Patches (+$10)</h2>'
    inputHtml += '<input id="wc_single_dropdown_brand" name="wc_single_side_patches" type="number" min="0" max="20">';
    */
    inputHtml += '<h2 class="pwc-layer-title wc-title">Front number Patch (+€19.98)</h2>'
    inputHtml += '<input id="wc_single_front_patches" name="wc_single_front_patches" type="number" min="0" max="999">';
    inputHtml += '<h2 class="pwc-layer-title wc-title">Additional Notes</h2>'
    inputHtml += '<textarea id="wc_single_dropdown_brand" name="wc_single_additional_notes"></textarea>';
    inputHtml += '<input id="total_price" type="hidden" name="wc_total_price" value="0">';
    inputHtml += '</div>';
    
    jQuery('form.cart').prepend( inputHtml );
    
    jQuery('#total_price').val(basePrice.toFixed(2));

    jQuery('#wc_single_dropdown_brand').change( function(){
        var parentID = jQuery(this);
        jQuery.ajax({
            url : wc_vars.ajaxurl,
            data : {
                action : 'wc_get_child_terms',
                parentID : parentID.val()
            },
            success: function( response ){
                var wcInnerHtml = wcShowDropdown(JSON.parse(response), 'model');
                jQuery('#wc_single_dropdown_model').remove();
                jQuery('#wc_single_dropdown_brands').append(wcInnerHtml);
            },
            error: function( response ){
                console.log('Please contact support.')
            }
        })
    });
    
    jQuery('#wc_single_front_patches').change( function(){
        var pricePlaceholder = jQuery('.calculation.price');
        var priceAmount = parseFloat(pricePlaceholder.text().replace('€','').replace(',','.'));
        console.log(jQuery(this).val());
        if ( jQuery(this).val() > 0 && changed == false ){
            changed = true;
            newPrice = priceAmount + 19.98;
            console.log('subio');
        }
        if ( jQuery(this).val() == 0 && changed == true){
            changed = false;
            newPrice = priceAmount - 19.98;
            console.log('bajo');
        }
        htmlString = '€' + newPrice.toFixed(2);
        jQuery('#total_price').val(newPrice.toFixed(2));
        pricePlaceholder.html(htmlString.replace('.',','));
    })
    
    jQuery('.pwc-controls-list-img').click(function(){        
        setTimeout(function(){
            var totalPrice = 0;
            var pricePlaceholder = jQuery('.calculation.price');
            //var priceAmount = parseFloat(pricePlaceholder.text().replace('€','').replace(',','.'));
            var optionsCircle = jQuery('.pwc-controls-list-img.current');

            jQuery.each( optionsCircle, function( index, value ){
                totalPrice += jQuery(this).data('price');
            })
            if( jQuery('#wc_single_front_patches').val() >= 1 ){
                totalPrice += 19.98;
            }
            totalPrice += basePrice;
            console.log(totalPrice);
            jQuery('#total_price').val(totalPrice.toFixed(2));
            pricePlaceholder.html('€' + totalPrice.toFixed(2));
        },100)
    })
})

function wcShowDropdown(object, nature){
    var html = '';
    html += '<select id="wc_single_dropdown_' + nature + '" class="wc-single-dropdown wc-single-dropdown-' + nature + '" name="wc_single_dropdown_' + nature + '" required>';
    html += '<option value="">Select ' + nature + '</option>';
    if (object){
        jQuery.each(object, function(index, value){            
            html += '<option value="' + value.term_id + '">' + value.name + '</option>'; 
        })
    }
    html += '</select>';
    return html; 
}