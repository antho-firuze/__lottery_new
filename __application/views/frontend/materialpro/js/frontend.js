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
	function Initialization() {
		// var rate = $(document).find('.barrating');
		// if (rate.length > 0)
			// rate.barrating({showValues:true,showSelectedRating:false,readonly:true,hoverState:false,theme:'bars-square'});
	}
	
	$(function () {
		// AutoSelectLeftNavbar();
		Initialization();
		loadDataPortfolio();
		
		populatePortfolioTop5();
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
				// console.log(data);
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
	
	function populatePortfolioTop5() {
		var table_cont = $('[id="tablePortfolio"]');
		var tbl_class = table_cont.attr('class');
		
		var container = $('<div class="'+tbl_class+'"><table class="table" style="margin-bottom:0px;"><thead></thead><tbody></tbody></table></div>'),
				table = container.find('table'),
				thead = container.find('thead'),
				tbody = container.find('tbody');
		
		// TABLE HEADER
		// if (o.showheader){
			// var tr = $('<tr />');
			// $.each(o.columns, function(j){
				// if (c==1){ if (o.rowno){ tr.append( $('<th />').html('#') ); } }
				// tr.append( $('<th />').html(o.columns[j]['title']) );
				// c++;
			// });
			// tr.appendTo(thead);
		// }
		var o = [
			{ name: "Obligasi Pemerintah", category: "Obligasi", percent: "31.00" },
			{ name: "Astra International", category: "Saham", percent: "6.00" },
			{ name: "Bank Central Asia", category: "Saham", percent: "6.00" },
			{ name: "PT. BRI (Persero)", category: "Saham", percent: "5.00" },
			{ name: "Bank Mandiri (Persero)", category: "Saham", percent: "5.00" },
		];
		
		// TABLE DETAIL
		$.each(o, function(i){
			var tr = $('<tr />');
			$.each(o[i], function(j){
				tr.append( $('<td style="padding:0px;" />').html(o[i][j]) );
			});
			tr.appendTo(tbody);
		});
		// return container;
		table_cont.append(container);
	}
	
	function populatePortfolioPerformance(o) {
		var container = $('#portfolio_accordion');
		var layout = $($('#portfolio_performance_layout')[0].innerHTML);
		container.html("");
		$.each(o, function(i){
			// console.log(o[i]['PortfolioNameShort']);
			var id = newGuid();
			var layout = $($('#portfolio_performance_layout')[0].innerHTML);
			var img = uri.hash('')+'__repository/portfolio/'+o[i]['PortfolioCode']+'.png';
			layout.attr('id', o[i]['simpiID']+'_'+o[i]['PortfolioID']);
			layout.find('#asset_type').html(o[i]['AssetTypeCode']);
			layout.find('a.view_detail').attr('href', '#'+id);
			layout.find('a.view_detail').data('simpi_id', o[i]['simpiID']);
			layout.find('a.view_detail').data('portfolio_id', o[i]['PortfolioID']);
			layout.find('div.collapse').attr('id', id);
			layout.find('img.img-portfolio').attr('src', img);
			layout.find('img.img-portfolio').attr('title', o[i]['PortfolioNameShort']);
			
			layout.find('div.nav-unit').html(accounting.formatMoney(o[i]['NAVperUnit'], '', 2, ".", ","));
			layout.find('div.nav-date').html(moment(o[i]['PositionDate']).format('DD MMM YYYY'));
			layout.find('.barrating').barrating({initialRating:o[i]['RiskScore'],emptyValue:0,allowEmpty:true,showValues:true,showSelectedRating:false,readonly:true,hoverState:false,theme:'bars-square'});
			
			layout.find('#mtd').html(accounting.formatMoney(o[i]['rMTD'], '', 2, ".", ",")+'%');
			layout.find('#ytd').html(accounting.formatMoney(o[i]['rYTD'], '', 2, ".", ",")+'%');
			layout.find('#1y').html(accounting.formatMoney(o[i]['r1Y'], '', 2, ".", ",")+'%');
			layout.find('#2y').html(accounting.formatMoney(o[i]['r2Y'], '', 2, ".", ",")+'%');
			layout.find('#5y').html(accounting.formatMoney(o[i]['r5Y'], '', 2, ".", ",")+'%');
			layout.find('#5y').html(accounting.formatMoney(o[i]['r5Y'], '', 2, ".", ",")+'%');
			layout.find('#inception').html(accounting.formatMoney(o[i]['rInception'], '', 2, ".", ",")+'%');
			
			layout.find('#investment_goal').html(o[i]['InvestmentGoal']);
			layout.find('#subs_fee').html(o[i]['SubsFee']);
			layout.find('#redeem_fee').html(o[i]['RedeemFee']);
			layout.find('#switching_fee').html(o[i]['SwitchingFee']);
			// layout.find('.barrating').barrating({initialRating:null,emptyValue:0,allowEmpty:true,showValues:true,showSelectedRating:false,readonly:true,hoverState:false,theme:'bars-square'});
			container.append(layout);
		});
	}
	
	$(document).on('click', '[id="view_detail"]', function(e) {
		var simpi_id = $(this).data('simpi_id');
		var portfolio_id = $(this).data('portfolio_id');
		// console.log($(this).data('portfolio_id'));
		// console.log($(this).attr('href'));
		// var this_id = $(this).attr('href');
		// console.log($(this).parent().parent().parent().find(this_id));
		// console.log($(this).hasClass('collapsed'));
		// console.log($(this).attr('href').hasClass('collapse'));
		if (!$(this).hasClass('collapsed')) {
			$.ajax({ url:jsonrpc_url, method:"POST", async:true, dataType:'json',
				data: JSON.stringify({ 
					"agent":"web", 
					"lang":lang,
					"method":"portfolio.chart", 
					"id":Math.floor(Math.random() * 1000),
					"params":{"simpi_id":simpi_id, "portfolio_id":portfolio_id}
				}),
				success: function(data) {
					// console.log(data);
					if (data.status) {
						// console.log(data.result);
						populateLineChart(simpi_id, portfolio_id, data.result);
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
	});
	
	function populateLineChart(simpi_id, portfolio_id, data) {
		
		var x_axis = [], y_axis = [];
		$.each(data, function(i){ 
			x_axis.push(data[i].PositionDate);
			y_axis.push(data[i].line1 * 100);
		});
		
		var dom = $('#'+simpi_id+'_'+portfolio_id).find('#line-chart');
		dom.html('');
		var mytempChart = echarts.init(dom[0]);
		var option = {
				tooltip: { trigger: 'axis' },
				// legend: { data: ['max temp', 'min temp'] },
				toolbox: {
					show: true,
					feature: {
							magicType: { show: true, type: ['line', 'bar'] },
							restore: { show: true },
							saveAsImage: { show: true }
					}
				},
				color: ["#55ce63", "#009efb"],
				calculable: true,
				xAxis: [{
					type: 'category',
					boundaryGap: false,
					data: x_axis
				}],
				yAxis: [{
					type: 'value',
					axisLabel: { formatter: '{value}' }
				}],
				series: [{
					name: 'max temp',
					type: 'line',
					color: ['#000'],
					data: y_axis,
					itemStyle: {
							normal: {
									lineStyle: {
											shadowColor: 'rgba(0,0,0,0.3)',
											shadowBlur: 10,
											shadowOffsetX: 8,
											shadowOffsetY: 8
									}
							}
					},
						// markLine: {
								// data: [
										// { type: 'average', name: 'Average' }
								// ]
						// }
				}]
		};
		mytempChart.setOption(option, true);
		
	}
	
	$(window).on("resize", function() {
		$(document).find('[id="line-chart"]').each(function(e){
			if ($(this).attr('_echarts_instance_'))
				echarts.getInstanceById($(this).attr('_echarts_instance_')).resize();
		});
	});

	function populateDoughnutChart() {
		
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