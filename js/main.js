jQuery(function($){

	'use strict';

   /* * * * * * * * * * * * * GENERAL * * * * * * * * * * * * * */

   // Scroll to top button appear
   $(window).scroll(function() {
      var y = $(this).scrollTop();
      if (y > 400 && y < ($(document).height() * 0.9)) {
         $('#back-to-top').fadeIn();
      } 
      else {
         $('#back-to-top').fadeOut();
      }
   });
      
   // Smooth scrolling body to 0px on click
   $('#back-to-top').click(function() {
      $('body,html').animate({
         scrollTop: 0
      }, 800);
      return false;
   });

   // Vertical social media share div appears on scroll from top > 800px
   $(document).scroll(function() {
      var y = $(this).scrollTop();
      if (y > 500) {
         $('#verticalShare').fadeIn("slow");
         $( "#verticalShare" ).animate({ left:-80 }, 1000 );
      } else {
         $('#verticalShare').fadeOut();
      }
   });

   // loader
   var loader = function() {
      setTimeout(function() {
         if($('#loader').length > 0) {
         $('#loader').removeClass('show');
         }
      }, 1);
   };
   loader();

   //home page carousel
   $('.carousel').carousel({
      interval: 2000,
      cycle: true,
      pause: "false"
   });

   /* * * * * * * * * * * * * CONTACT * * * * * * * * * * * * * */

   // AJAX submit contact form
   $("#contactForm").on('submit', function(event) {
      event.preventDefault();
      var form_data = $(this).serialize();
      $.ajax({
         url:"",
         method:"POST",
         data:form_data,
         dataType:"JSON",
         success:function(data)
         {
            // Clean error message templates from previous errors
            $(".invalid-feedback").text('');
            $("#contactForm input, #contactForm textarea").each(function() {
               if ($(this).hasClass("is-invalid")) {$(this).removeClass("is-invalid")}
            });
            // If there are no errors
            if(data.errors === undefined) {
               $("#contactForm")[0].reset();
               $("#info_msg").html(data.success);
            }
            else {
               // Find inputs that have error and add an error message in the template below
               $.each(data.errors, function(fieldName, error) {
                  let field = $("#contactForm").find('[name="' + fieldName + '"]');
                  field.addClass("is-invalid");
                  let immediateSibling = field.next();
                  if (immediateSibling.hasClass('invalid-feedback')) {
                     immediateSibling.text(error[0]);
                  }
                  else {
                     field.after("<div class='invalid-feedback'>" + error + "</div>")
                  }
               });
            }
         },
         error: function ()
         {
            console.log('An error occurred.');
         }
      });
   });

   /* * * * * * * * * * * * * ARTICLES * * * * * * * * * * * * * */

    //Fetch comments - Add comment
    var articleId = $('#inputPostId').val();

    if (typeof articleId !== 'undefined') {
      load_comment(articleId);
    }

   // AJAX call for fetch and show more articles
   $("#postList").on("click", "#show_more", function(event) {
      event.preventDefault();
      var aDate = $(this).data('adate');
      $("#show_more").hide();
      $("#loading").show();
      $.ajax({
         url:"",
         method:"POST",
         data:{aDate: aDate},
         dataType:"html",
         success:function(data)
         {
            $('#show_more_main').remove();
            $("#postList").append(data);
         }
      });
   });

   // Submit comment - return message - reset form inputs
   $("#commentForm").on('submit', function(event) {
     event.preventDefault();
     var form_data = $(this).serialize();
      $.ajax({
         url: "",
         method: "POST",
         data: form_data + "&action=addComment",
         dataType: "JSON",
         success:function(data)
         {
            // Clean error message templates from previous errors
            $(".invalid-feedback").text('');
            $("#commentForm input, #commentForm textarea").each(function() {
               if ($(this).hasClass("is-invalid")) {$(this).removeClass("is-invalid")}
            });
            // If there are no errors
            if(data.errors === undefined) {
               $('#commentForm')[0].reset();
               $('#returnMsg').html(data.success);
               $('#inputParentId').val('0');
               load_comment(articleId);
            }
            else {
               // Find inputs that have error and add an error message in the template below
               $.each(data.errors, function(fieldName, error) {
                  let field = $("#commentForm").find('[name="' + fieldName + '"]');
                  field.addClass("is-invalid");
                  let immediateSibling = field.next();
                  if (immediateSibling.hasClass('invalid-feedback')) {
                     immediateSibling.text(error[0]);
                  } 
                  else {
                     field.after("<div class='invalid-feedback'>" + error + "</div>")
                  }
               });
            }
         },
         error: function() {
            console.log('Houston, we have a problem!');
         }
      });
   });

   // On click reply button on a comment - focus on commentForm and get the id of the comment and place it in a hidden input
   $("#display_comments").on("click", ".reply", function() {
      var comment_id = $(this).attr("id");
      $("#inputParentId").val(comment_id);
      $("#inputCommentName").focus();
      $("html, body").animate({
         scrollTop: $("#commentForm").offset().top - 200
      }, 800);
   });

   // AJAX fetch comments from DB
   function load_comment(postId)
   {
     var action = "fetchComments";
      $.ajax({
         url: "",
         method: "POST",
         data: {action: action,id: postId},
         dataType: "html",
         success:function(data)
         {
            $('#display_comments').html(data);
         },
         error: function() {
            console.log('Houston, we have a problem!');
         }
      });
   }

   /* * * * * * * * * * * * * SHARE ICONS * * * * * * * * * * * * * */

   // copy to Clipboard function for copy link icon in article, wine review pages
   $("#copy_link, #copy_link2").on("click", function(event) {
      event.preventDefault();
      var copyText = decodeURIComponent(document.URL);
      var $temp = $("<input>");
      $("body").append($temp);
      $temp.val(copyText).select();
      document.execCommand("copy");
      $temp.remove();
      $(this).attr('title', 'Link copied!');
      $(this).tooltip('show');
   });

   if (window.location.pathname.split('/')[1] === 'article' || window.location.pathname.split('/')[1] === 'wine_review') {
      setShareLinks();
   }

   //Social Share Links
   function socialWindow(url)
   {
      var left = (screen.width - 570) / 2;
      var top = (screen.height - 570) / 2;
      var params = "menubar=no,toolbar=no,status=no,width=570,height=570,top=" + top + ",left=" + left;
      window.open(url,"NewWindow",params);
   }

   function setShareLinks()
   {
      var pageUrl = encodeURIComponent(document.URL);
      var tweet = encodeURIComponent(jQuery("meta[property='og:description']").attr("content"));

      jQuery(".facebook_share, .fb_share").on("click", function() {
         var url = "https://www.facebook.com/sharer.php?u=" + pageUrl;
         socialWindow(url);
      });

      jQuery(".twitter_share, .tw_share").on("click", function() {
         var url = "https://twitter.com/intent/tweet?url=" + pageUrl + "&text=" + tweet;
         socialWindow(url);
      });

      jQuery(".linkedin_share, .li_share").on("click", function() {
         var url = "https://www.linkedin.com/shareArticle?mini=true&url=" + pageUrl;
         socialWindow(url);
      });
      jQuery(".whatsapp_share, .wa_share").on("click", function() {
         var url = "whatsapp://send?text=" + pageUrl;
         socialWindow(url);
      })
   }

   /* * * * * * * * * * * * * WINE REVIEWS * * * * * * * * * * * * * */

   //window.onpopstate = function(event) {
      
   //};

   /*window.addEventListener("popstate", function(event) {
      var state = JSON.stringify(event.state);
      console.log(state);
      loadFilteredData(state.filter_params, state.page);
      //window.location.href = location.href;
   });*/

   // Action when user submits the filter form
   $("#filterForm").on("submit", function(event) {
      event.preventDefault();
      // serialize form inputs (name, value) that are not empty 
      var form_data = $(this).serializeArray().filter(function (i) {
        return i.value;
      });
      form_data = objectifyForm(form_data);
      loadFilteredData(form_data);
   });

   // When reset button is clicked, reset filterForm elements - load first page 
   $("#resetButton").on("click", function() {
      $("#filterForm")[0].reset();
      loadData();
      //$(location).attr('href', url);
   });

   // On click page link, take page-link data-attr and pass it to functions to load requested page
   $("#pagination").on("click", "a", function() {
      var page = $(this).data("page");
      var filter_data = $(this).data("filter");
      if (filter_data.length !== 0) {
         loadFilteredData(filter_data, page);
      }
      else {
         loadData(page);
      }
   });

   if (window.location.pathname === '/wine_reviews') {
      var urlFilters = getAllUrlParams();
      if (urlFilters) {
         UrlFiltersRouter(urlFilters);
      }
   }
  
   function UrlFiltersRouter(urlFilters)
   {
      setUrlParamsToFilterForm(urlFilters);
      if(urlFilters.page) {
         var page = urlFilters.page;
         if(Object.keys(urlFilters).length > 1) {
            delete urlFilters.page;
            loadFilteredData(urlFilters, page);
         }
         else {
            loadData(page);
         }
      }
      else {
         loadFilteredData(urlFilters);
      }
   }

   // Return object with all url querystring parameters
   function getAllUrlParams()
   {
      var filterParams = {};
      const queryString = window.location.search;
      if (queryString) {
         const urlParams = new URLSearchParams(queryString);
         urlParams.forEach(function(value, key) {
            filterParams[key] = value;
         });
            
         console.log(filterParams);
            
         return filterParams;
      }
      else {
         return false;
      }
   }
   
   // Set filter params from URL to filter form inputs
   function setUrlParamsToFilterForm(url_filters)
   {
      $.each(url_filters, function(index, value) {
         switch(index)
         {
            case 'search':
               $("#inputSearch").val(value);
               break;
            case 'variety':
               $("#inputVariety").val(value);
               break;
            case 'color':
               $("#inputColor option[value='']").removeProp("selected");
               $("#inputColor option[value="+value+"]").prop("selected", true);
               break;
            case 'sweetness':
               $("#inputSweetness option[value='']").removeProp("selected");
               $("#inputSweetness option[value="+value+"]").prop("selected", true);
               break;
            case 'rating':
               $("#inputRating option[value='']").removeProp("selected");
               $("#inputRating option[value='"+(value)+"']").prop("selected", true);
               break;
         }
      });
   }

   function loadData(page = 1)
   {
      var action = "fetch_wines";
      var urlQuery = 'wine_reviews?&page=' + page;
      $.ajax({
         url: "",
         method: "POST",
         data: {action:action, page:page},
         dataType: "JSON",
         success: function(data)
         {
            buildContent(data.wines, data.pagination);
            history.pushState(/*{page: page}*/ null, null, urlQuery);   // Update the address bar with the 'url' parameter
            $("html, body").animate({
               scrollTop: $("#display_wines").offset().top - 200
            }, 800);
         },
         error: function() { // Debugging error
            console.log('Houston, we have a problem!');
         }
      });
   }

   function loadFilteredData(filter_params, page = 1)
   {
      var action = "filter_wines";
      var urlQuery = 'wine_reviews?' + $.param(filter_params) + '&page=' + page;
      $.ajax({
         url: "",
         method: "POST",
         data: {action:action, page:page, search:filter_params["search"], variety:filter_params["variety"], color:filter_params["color"], sweetness:filter_params["sweetness"], rating:filter_params["rating"]},
         dataType: "JSON",
         success: function(data)
         {
            buildContent(data.wines, data.pagination, data.total_records);
            history.pushState(/*{filter_params: filter_params, page: page}*/ null, null, urlQuery);  // Push new url to URL bar and history of the browser
            $("html, body").animate({
               scrollTop: $("#display_wines").offset().top - 200
            }, 800);
         },
         error: function() {
            console.log('Houston, we have a problem!');
         }
      });
   }

   function buildContent(wines, pagination, filter_records = 0)
   {
      var content = "",  bg_color = "";
      var baseUrl = window.location.origin;
      //the element where the wines should be appended
      var template = $('#display_wines');

      //empty this element first
      template.empty();
      $("#pagination").empty();

      if (wines.length !== 0) {
         if (filter_records) {
            content = '<h5 class="ml-4 mb-4">Βρέθηκαν ' + filter_records + ' κρασιά, που ταιριάζουν στην αναζήτηση σας!</h5>';
         }
         content += '<div class="row mx-0">';
         
         // Iterate through array of wine objects
         $.each(wines, function(index, wine) {
            var pos = wine.rating.indexOf("+");
            var rating = wine.rating.substring(0,2);
            if (pos != -1) {
               wine.rating = rating + '<sup>+</sup>'
            }
            if (rating >= 95) {
               bg_color = 'rgba(255, 223, 50, 0.6);';
            }
            else if (rating >= 90) {
               bg_color = 'rgba(218, 218, 218, 0.6);';
            }
            else if (rating >= 85) {
               bg_color = 'rgba(205, 127, 50, 0.6);';
            }
            else {
               bg_color = 'rgba(40, 40, 40, 0.6);';
            }
            //append every node instead of replacing it with your markup
            content +=
               '<div class="col-sm-6 col-md-4 col-lg-3 my-3">' +
                  '<a href="wine_review/' + wine.slug + '" class="link-thumbnail">' +
                     '<h3>' + wine.title + '</h3>' +
                     '<span class="rating" style="background-color:' + bg_color + '">' + wine.rating + '</span>' +
                     '<img src="' + baseUrl + '/uploads/images/wines/' + wine.imageh + '" alt="Image" class="img-fluid">' +
                  '</a>' +
               '</div>';
         });
         content += '</div>';
         $("#pagination").append(pagination);
      }  
      else {
         content = '<h1 class="text-center my-5">Sorry, there are no results matching your search!</h1>';
      }
      template.append(content);
   }

   // Get array of objects as param and serialize it into an object data function
   function objectifyForm(formArray)
   {
      var returnArray = {};
      for (var i = 0; i < formArray.length; i++) {
        returnArray[formArray[i]['name']] = formArray[i]['value'];
      }
      return returnArray;
   }

   function getQueryVariable(variable)
   {
      var query = window.location.search.substring(1);
      var vars = query.split("&");
      for (var i=0;i<vars.length;i++) {
         var pair = vars[i].split("=");
         if(pair[0] == variable){return decodeURIComponent(pair[1]);}
      }
      return false;
   }

   function pageRedirect(url) {
      window.location.href = window.location.origin + url;
   }

   /* * * * * * * * * * * * END WINE REVIEWS * * * * * * * * * * * * */

	// bootstrap dropdown hover
	$('nav .dropdown').hover(function() {
		var $this = $(this);
		$this.addClass('show');
		$this.find('> a').attr('aria-expanded', true);
		$this.find('.dropdown-menu').addClass('show');
	}, function() {
		var $this = $(this);
			$this.removeClass('show');
			$this.find('> a').attr('aria-expanded', false);
			$this.find('.dropdown-menu').removeClass('show');
	});


	$('#dropdown04').on('show.bs.dropdown', function () {
	  console.log('show');
	});

	// home slider
	$('.home-slider').owlCarousel({
      loop:true,
      autoplay: true,
      margin:0,
      animateOut: 'fadeOut',
      animateIn: 'fadeIn',
      nav:true,
      autoplayHoverPause: true,
      items: 1,
      dragTouch: false,
      navText : ["<span class='ion-chevron-left'></span>","<span class='ion-chevron-right'></span>"],
      responsive:{
         0:{
            items:1,
            nav:false
         },
         600:{
            items:1,
            nav:false
         },
         1000:{
            items:1,
            nav:true
         }
      }
	});

   $('.nonloop-block-11').owlCarousel({
      center: false,
      items: 1,
      loop: false,
      stagePadding: 20,
      margin:50,
      nav: true,
      smartSpeed: 1000,
      navText: ['<span class="ion-chevron-left">', '<span class="ion-chevron-right">'],
      responsive:{
         600:{
            stagePadding: 20,
            items:1
         },
         800:{
            stagePadding: 20,
            items:1
         },
         1000:{
            // stagePadding: 200,
            items:1
         }
      }
   });

	// owl carousel
	var majorCarousel = $('.js-carousel-1');
	majorCarousel.owlCarousel({
      loop:true,
      autoplay: true,
      stagePadding: 7,
      margin: 20,
      animateOut: 'fadeOut',
      animateIn: 'fadeIn',
      nav: true,
      autoplayHoverPause: true,
      items: 3,
      navText : ["<span class='ion-chevron-left'></span>","<span class='ion-chevron-right'></span>"],
      responsive:{
         0:{
            items:1,
            nav:false
         },
         600:{
            items:2,
            nav:false
         },
         1000:{
            items:3,
            nav:true,
            loop:false
         }
      }
	});

	// owl carousel
	var major2Carousel = $('.js-carousel-2');
	major2Carousel.owlCarousel({
      loop:true,
      autoplay: true,
      stagePadding: 7,
      margin: 20,
      animateOut: 'fadeOut',
      animateIn: 'fadeIn',
      nav: true,
      autoplayHoverPause: true,
      items: 4,
      navText : ["<span class='ion-chevron-left'></span>","<span class='ion-chevron-right'></span>"],
      responsive:{
         0:{
            items:1,
            nav:false
         },
         600:{
            items:3,
            nav:false
         },
         1000:{
            items:4,
            nav:true,
            loop:false
         }
      }
	});


	var contentWayPoint = function() {
		var i = 0;
		$('.element-animate').waypoint( function( direction ) {

			if( direction === 'down' && !$(this.element).hasClass('element-animated') ) {
				
				i++;

				$(this.element).addClass('item-animate');
				setTimeout(function(){

					$('body .element-animate.item-animate').each(function(k){
						var el = $(this);
						setTimeout( function () {
							var effect = el.data('animate-effect');
							if ( effect === 'fadeIn') {
								el.addClass('fadeIn element-animated');
							} else if ( effect === 'fadeInLeft') {
								el.addClass('fadeInLeft element-animated');
							} else if ( effect === 'fadeInRight') {
								el.addClass('fadeInRight element-animated');
							} else {
								el.addClass('fadeInUp element-animated');
							}
							el.removeClass('item-animate');
						},  k * 100);
					});
					
				}, 100);
				
			}

		} , { offset: '95%' } );
	};
	contentWayPoint();

});