(function($){
	"use strict";
	var app = {
		el: '',
		msgContainer: false,
		validValues: [],

		submit: function(){
			var self = this;
			$(self.el).trigger('submit');
		},

		request: function(value){
			if(!$('body').hasClass('upfvp-loading')){
				var self = this;

				$('body').addClass('upfvp-loading');

				var inputs = $(self.el).find('.upfvp-value');
				_.each($(inputs),function(el,key){
					if($(el).val()==value){
						$(el).addClass('upfvp-loading-mail');
					}
				});

				$.ajax({
					url: upfvp.AJAX_URL,
					type: 'POST',
					cache: false,
					crossDomain: true,
					data: {
						'action'     : 'upfvp-verify-value',
						'upfvp-nonce' : upfvp.nonce,
						'param'       : 'email',
						'value'       : value
					},
					dataType: 'json',
					success: function (data) {
						$('body').removeClass('upfvp-loading');
						var inputs = $(self.el).find('.upfvp-value');
						_.each($(inputs),function(el,key){
							if($(el).val()==value){
								$(el).removeClass('upfvp-loading-mail');
							}
						});

						if( data.is_valid ){
							self.validValues.push( value );
							self.submit();
						} else {
							self.showError(value, data.status);
						}
					},
					error: function(){
						$('body').removeClass('upfvp-loading');
					}
				});
			}
		},
		validateForm: function( form ){
			var self = this,
				inputs = $(form).find('.upfvp-value');

			self.el = form;

			if( $(self.el).find('.wpcf7-response-output').length ){
				self.msgContainer = $(self.el).find('.wpcf7-response-output');
			} else {
				self.msgContainer = false;
			}

			var values = [],
				inputs = $(form).find('.upfvp-value');

			_.each( inputs, function(el, key){
				values.push( $(inputs[key]).val() );
			});

			if( _.isEmpty( _.difference( values, app.validValues ) ) ){
				self.submit();
			} else {
				var value = _.difference( values, app.validValues );
				self.request( value[0] );
			}


		},
		showError: function(value, status){
			var self = this;
			if(self.msgContainer){
				var template = _.template( upfvp.tpl );
				$(self.msgContainer).html( template( {value:value, status: upfvp[ status ]} ) ).show();
			} else {
				var inputs = $(self.el).find('.upfvp-value');
				_.each($(inputs),function(el,key){
					if($(el).val()==value){
						var template = _.template( upfvp.tpl );
						$(el).after( template( {value:value, status: upfvp[ status ]} ) );
					}
				});
			}
		}
	};

	$(document).ready(function(){
		if($('form').length){
			_.each( $('form'), function( form, key ){
				if($(form).find('.upfvp-value').length){
					$(form).on('submit', function(){
						var values = [],
							inputs = $(form).find('.upfvp-value');

						$(form).find('.upfvp-error').remove();

						_.each( inputs, function(el, key){
							values.push( $(inputs[key]).val() );
						});

						if( _.isEmpty( _.difference( values, app.validValues ) ) ){
							$(form).removeClass( upfvp.form_class );
							return true;
						} else {
							app.validateForm( $(form) );
						}

						return false;
					});
				}
			});
		}
	});
}(jQuery));
