/*
	KD Coming Soon

	Copyright (c) 2015-2017 Kalli Dan. (email : kallidan@yahoo.com)

	KD Coming Soon is free software: you can redistribute it but NOT modify it
	under the terms of the GNU Lesser Public License as published by the Free Software Foundation,
	either version 3 of the LGPL License, or any later version.

	KD Coming Soon is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU Lesser Public License for more details.

	You should have received a copy of the GNU Lesser Public License along with KD Coming Soon.
	If not, see <http://www.gnu.org/licenses/>.
*/

var ComingSoon = function () {
	var runInit = function (end_date, act_url, err) { 
		var countdown = end_date.split('/');
		jQuery('#csDefaultCountdown').countdown({  	/* 07/31/2015 */
			until: new Date(countdown[2], (countdown[0] -1), countdown[1]),
			padZeroes: false,
			format: 'DHMS',
			onTick: everyOne, tickInterval: 1
			//onExpiry: 
			//expiryText: 'All Done!'
		});

		jQuery('.subscribe form').submit(function(e) {
			e.preventDefault();
			var nonce = jQuery(this).attr("data-nonce")
			var postdata = 'action=kd_cemailer' + '&' + jQuery('.subscribe form').serialize();
			var email = jQuery('#regForm #subscribe-email').val();
			if(isEmpty(email, err)){ return false; }
			if(notEmail(email, err)){ return false; }

			jQuery.ajax({
				type : "POST",
				dataType : "json",
				url : act_url,
				data : postdata,
				success: function(response) {
					//console.log(response);
					if(response.valid == "success") {
						jQuery('.error-message').hide();
						jQuery('.success-message').hide();
						jQuery('.subscribe form').hide();
						jQuery('#subscribe-email').val("");
						jQuery('.success-message').html(response.message);
						jQuery('.success-message').fadeIn();
						setTimeout(function(){
							jQuery('.success-message').slideUp('slow', function() {
								jQuery('.success-message').hide();
								jQuery('.subscribe form').fadeIn();
							});
						},5000);
					}else {
						displayFormError(response.message);
					}
				}
			})
		});

		function everyOne(periods) {
			jQuery('.days').text(periods[3]);
			jQuery('.hours').text(periods[4]);
			jQuery('.minutes').text(periods[5]);
			jQuery('.seconds').text(periods[6]);
		}
	};

	return {
		init: function (date, act_url, err) {
			runInit(date, act_url, err);
			jQuery('.social a').tooltip();
		}
	};
}();

function isEmpty(field, msg) {
	if (trim(field) == "") {
		displayFormError(msg);
		return true;
	}
	return false;
}
function notEmail(field, msg) {
	var email = trim(field);
	var at = false;
	var dot = false;
	for (var i=0; i<email.length; i++) {
		if (email.charAt(i) == "@") at = true;
		if (email.charAt(i) == "." && at) dot = true;
	}
	if (at && dot && email.length > 5){ return false; }
	displayFormError(msg);
	return true;
}
function trim(stringToTrim) {
	var trimmedString = "";
	if(stringToTrim){
		//left trim
		for(var i=0; i<stringToTrim.length; i++) {
			if (stringToTrim.charAt(i) != " ") break;
		}
		trimmedString = stringToTrim.substring(i);
		//right trim
		for(var i=trimmedString.length-1; i>=0; i--) {
			if (trimmedString.charAt(i) != " ") break;
		}
		trimmedString = trimmedString.substring(0, i + 1);
	}
	return trimmedString;
}
function displayFormError(err){
	if(err){
		jQuery('.success-message').hide();
		jQuery('.error-message').hide();
		jQuery('.error-message').html(err);
		jQuery('.error-message').fadeIn();
		setTimeout(function(){
			clearFormError();
		},5000);
	}
	return false;
}
function clearFormError(){
	jQuery('.error-message').slideUp('slow', function() {
		jQuery('.error-message').hide();
	});
}