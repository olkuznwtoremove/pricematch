/**
 *  @author    Olga Kuznetsova <olkuznw@gmail.com>
 *  @copyright odev.me 2017
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  @version   1.0.0
 *
 * Languages: EN
 * PS version: 1.6
 **/

$(document).ready(function() {
	$("#pricematchButton").fancybox({
		width: 400,
		beforeClose: function() {
			cleanPriceMatchForm();
		}
	});

	$(document).on('submit', '#pricematchForm', function(event){
		event.preventDefault();		
		var requiredValue,
			hasError = false,
			self = $(this),
			loader = $('.pricematch-loader');
		$('.pricematchpopup .required').each(function(index){
			requiredValue = $(this).val();
			if ('undefined' == typeof(requiredValue) || !requiredValue.length) {
				$(this).addClass('red');
				hasError = true;
			}
		});
		if (!hasError) {
			loader.show();
			$.ajax({
				url: self.attr('action'),
				data: self.serialize() + '&ajax=1',
				type: 'POST',
				dataType: 'json',
				success: function(jsonData) {
					loader.hide();
					if (!jsonData.hasError) {
						$.fancybox.close();
							$.fancybox.open([{
								closeBtn: true,
								type: 'inline',
								autoScale: true,
								minHeight: 30,
								minWidth: 200,
								content: '<p>' + jsonData.messages + '</p>'
							}
						]);
					}
					else {
						$('.pricematch-error').html(jsonData.messages);
					}					
				}
			});
		}
	});
	$('.pricematchpopup').on('click', '#pricematchClose', function(event){
		$.fancybox.close();
	});	
});


function cleanPriceMatchForm()
{
	$('.pricematchpopup .required').removeClass('red');
	$('.pricematch-error').html('');
	$('.pricematchpopup .toReset').val('');
}