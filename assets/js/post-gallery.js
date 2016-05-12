(function ($, root, undefined) {
	
	$(function () {
		
		'use strict';
		
		$(document).ready(function(){

			var gallerySliderSelector = $('.gallery-selector li');

			gallerySliderSelector.click(function(){

				var $this = $(this);
				var $parent = $this.parent().parent();
				var slideToShow = $this.data('slide');

				$parent.find('.slide').removeClass('active');
				$parent.find('.' + slideToShow).addClass('active');
				$this.addClass('active');
				gallerySliderSelector.removeClass('active');



			});

			// Enable Masonry
			var $container = $('.masonry-gallery');

			if($container.length > 0){
				$container.isotope({
				  itemSelector: '.masonry-item',
				  percentPosition: true,
				  masonry: {
				    // use outer width of grid-sizer for columnWidth
				    columnWidth: '.sizer' // change to empty div with sizing
				  }
				});

				var iso = $container.data('isotope');
				$container.isotope('reveal', iso.items );
			} // end isoptop
			
			// init lightbox
			$(function() {
				$('a[data-rel^=lightcase]').lightcase();
			});
			
		});
		
	});
	
})(jQuery, this);
