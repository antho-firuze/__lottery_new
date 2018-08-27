/*
Description: This is script for authenticating AyoAvram Web Application
Author: Ahmad hertanto
Email: antho.firuze@gmail.com
File: js
*/
$(function () {
	"use strict";
	
	/**
	* Serialize tag type form
	*
	* @param String type Output type of data 'json' or 'object'
	* @returns json/object
	*/
	$.fn.serialize = function(type) {
		if (typeof(type) == 'undefined') type = 'object';
		type = type.toLowerCase();
		
		var o = {};
		// Exclude Select Element
		var a = this.find('[name]').not('select').serializeArray();
		$.each(a, function (i, v) {
			v.value = (v.value == 'on') ? '1' : v.value;
			o[v.name] = o[v.name] ? o[v.name] || v.value : v.value;
		});
		// Only Select Element
		var a = this.find('select').serializeArray();
		$.each(a, function (i, v) {
			if (o[v.name]) {
				o[v.name] += ',' + v.value;
			} else {
				o[v.name] = v.value;
			}
		});
		return (type == 'json') ? JSON.stringify(o) : o;
	};

	/**
	* Store a new settings in the browser
	*
	* @param String name Name of the setting
	* @param String val Value of the setting
	* @returns void
	*/
	function db_store(name, val) {
		if (typeof (Storage) !== "undefined") {
			localStorage.setItem(name, val);
		} else {
			window.alert('Please use a modern browser to properly view this template!');
		}
	}

	/**
	* Get a prestored setting
	*
	* @param String name Name of of the setting
	* @returns String The value of the setting | null
	*/
	function db_get(name) {
		if (typeof (Storage) !== "undefined") {
			return localStorage.getItem(name);
		} else {
			window.alert('Please use a modern browser to properly view this template!');
		}
	}

	/**
	* Remove a prestored setting
	*
	* @param String name Name of of the setting
	* @returns String The value of the setting | null
	*/
	function db_remove(name) {
		if (typeof (Storage) !== "undefined") {
			return localStorage.removeItem(name);
		} else {
			window.alert('Please use a modern browser to properly view this template!');
		}
	}

	// =================================================================================
	// Variable Global
	// =================================================================================
	var uri = URI(location.href);
	var uri_path = URI(uri.origin()+uri.path());
	var lang = URI.parseQuery(uri.query()).lang;
	var state = URI.parseQuery(uri.query()).state;
	var page = URI.parseQuery(uri.query()).page;
	var token = encodeURI(URI.parseQuery(uri.query()).token);
	var jsonrpc_url = uri.origin()+"/jsonrpc";
	var language = $("#shelter_language").text() ? JSON.parse($("#shelter_language").text()) : '';
	var language_sub = $("#shelter_language_sub").text() ? JSON.parse($("#shelter_language_sub").text()) : '';
	
	/**
	* For Auto Select Left Navbar
	*
	* @param String name Name of of the setting
	* @returns String The value of the setting | null
	*/
	function AutoSelectLeftNavbar() {
		$('ul#sidebarnav li.active').removeClass('active');
		$('ul#sidebarnav a.active').removeClass('active');
		var element = $('ul#sidebarnav a').filter(function () {
				return this.name == page;
		}).addClass('active').parent().addClass('active');
		while (true) {
				if (element.is('li')) {
						element = element.parent().addClass('in').parent().addClass('active');
				}
				else {
						break;
				}
		}
	}
	/**
	* For loading ajax page
	*
	* @param String name Name of of the setting
	* @returns String The value of the setting | null
	*/
	function LoadAjaxPage(curr_page) {
		// var curr_page = this.name;
		var content_url = URI(uri.origin()+uri.path()+'/getContent').search({"lang":lang, "state":state, "page":curr_page, "token":decodeURI(token)});
		$.ajax({ url:content_url, method:"GET", async:true, dataType:'json',
			success: function(data) {
				// console.log(data);
				if (data.status) {
					language_sub = data.language;
					console.log(language_sub);
					$('div.page-titles h3').html(data.title);
					$('div.content').html(data.content);
					$(".carousel").carousel();
					
					page = curr_page;
					var new_url = uri_path.search({"lang":lang, "state":state, "page":page, "token":decodeURI(token)});
					// history.pushState({}, '', new_url);
					history.replaceState({}, '', new_url);
					AutoSelectLeftNavbar();
					// Initialization();

				} else {
					alert(data.message);
				}
			},
			error: function(data, status, errThrown) {
				if (data.status >= 500){
					var message = data.statusText;
				} else {
					var error = JSON.parse(data.responseText);
					var message = error.message;
				}
				console.log(data);
				alert(message);
			}
		});
	}
	
	// ============================================================== 
	// Initial Procedure
	// ============================================================== 
	$(function () {
		AutoSelectLeftNavbar();
		// Initialization();
	});
	
	// ============================================================== 
	// loginform
	// ============================================================== 
	$(document).on('submit', '#loginform', function(e) {
		e.preventDefault();
		
		var params = $(this).serialize();
		params.dt = new Date().toISOString().substr(0,19).replace('T',' ');
		params.dt_epoc = Math.round((new Date).getTime()/1000);
		
		// console.log("a:"+vform); return false;
		// console.log("a:"+JSON.stringify(params)); return false;
		$.ajax({ url:jsonrpc_url, method:"POST", async:true, dataType:'json',
			data: JSON.stringify({ 
				"agent":"web", 
				"lang":lang,
				"method":"auth.login", 
				"id":Math.floor(Math.random() * 1000),
				"params":params
			}),
			beforeSend: function(xhr) { $(this).find('[type="submit"]').attr("disabled", "disabled"); },
			success: function(data) {
				// console.log(data);
				if (data.status) {
					db_store('session', JSON.stringify(data.result));
					var url_to = uri_path.search({
						"lang":lang, 
						"state":"lottery", 
						"page":"dashboard",
						"token":data.result.user.token
					});
					window.location = url_to;
				} else {
					alert(data.message);
				}
				setTimeout(function(){ $(this).find('[type="submit"]').removeAttr("disabled"); },1000);
			},
			error: function(data, status, errThrown) {
				if (data.status >= 500){
					var message = data.statusText;
				} else {
					var error = JSON.parse(data.responseText);
					var message = error.message;
				}
				alert(message);
				setTimeout(function(){ $(this).find('[type="submit"]').removeAttr("disabled"); },1000);
			}
		});
	}); 
	
	// ============================================================== 
	// recoverform
	// ============================================================== 
	$(document).on('submit', '#recoverform', function(e) {
		e.preventDefault();
		
		var params = $(this).serialize();
		
		// console.log("b:"+JSON.stringify(params)); return false;
		$.ajax({ url:jsonrpc_url, method:"POST", async:true, dataType:'json',
			data: JSON.stringify({ 
				"agent":"web", 
				"lang":lang,
				"method":"auth.forgot_password_simple", 
				"id":Math.floor(Math.random() * 1000),
				"params":params
			}),
			beforeSend: function(xhr) { $(this).find('[type="submit"]').attr("disabled", "disabled"); },
			success: function(data) {
				// console.log(data);
				if (data.status) {
					// var url_to = uri_path.search({
						// "lang":lang, 
						// "state":"auth", 
						// "page":"login"
					// });
					// window.location = url_to;
					
					alert(data.message);
					$("#recoverform").fadeOut();
					$("#loginform").fadeIn();
				} else {
					alert(data.message);
				}
				setTimeout(function(){ $(this).find('[type="submit"]').removeAttr("disabled"); },1000);
			},
			error: function(data, status, errThrown) {
				if (data.status >= 500){
					var message = data.statusText;
				} else {
					var error = JSON.parse(data.responseText);
					var message = error.message;
				}
				alert(message);
				setTimeout(function(){ $(this).find('[type="submit"]').removeAttr("disabled"); },1000);
			}
		});
	}); 
	
	// $('[name="name_first"]').val("Antho");
	// $('[name="name_last"]').val("Firuze");
	// $('[name="phone"]').val("085777974703");
	// $('[name="email"]').val("antho.firuze@gmail.com");
	
	// ============================================================== 
	// registerform
	// ============================================================== 
	$(document).on('submit', '#registerform', function(e) {
		e.preventDefault();
		
		var params = $(this).serialize();
		if (!params.account)
			if (lang == 'id')
				alert("Silahkan pilih Status Nasabah");
			else
				alert("Please choose Account Status");
		
		// console.log("c:"+JSON.stringify(params)); return false;
		$.ajax({ url:jsonrpc_url, method:"POST", async:true, dataType:'json',
			data: JSON.stringify({ 
				"agent":"web", 
				"lang":lang,
				"method":"auth.forgot_password_simple", 
				"id":Math.floor(Math.random() * 1000),
				"params":params
			}),
			beforeSend: function(xhr) { $(this).find('[type="submit"]').attr("disabled", "disabled"); },
			success: function(data) {
				// console.log(data);
				if (data.status) {
					alert(data.message);
					$("#recoverform").fadeOut();
					$("#loginform").fadeIn();
				} else {
					alert(data.message);
				}
				setTimeout(function(){ $(this).find('[type="submit"]').removeAttr("disabled"); },1000);
			},
			error: function(data, status, errThrown) {
				if (data.status >= 500){
					var message = data.statusText;
				} else {
					var error = JSON.parse(data.responseText);
					var message = error.message;
				}
				alert(message);
				setTimeout(function(){ $(this).find('[type="submit"]').removeAttr("disabled"); },1000);
			}
		});
	}); 
	// ============================================================== 
	// my profile
	// ============================================================== 
	$(document).on('click', '[id="profile"]', function(e){
		e.preventDefault();
		
		if (this.id == page)
			return false;
		
		LoadAjaxPage(this.id);
	});
	// ============================================================== 
	// change password
	// ============================================================== 
	$(document).on('click', '[id="chg_pwd"]', function(e){
		e.preventDefault();
		
		if (this.id == page)
			return false;
		
		LoadAjaxPage(this.id);
	});
	$(document).on('submit', '#changepasswordform', function(e) {
		e.preventDefault();
		
		if ($(this).find('[name="new_password"]').val() !== $(this).find('[name="new_password_confirm"]').val()) {
			alert(language_sub.err_new_password_confirm);
			return false;
		}
		
		var params = $(this).serialize();
		console.log(params); return false;
		
		$.ajax({ url:jsonrpc_url, method:"POST", async:true, dataType:'json',
			data: JSON.stringify({ 
				"agent":"web", 
				"lang":lang,
				"method":"auth.chg_password", 
				"id":Math.floor(Math.random() * 1000),
				"token":token,
				"params":params
			}),
			success: function(data) {
				// console.log(data);
				if (data.status) {
										// var url_to = uri_path.search({
						// "lang":lang, 
						// "state":"client", 
						// "page":"dashboard",
						// "token":data.result.user.token
					// });
					// window.location = url_to;
					alert(data.message);
					LoadAjaxPage('dashboard');
					// window.location = login_url;
				} else {
					alert(data.message);
				}
			},
			error: function(data, status, errThrown) {
				if (data.status >= 500){
					var message = data.statusText;
				} else {
					var error = JSON.parse(data.responseText);
					var message = error.message;
				}
				alert(message);
			}
		});
	});
	// ============================================================== 
	// logout
	// ============================================================== 
	$(document).on('click', '[id="logout"]', function(e) {
		e.preventDefault();
		
		if (!confirm(language.conf_logout)) { return false; } 
		
		$.ajax({ url:jsonrpc_url, method:"POST", async:true, dataType:'json',
			data: JSON.stringify({ 
				"agent":"web", 
				"lang":lang,
				"method":"auth.logout", 
				"id":Math.floor(Math.random() * 1000),
				"token":token
			}),
			success: function(data) {
				// console.log(data);
				if (data.status) {
					var login_url = uri.path('frontend').search({"lang":lang, "page":"home"});
					window.location = login_url;
				} else {
					alert(data.message);
				}
			},
			error: function(data, status, errThrown) {
				if (data.status >= 500){
					var message = data.statusText;
				} else {
					var error = JSON.parse(data.responseText);
					var message = error.message;
				}
				alert(message);
			}
		});
	});
	// ============================================================== 
	// sidebar-nav menu
	// ============================================================== 
	$(document).on('click', '[id="menu"]', function(e) {
		e.preventDefault();

		if (this.name == page)
			return false;
		
		LoadAjaxPage(this.name);
	});
	// ============================================================== 
	// nav-item dropdown
	// ============================================================== 
	$(function () {
		if (db_get('session')) {
			var session = JSON.parse(db_get('session'));
			var profile_img = $('img.profile-img img');
			// var profile_text = $('div.profile-text a.dropdown-toggle.u-dropdown').html(session.client.full_name);
			
			var profile_pic = $('img.profile-pic');
			var u_img = $('ul#dropdown-user .u-img img');
			// var u_text = $('div.u-text h4').html(session.client.full_name);
			// var u_text = $('div.u-text p').html(session.client.CorrespondenceEmail);
		}
	});
});