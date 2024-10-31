jQuery(document).ready(function($) {
	
	var ajaxurl = wapfparms.ajaxurl,
	
	queried_termid = wapfparms.queried_termid,
	
	queried_is_cat = wapfparms.queried_is_cat,
	
	queried_is_shop = wapfparms.queried_is_shop,
	
	woocurrency = wapfparms.woocurrency;
			
    function filter_data(dataId, dataAttValue) {	
			
		var filter = [];
		
		if ( $( "#wapf-products-loader" ).length == 0 ) {
			
			$('.products').after('<div id="wapf-products-loader"><div class="lds-wapfloader"><div></div><div></div><div></div></div></div>');	
		
		}
		
		$('.products').hide();	
		
		$('#wapf-products-loader').show();
		
		$('.filter_all:checked').each(function(i, obj) {
				
			var attribut = $(obj).attr("data-att-value");
			
			var value = $(obj).attr("data-value");
			
			var selectval = $( ".wapf-select-cat option:selected" ).val();		
			
			filter.push({attribute : attribut, Item : [value]});	
	
		});	


		$('.wapf-select-cat option:selected').each(function(i, obj) {
			
			var attribut = $(obj).attr("data-att-value");
			
			var value = $(obj).attr("data-value");
			
			var selectval = $( ".wapf-select-cat option:selected" ).val();		

			filter.push({attribute : 'cat', Item : [selectval]});			   
	
		});	
			
				
        $('.filter_data');
		
		var action = 'wapf_gfp';
		
        var minimum_price = $('#min_price_hide').val();
        
		var maximum_price = $('#max_price_hide').val();
        
		var brand = get_filter('brand');
        
		var color = get_filter('wapf_'+dataAttValue);
        
		var gender = get_filter('gender');
        
		$.ajax({
                    url: ajaxurl,
                    method: "POST",
                    data: {
                        action: action,
						is_cat: queried_is_cat,
						term_id: queried_termid,
						is_shop: queried_is_shop,
                        minimum_price: minimum_price,
                        maximum_price: maximum_price,
                        brand: brand,
                        color: color,
                        gender: gender,
						filters: filter
                    },
                    success: function(data) {
						
                        $('ul.products').html(data);
						
						$('.products').show();	
						
						$('#wapf-products-loader').hide();
						
						var newmaxval = $(document.body).find('#wapf-max-price-val').text();
						
						var itemcount = $(document.body).find('#wapf-itemcount').text();
						
						$('p.woocommerce-result-count').html('Showing all '+itemcount+' results');
						
                    }
					
        });
				
    }
			
    function get_filter(class_name) {
        
		var filter = [];
        
		$('.' + class_name + ':checked').each(function() {
            
			filter.push($(this).val());
        
		});
        
		return filter;
		
    }	

	$(".filter_all").on("change", function(){
		
		var dataId = $(this).attr("data-value");
		
		var dataAttValue = $(this).attr("data-att-value");
		
		filter_data(dataId, dataAttValue);
	
	});
	
	
	
	$(".wapf-toggle-filter").on("click", function(e){
		var id = $(this).data('id');
		
		if($('[data-id="' + id + '-filter"]').is(':visible')){

			$('[data-id="' + id + '-filter"]').hide(300);
		} else {

			$('[data-id="' + id + '-filter"]').width($(this).width());
			$('[data-id="' + id + '-filter"]').show(300);
		}

		e.preventDefault();


	});	
	

	var max = $( "#max_price_hide" ).val();
    
		
$('.wapf-price-range').each(function(i, obj) {
    //alert('slid');
	var filterid = $(obj).data("wapf-fnum");
	
		$('.wapf-price-filter-'+filterid).slider({
					range: true,
					min: 0,
					max: max,
					values: [0, max],
					stop: function(event, ui) {
						$('#price_show').html('<span class="wapf-minval">' +woocurrency + ui.values[0] + '</span> - <span class="wapf-maxval">' + woocurrency + ui.values[1] +'</span>');
						console.log(ui.values[1]);
						$('#min_price_hide').val(ui.values[0]);
						$('#max_price_hide').val(ui.values[1]);
						filter_data();
					},
					slide: function( event, ui ) {
						$( ".wapf-maxval" ).html( woocurrency + ui.values[1] );
						$( ".wapf-minval" ).html( woocurrency + ui.values[0] );
					},
					change: function(event, ui) {
					
					}
		});	
	console.log(filterid);
});		

	
	
				
});