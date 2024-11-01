(function($){
	"use strict";
	$(document).ready(function(){
		var app = {
			elMsg: $('#upfvp-message'),
			elList: $('#upfvp-list'),
			show: function(){
				$(this.elMsg).show();
			},
			hide: function(){
				$(this.elMsg).hide();
			},
			empty: function(){
				$(this.elMsg).html('');

				return this.hide();
			},
			setMessage: function(text){
				$(this.elMsg).html(text);

				return this.show();
			},
			append: function(value,status){
				var template = _.template( upfvp.ul_tpl );
				$(this.elList).prepend( template( {value:value, status: upfvp[ status ]} ) );
			},
			request: function(value){
				if(!$('body').hasClass('upfvp-loading')){
					var self = this;

					$('body').addClass('upfvp-loading');
					$('#upfvp-value').addClass('upfvp-loading-mail');
					$('#upfvp-button-validate').attr('disabled', true);
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
							$('#upfvp-value').removeClass('upfvp-loading-mail');
							$('#upfvp-button-validate').removeAttr('disabled');
							self.append( value, data.status );
						},
						error: function(){
							$('body').removeClass('upfvp-loading');
							$('#upfvp-value').removeClass('upfvp-loading-mail');
							$('#upfvp-button-validate').removeAttr('disabled');
							self.setMessage(upfvp[801]);
						}
					});
				}
			}
		};

		$('#upfvp-button-validate').on('click',function(){
			if($('#upfvp-value').val().length){
				app.hide();
				app.request( $('#upfvp-value').val() );
			} else {
				app.setMessage(upfvp[800]);
			}

			return false;
		});
	});
}(jQuery));
