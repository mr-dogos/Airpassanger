jQuery(function(){
	function initFunction(){
		appweight = (jQuery.cookie('weight') == null)? 0 : Number(jQuery.cookie('weight'));
		quicks = (jQuery.cookie('quicks') == null)? [] : JSON.parse(jQuery.cookie('quicks'));
		accord = (jQuery.cookie('accord') == null)? [] : JSON.parse(jQuery.cookie('accord'));
		if(quicks.length > 0){
			quickCheck();
			printCount(); 
			printWeight();
		}
	}
	
	function printCount(){
		jQuery('#quick-card span.uk-badge').text(isCount());
	}

	function printWeight(){
		jQuery('#progress-bar p > span').text(appweight);
		jQuery('#js-progressbar').attr('value',appweight);
	}
	
	function isCount(){
		return quicks.length;
	}
	
	function quickAddModel(id){
		jQuery('#model_'+id).removeClass('uk-hidden');
		jQuery('#model_'+id+' label input').prop('checked', true);
	}
	
	function quickMinusModel(id){
		jQuery('#model_'+id).addClass('uk-hidden');
		jQuery('#model_'+id+' label input').prop('checked', true);
	}

	function quickAdd(id,weight,accord_id){
		if((appweight + Number(weight)) <= 30001){
			appweight = appweight + Number(weight);
			quicks.push(id);
			accord.push(accord_id);
			printCount();
			printWeight();
			quickAddModel(id);
			jQuery('#item_'+id+' label input').prop('checked', true);
			jQuery.cookie('weight', appweight,  {expires: 7, path: '/'});
			jQuery.cookie('quicks', JSON.stringify(quicks),  {expires: 7, path: '/'});
			jQuery.cookie('accord', JSON.stringify(accord),  {expires: 7, path: '/'});
			return true;
		}else{
			var leng = jQuery('header a.leng-item.active').attr('data-leng');
			if(leng == 'ru'){
				UIkit.notification({message: '<span uk-icon="warning"></span> &nbsp; Приблизительный вес багажа и ручной клади, превышает норму', status: 'danger'});
			}else{
				UIkit.notification({message: '<span uk-icon="warning"></span> &nbsp; Approximate weight of luggage and hand luggage, exceeds the norm', status: 'danger'});
			}
			return false;
		}
	}
	
	function quickMinus(id,weight,accord_id){
		var key = quicks.indexOf(id);
		if (key !== -1) { quicks.splice(key, 1); }
		key = accord.indexOf(accord_id);
		if (key !== -1) { accord.splice(key, 1); }
		appweight = appweight - Number(weight);
		printCount();
		printWeight();
		quickMinusModel(id);
		jQuery('#item_'+id+' label input').prop('checked', false);
		jQuery.cookie('weight', appweight,  {expires: 7, path: '/'});
		jQuery.cookie('quicks', JSON.stringify(quicks),  {expires: 7, path: '/'});
		jQuery.cookie('accord', JSON.stringify(accord),  {expires: 7, path: '/'});
	}
	
	function quickCheck(){
		quicks.forEach(function(entry) {
			jQuery('#item_'+entry+' label input').prop('checked', true);
			quickAddModel(entry);
		});
	}
	
	function luggaEmpty(leng){
		if(leng == 'ru'){
			UIkit.notification({message: '<span uk-icon="warning"></span> &nbsp; В вашем багаже пусто!', status: 'danger'});
		}else{
			UIkit.notification({message: '<span uk-icon="warning"></span> &nbsp; Your luggage is empty!', status: 'danger'});
		}
	}
	
	jQuery('a').click(function(){
		var href = jQuery(this).attr('href');
		var role = jQuery('header ul.role').attr('data-role');
		if((role !== 'Contributor') && (href == '/?p=71') || (role !== 'Contributor') && (href == '/?p=68')){
			UIkit.modal('#info-form').show();
			return false;
		}
	});
	
	jQuery('.disabled').click(function(){
		UIkit.modal('#info-form').show();
		return false;
	});

	jQuery('#but-pay').click(function(){
		jQuery.ajax({
			url: "/wp-admin/admin-ajax.php",
			method: 'post',
			data: {
				action: 'uuid_pay',
			},
			success: function(msg){
				var result = JSON.parse(msg);
				if(result){
					jQuery('#cps_email').attr('value',result.email);
					jQuery('#shopSuccessURL').attr('value','http://airpassenger.site/?shopsuccess=true&uuid='+result.uuid);
					jQuery('#shopFailURL').attr('value','http://airpassenger.site/payerror');
				}
			}
		});
		
		UIkit.modal('#pay-form').show();
		return false;
	});
	
	jQuery('#logout').click(function(){
		jQuery.get('/?logout=1');
		location.reload();
	});
	
	jQuery('.quick-list_item').click(function(){
		var id = jQuery(this).find('label > input').attr('data-id');
		var weight = jQuery(this).find('label > input').attr('data-weight');
		var accord_id = jQuery(this).find('label > input').attr('data-accord');
		if (jQuery(this).find('label > input').is(':checked')){
			quickMinus(id,weight,accord_id);
			jQuery(this).find('label > input').prop('checked', false);
		}else{
			if(quickAdd(id,weight,accord_id)){
				jQuery(this).find('label > input').prop('checked', true);
			}
		}
	});
	
	jQuery('.quick-list_item label input').click(function(){
		if (jQuery(this).is(':checked')){
			jQuery(this).prop('checked', false);
		}else{
			jQuery(this).prop('checked', true);
		}
	});
	
	jQuery('#register-form label input.uk-checkbox').click(function(){
		if (jQuery(this).is(':checked')){
			jQuery('#register-form button[type="submit"]').attr('disabled',false);
		}else{
			jQuery('#register-form button[type="submit"]').attr('disabled','disabled');
		}
	});
	
	jQuery('.quick-model_item label input').click(function(){
		var id = jQuery(this).attr('data-id');
		var weight = jQuery(this).attr('data-weight');
		var accord_id = jQuery(this).attr('data-accord');
		quickMinus(id,weight,accord_id);
		if(isCount() < 1){
			UIkit.modal('#quick-model').hide();
		}
	});
	
	jQuery('#quick-card').click(function(){
		var leng = jQuery('header a.leng-item.active').attr('data-leng');
		if(isCount() <= 0){
			luggaEmpty(leng);
			return false;
		}else{			
			UIkit.modal('#quick-model').show();
			return false;
		}
	});
	
	jQuery('#quick-model button.clear-but').click(function(){
		UIkit.modal('#quick-model').hide();
		quicks.forEach(function(entry){
			quickMinusModel(entry);
			jQuery('#item_'+entry+' label input').prop('checked', false);
		});
		appweight = 0;
		quicks.length = 0;
		accord.length = 0;
		printCount();
		printWeight();
		jQuery.cookie('weight', appweight,  {expires: 7, path: '/'});
		jQuery.cookie('quicks', JSON.stringify(quicks),  {expires: 7, path: '/'});
		jQuery.cookie('accord', JSON.stringify(accord),  {expires: 7, path: '/'});
	});
	
	jQuery('#quick-model button.recom-but').click(function(){
		var accords = new Set(accord);
		jQuery('#recom-model .uk-accordion > li').addClass('uk-hidden');		
		accords.forEach(function(id) {
			jQuery('#recom-model .uk-accordion #accord_'+id).removeClass('uk-hidden');
		});

		UIkit.modal('#quick-model').hide();
		UIkit.modal('#recom-model').show();
	});
	
	jQuery('header a.leng-item').click(function(){
		if(!jQuery(this).hasClass('active')){
		var leng = jQuery(this).attr('data-leng');
		if(leng == 'ru'){
			jQuery.cookie('leng', 'ru',  {expires: 7, path: '/'});
		}else{
			jQuery.cookie('leng', 'en',  {expires: 7, path: '/'});
		}

		location.reload();
		}

		return false;
	});

	jQuery('#login-form').submit(function(){
		var leng = jQuery('#login-form').attr('data-leng');
		jQuery('#login-form').find('button[type="submit"]').attr('disabled','disabled');
		var email = jQuery('#useremail input').val().replace(/\s+/g, '');
		var password = jQuery('#userpassword input').val().replace(/\s+/g, '');
		if(!jQuery('#login-card .uk-alert-danger').hasClass('uk-hidden')){
			jQuery('#login-card .uk-alert-danger').addClass('uk-hidden');
			jQuery('#login-card .uk-alert-danger > p').remove();
		}
		jQuery('#login-form > div > input').removeClass('uk-form-danger');
		jQuery('#login-form > div > p').remove();

		jQuery.ajax({
			url: "/wp-admin/admin-ajax.php",
			method: 'post',
			data: {
				action: 'login_user',
				leng: leng,
				email: email,
				password: password
			},
			success: function(msg){
				var result = JSON.parse(msg);
				if(result.result == 'error'){
					result.message.forEach(function(item){
						switch(item){
							case 'email_error':
								jQuery('#useremail input').addClass('uk-form-danger');
								if(leng == 'ru'){
									jQuery('#useremail').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Некорректный адрес электронной почты</p>');
								}else{
									jQuery('#useremail').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Incorrect e-mail address</p>');
								}
							break;
							case 'password_error':
								jQuery('#userpassword input').addClass('uk-form-danger');
								if(leng == 'ru'){
									jQuery('#userpassword').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Поле не может быть пустым</p>');
								}else{
									jQuery('#userpassword').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">The field can not be empty</p>');
								}
							break;
							case 'error_login':
								jQuery('#useremail input').addClass('uk-form-danger');
								if(leng == 'ru'){
									jQuery('#login-card .uk-alert-danger').removeClass('uk-hidden').append('<p class="uk-text-center">Извините, неверный логин или пароль!</p>');
								}else{
									jQuery('#login-card .uk-alert-danger').removeClass('uk-hidden').append('<p class="uk-text-center">Sorry, incorrect login or password!</p>');
								}
							break;
						};
					});
				}
				
				if(result.result == 'success'){
					location.reload();
				}

				jQuery('#login-form').find('button[type="submit"]').attr('disabled',false);
			}
		});
		
		return false;
	});
	
	jQuery('#register-form').submit(function(){
		var leng = jQuery('#register-form').attr('data-leng');
		jQuery('#register-form').find('button[type="submit"]').attr('disabled','disabled');
		var logname = jQuery('#reglogin input').val().replace(/\s+/g, '');
		var email = jQuery('#regemail input').val().replace(/\s+/g, '');
		var password = jQuery('#regpassword input').val().replace(/\s+/g, '');
		var pasdoes = jQuery('#regpasdoes input').val().replace(/\s+/g, '');
		
		if(!jQuery('#register-card .uk-alert-danger').hasClass('uk-hidden')){
			jQuery('#register-card .uk-alert-danger').addClass('uk-hidden');
			jQuery('#register-card .uk-alert-danger > p').remove();
		}
		jQuery('#register-form > div > input').removeClass('uk-form-danger');
		jQuery('#register-form > div > p').remove();

		jQuery.ajax({
			url: "/wp-admin/admin-ajax.php",
			method: 'post',
			data: {
				action: 'register_user',
				leng: leng,
				logname: logname,
				email: email,
				password: password,
				pasdoes: pasdoes
			},
			success: function(msg){
				var result = JSON.parse(msg);
				if(result.result == 'error'){
					result.message.forEach(function(item){
						switch(item){
							case 'loginerror':
								jQuery('#reglogin input').addClass('uk-form-danger');
								if(leng == 'ru'){
									jQuery('#reglogin').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Латинскими буквами от 3 до 12 символов без пробелов</p>');
								}else{
									jQuery('#reglogin').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">In Latin letters from 3 to 12 characters without spaces</p>');	
								}
							break;
							case 'email_error':
								jQuery('#regemail input').addClass('uk-form-danger');
								if(leng == 'ru'){
									jQuery('#regemail').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Некорректный адрес электронной почты</p>');
								}else{
									jQuery('#regemail').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Incorrect e-mail address</p>');
								}
							break;
							case 'password_error':
								jQuery('#regpassword input').addClass('uk-form-danger');
								if(leng == 'ru'){
									jQuery('#regpassword').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Латинскими буквами и цифрами от 6 до 12 символов</p>');
								}else{
									jQuery('#regpassword').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Latin letters and numbers from 6 to 12 characters</p>');
								}
							break;
							case 'pasdoes_error':
								jQuery('#regpasdoes input').addClass('uk-form-danger');
								if(leng == 'ru'){
									jQuery('#regpasdoes').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Пароли не совпадают</p>');
								}else{
									jQuery('#regpasdoes').append('<p class="uk-text-meta uk-margin-remove-top uk-text-danger">Password does not match</p>');
								}
							break;
							case 'Извините, этот адрес email уже используется!':
								jQuery('#regemail input').addClass('uk-form-danger');
								if(leng == 'ru'){
									jQuery('#register-card .uk-alert-danger').removeClass('uk-hidden').append('<p class="uk-text-center">Извините, этот адрес email уже используется!</p>');
								}else{
									jQuery('#register-card .uk-alert-danger').removeClass('uk-hidden').append('<p class="uk-text-center">Sorry, this email address is already in use!</p>');
								}
							break;
						}
					});
				}
				if(result.result == 'success'){
					location.reload();
				}
				
				jQuery('#register-form').find('button[type="submit"]').attr('disabled', false);
			}
		});

		return false;
	});
	
	let appweight = 0;
	let quicks = [];
	let accord = [];
	document.ondragstart = prohibit;
	document.onselectstart = prohibit;
	document.oncontextmenu = prohibit;
	function prohibit() {return false;}
	
	initFunction();
});