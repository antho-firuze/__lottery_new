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

	/**
	* Generate random GUID
	*
	* 
	* @returns String the value of random guid
	*/
	var newGuid = function(){
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
			var r = Math.random() * 16 | 0, v = c === 'x' ? r : (r & 0x3 | 0x8);
			return v.toString(16);
		});
	};
	
	// =================================================================================
	// Variable Global
	// =================================================================================
	var uri = URI(location.href);
	var uri_path = URI(uri.origin()+uri.path());
	var lang = URI.parseQuery(uri.query()).lang;
	var page = URI.parseQuery(uri.query()).page;
	var jsonrpc_url = uri.origin()+"/jsonrpc";
	var language = $("#shelter_language").text() ? JSON.parse($("#shelter_language").text()) : '';
	var language_sub = $("#shelter_language_sub").text() ? JSON.parse($("#shelter_language_sub").text()) : '';
	
	var lang = lang ? lang : 'id';
	var page = page ? page : 'home';
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
		var content_url = URI(uri.origin()+uri.path()+'/getContent').search({"lang":lang, "page":curr_page});
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
					var new_url = uri_path.search({"lang":lang, "page":page});
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
		// AutoSelectLeftNavbar();
		// Initialization();
		loadDataPortfolio();
	});
	
	// ============================================================== 
	// login
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
					var url_to = uri.path('backend').search({
						"lang":lang, 
						"state":"client", 
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
	// forgot
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
	
	// ============================================================== 
	// register
	// ============================================================== 
	$(document).on('click', '[id="register"]', function(e) {
		e.preventDefault();

		var url_to = uri.path('backend').search({
			"lang":lang, 
			"state":"auth", 
			"page":"register"
		});
		window.location = url_to;
	});
	
	// ============================================================== 
	// load data portfolio
	// ============================================================== 
	function loadDataPortfolio(){
		$.ajax({ url:jsonrpc_url, method:"POST", async:true, dataType:'json',
			data: JSON.stringify({ 
				"agent":"web", 
				"lang":lang,
				"method":"portfolio.performance", 
				"id":Math.floor(Math.random() * 1000),
				"params":{"simpi_id":"812"}
			}),
			success: function(data) {
				console.log(data);
				if (data.status) {
					populatePortfolioPerformance(data.result);
					// alert(data.message);
					// $("#recoverform").fadeOut();
					// $("#loginform").fadeIn();
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
	}
	
	function populatePortfolioPerformance(o) {
		// var container = $('#portfolio_accordion');
		// var layout = $('#portfolio_performance_layout');
		// var c = '';
		// $.each(o, function(i){
			// var id = 'id="collapse'+newGuid()+'"';
			// var title = o.dataList[i]['title'];
			// var paneltype = o.dataList[i]['paneltype'];
			// var content = o.dataList[i]['content'];
			// var panel = $('<div class="panel panel-'+paneltype+'" />');
			
			// var collapse- = layout.find('a.collapse-header').attr('id', id);
			// var collapse = layout.find('div.collapse').attr('id', id);
			
			// panel.append( $('<div class="panel-heading" />')
				// .append( $('<h4 class="panel-title" />')
					// .append( $('<a style="display:table; table-layout:fixed; width:100%;" data-toggle="collapse" data-parent="#'+id+'" href="#'+id2+'" />')
						// .append( $('<div style="display:table-cell; width:90%; overflow:hidden; text-overflow:ellipsis" />')
							// .html(title) )
						// .append( $('<span class="pull-right glyphicon glyphicon-triangle-bottom"></span>') ) ) ) );
			// panel.append( $('<div '+id2+' class="panel-collapse collapse" />')
				// .append( $('<div class="panel-body" />')
					// .html(content) ) );
					
			// container.append(panel)
		// });
	}
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
			var profile_text = $('div.profile-text a.dropdown-toggle.u-dropdown').html(session.client.full_name);
			
			var profile_pic = $('img.profile-pic');
			var u_img = $('ul#dropdown-user .u-img img');
			var u_text = $('div.u-text h4').html(session.client.full_name);
			var u_text = $('div.u-text p').html(session.client.CorrespondenceEmail);
		}
	});
});