(function ($, root, undefined) {
	
	$(function () {
		
		'use strict';
		
		$(document).ready(function(){

			$('.gallery-selector').slick({
			  dots: false,
			  arrows: false,
			  infinite: false,
			  speed: 300,
			  slidesToShow: 4,
			  slidesToScroll: 4,
			  responsive: [
			    {
			      breakpoint: 1024,
			      settings: {
			        slidesToShow: 3,
			        slidesToScroll: 3,
			        infinite: true,
			        dots: true
			      }
			    },
			    {
			      breakpoint: 600,
			      settings: {
			        slidesToShow: 2,
			        slidesToScroll: 2
			      }
			    },
			    {
			      breakpoint: 480,
			      settings: {
			        slidesToShow: 1,
			        slidesToScroll: 1
			      }
			    }
			    // You can unslick at a given breakpoint now by adding:
			    // settings: "unslick"
			    // instead of a settings object
			  ]
			});

			var gallerySliderSelector = $('.gallery-selector li');

			gallerySliderSelector.on('mouseup',function(){

				var $this = $(this);
				var $parent = $this.parent().parent().parent().parent();
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
