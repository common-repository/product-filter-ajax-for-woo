jQuery(document).ready(function($) {
	
	var ajaxurl = wapfadminparms.ajaxurl;

	$(document).on("click", '.wapf-select-ftype', function(event) { 

		var value = this.value;
		
		var dataId = $(this).attr("data-wapf"); 
		
		dataId = dataId.replace('wapf-type', 'wapf-select');

		if (value == 'checkbox') {
	console.log(dataId+'.'+dataId+'-colorcheckbox');		
			$('.'+dataId).show();
			
			$('[data-wapf="'+dataId+'"]').show();
			
			$('[data-wapf-cat="'+dataId+'"]').hide();
			
			$('.'+dataId+'-label').show();
			
			$('.'+dataId+'-labelcolor').show();
			
			$('.'+dataId+'-colorcheckbox').show();
			
		} else if (value == 'select') {
			
			$('[data-wapf-cat="'+dataId+'"]').show();
			
			$('[data-wapf="'+dataId+'"]').hide();
			
			$('.'+dataId+'-label').show();
			
			$('.'+dataId+'-labelcolor').hide();
			
			$('.'+dataId+'-colorcheckbox').hide();
			
		} else {
			
			$('[data-wapf="'+dataId+'"]').hide();  
			
			$('[data-wapf-cat="'+dataId+'"]').hide();
			
			$('.'+dataId+'-label').hide();
			
			$('.'+dataId+'-labelcolor').hide();
			
			$('.'+dataId+'-colorcheckbox').hide();
			
		}
		
	});

	$(document).on("click", '.wapf-delete-filter', function(event) { 

		let filterId = $(this).attr("data-wapf-filter-id");
		
		let filterIdReal = parseInt(filterId, 10) + 1;
		
		if (confirm('Are you sure you want delete the filter with ID #'+filterIdReal+'?')) {
			
			if (filterId) {
				
				$.ajax({
						url: ajaxurl,
						method: "POST",
						data: 	{
								action: 'wapf_delete_filter',
								fiter_id: filterId
								},
						success: function(data) {
								jQuery('.wapfnum'+filterId).remove();
								alert(data);
						}
					});	
					
			}
			
		}
		
		event.preventDefault();
		
	});
		
	$(document).on("click", '.wapf-remove-filter', function(event) {
		
		let filterId = $(this).attr("data-wapf-filter-id");
		
		let filterIdReal = parseInt(filterId, 10) - 1;
		
		jQuery('.wapfnum'+filterIdReal).remove();
		
		event.preventDefault();
		
	});		
		

	$(".wapf-add-selection").click(function(e){
		
		var numItems = $('.wapf-seletion-select').length;
		
		if (numItems < 22) {
		
			$.post(ajaxurl, {action: "wapf_get_all_attributes"}, function(data) {
				
				data = data.replace('wapfselectid', 'wapf-select-'+numItems);
				
				data = data.replace('wapftypeid', 'wapf-type-'+numItems);
				
				data = data.replace('wapfselectlabel', 'wapf-select-'+numItems+'-label');
				
				data = data.replace('wapfselectcatid', 'wapf-select-'+numItems);
				
				data = data.replace('wapfselectlabelcolor', 'wapf-select-'+numItems+'-labelcolor');
				
				data = data.replace('wapfselectcolorcheckbox', 'wapf-select-'+numItems+'-colorcheckbox');
				
				$(".wapf-selection-container").append('<span class="wapf-selection-select-container wapfnum'+numItems+' wapf100">'+data+'</span>');
			
			});	
		
		} else {
			
			$(".wapf-selection-container").append('<span class="wapf-selection-select-container wapfnum'+numItems+' wapf100"><span class="wapf-filter-title wapf100">Sorry....</span><span class="wapf-filter-content wapf100">Limit</span></span>');

		}
		
		e.preventDefault();	
		
	});	
		
});