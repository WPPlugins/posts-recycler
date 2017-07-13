jQuery(function($) {
	
	$('#category_select_all').on('click',function() {
		
		if ($(this).is(':checked')) {
			
		$('.recycler_category').each(function() {
			
		this.checked = true;
		
		});
		
		}
		
		else {
			
		$('.recycler_category').each(function() {
			
		this.checked = false;
		
		});
		
		}
		
	});
	
});



jQuery(function($) {
	
	$('#post_types_select_all').on('click',function() {
		
		if ($(this).is(':checked')) {
			
		$('.post_types_item').each(function() {
			
		this.checked = true;
		
		});
		
		}
		
		else {
			
		$('.post_types_item').each(function() {
			
		this.checked = false;
		
		});
		
		}
		
	});
	
});