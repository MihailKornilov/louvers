var AJAX_SETUP = APP_HTML + '/ajax/setup.php?' + VALUES,
	hashLoc,
	hashSet = function(hash) {
		if(!hash && !hash.p)
			return;
		hashLoc = hash.p;
		var s = true;
		switch(hash.p) {
			case 'client':
				if(hash.d == 'info')
					hashLoc += '_' + hash.id;
				break;
			case 'zayav':
				if(hash.d == 'info')
					hashLoc += '_' + hash.id;
				else if(hash.d == 'add')
					hashLoc += '_add' + (REGEXP_NUMERIC.test(hash.id) ? '_' + hash.id : '');
				//else if(!hash.d)
				//	s = false;
				break;
			default:
				if(hash.d) {
					hashLoc += '_' + hash.d;
					if(hash.d1)
						hashLoc += '_' + hash.d1;
				}
		}
		if(s)
			VK.callMethod('setLocation', hashLoc);
	},
	clientAdd = function(callback) {
		var html = '<table class="client-add">' +
				'<tr><td class="label">Название организации:<td><input type="text" id="ca-org" maxlength="100">' +
				'<tr><td class="label">Контактное лицо (фио):<td><input type="text" id="ca-fio" maxlength="100">' +
				'<tr><td class="label">Телефоны:<td><input type="text" id="ca-telefon" maxlength="100">' +
				'<tr><td class="label">Адрес:<td><input type="text" id="ca-adres" maxlength="100">' +
			'</table>',
			dialog = _dialog({
				width:450,
				head:'Добавление нoвого клиента',
				content:html,
				submit:submit
			});
		$('#ca-org').focus();
		$('#ca-org,#ca-fio,#ca-telefon,#ca-adres').keyEnter(submit);
		function submit() {
			var send = {
				op:'client_add',
				org:$.trim($('#ca-org').val()),
				fio:$.trim($('#ca-fio').val()),
				telefon:$.trim($('#ca-telefon').val()),
				adres:$.trim($('#ca-adres').val())
			};
			if(!send.org && !send.fio) {
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">Обязательно укажите название организации или контактное лицо</SPAN>',
					top:-47,
					left:98,
					indent:80,
					show:1,
					remove:1
				});
				$('#ca-org').focus();
			} else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Новый клиент внесён');
						if(typeof callback == 'function')
							callback(res);
						else
							document.location.href = URL + '&p=client&d=info&id=' + res.uid;
					} else dialog.abort();
				}, 'json');
			}
		}
	},
	clientFilter = function() {
		var v = {
				op:'client_spisok',
				find:$.trim($('#find')._search('val'))
			},
			loc = '';

		$('.filter')[v.find ? 'hide' : 'show']();

		if(v.find) loc += '.find=' + escape(v.find);
		else {
		}
		VK.callMethod('setLocation', hashLoc + loc);

		_cookie('client_find', escape(v.find));

		return v;
	},
	clientSpisok = function() {
		var result = $('.result');
		if(result.hasClass('busy'))
			return;
		result.addClass('busy');
		$.post(AJAX_MAIN, clientFilter(), function(res) {
			result.removeClass('busy');
			if(res.success) {
				result.html(res.result);
				$('.left').html(res.spisok);
			}
		}, 'json');
	},

	stockFilter = function() {
		var v = {
				op:'stock_spisok',
				find:$.trim($('#find')._search('val'))
			},
			loc = '';
		return v;
	},
	stockSpisok = function() {
		$('#mainLinks').addClass('busy');
		$.post(AJAX_MAIN, stockFilter(), function (res) {
			$('#mainLinks').removeClass('busy');
			$('.result').html(res.result);
			$('#spisok').html(res.spisok);
		}, 'json');
	};

$.fn.clientSel = function(o) {
	var t = $(this);
	o = $.extend({
		width:270,
		add:null,
		client_id:t.val() || 0,
		func:function() {},
		funcAdd:function() {}
	}, o);

	if(o.add)
		o.add = function() {
			clientAdd(function(res) {
				var arr = [];
				arr.push(res);
				t._select(arr);
				t._select(res.uid);
				o.funcAdd(res);
			});
		};

	t._select({
		width:o.width,
		title0:'Начните вводить данные клиента...',
		spisok:[],
		write:1,
		nofind:'Клиентов не найдено',
		func:o.func,
		funcAdd:o.add,
		funcKeyup:clientsGet
	});
	clientsGet();

	function clientsGet(val) {
		var send = {
			op:'client_sel',
			val:val || '',
			client_id:o.client_id
		};
		t._select('process');
		$.post(AJAX_MAIN, send, function(res) {
			t._select('cancel');
			if(res.success) {
				t._select(res.spisok);
				if(o.client_id) {
					t._select(o.client_id);
					o.client_id = 0;
				}
			}
		}, 'json');
	}
	return t;
};
$.fn.productList = function(o) {
	var t = $(this),
		id = t.attr('id'),
		num = 1,
		n;

	if(typeof o == 'string') {
		if(o == 'get') {
			var units = t.find('.ptab'),
				send = [];
			for(n = 0; n < units.length; n++) {
				var u = units.eq(n),
					attr = id + u.attr('val'),
					pr = $('#' + attr + 'id').val(),
					prsub = $('#' + attr + 'subid').val(),
					x = _cena($('#' + attr + 'x').val()),
					y = _cena($('#' + attr + 'y').val()),
					count = $('#' + attr + 'count').val();
				if(pr == 0)
					continue;
				if(!x || !y)
					return 'size_error';
				if(!REGEXP_NUMERIC.test(count) || count == 0)
					return 'count_error';
				send.push(pr + ':' + prsub + ':' + x + ':' + y + ':' + count);
			}
			return send.length == 0 ? false : send.join(';');
		}
	}

	t.html('<div class="_product-list"><a class="add">Добавить поле</a></div>');
	var add = t.find('.add');
	add.click(itemAdd);

	if(typeof o == 'object')
		for(n = 0; n < o.length; n++)
			itemAdd(o[n])
	else
		itemAdd([]);

	function itemAdd(v) {
		var attr = id + num,
			attr_id = attr + 'id',
			attr_subid = attr + 'subid',
			attr_count = attr + 'count',
			attr_x = attr + 'x',
			attr_y = attr + 'y',
			html = '<table id="ptab'+ num + '" class="ptab" val="'+ num + '"><tr>' +
				'<td class="td">' +
					'<input type="hidden" id="' + attr_id + '" value="' + (v[0] || 0) + '" />' +
					'<input type="hidden" id="' + attr_subid + '" value="' + (v[1] || 0) + '" />' +
					'<table class="doptab">' +
						'<tr><td class="lab">Ширина:<td><input type="text" id="' + attr_x + '" class="size" /> м.' +
						'<tr><td class="lab">Высота:<td><input type="text" id="' + attr_y + '" class="size" /> м.' +
						'<tr><td class="lab">Количество:<td><input type="text" id="' + attr_count + '" value="' + (v[2] || '') + '" class="count" maxlength="3" /> шт.' +
					'</table>' +
				'<td class="td">' + (num > 1 ? '<div class="img_del"></div>' : '') +
				'</table>';
		add.before(html);
		var ptab = $('#ptab' + num);
		ptab.find('.img_del').click(function() {
			ptab.remove();
		});
		$('#' + attr_id)._select({
			width:170,
			title0:'Не указано',
			spisok:CATEGORY_SPISOK,
			func:function(id) {
				$('#' + attr_subid)
					._select('remove')
					.val(0);
				if(id > 0 && CATEGORY_SUB_SPISOK[id])
					subSel(id, attr_subid, attr_x);
				$('#' + attr_x).focus();
				$('#' + attr_count).val(id ? 1 : '');
			}
		});
		subSel(v[0] || 0, attr_subid, attr_x);
		num++;
	}
	function subSel(id, attr_subid, attr_x) {
		if(id == 0 || !CATEGORY_SUB_SPISOK[id])
			return;
		$('#' + attr_subid)._select({
			width:160,
			title0:'Подкатегория не указана',
			spisok:CATEGORY_SUB_SPISOK[id],
			func:function() {
				$('#' + attr_x).focus();
			}
		});
	}
	return t;
};

$(document)
	.ajaxSuccess(function(event, request, settings) {
		if(request.responseJSON.pin) {
			var html = '<table class="setup-tab">' +
					'<tr><td colspan="2"><div class="_info">Истекло время действия пин-кода. Требуется подтверждение.</div>' +
					'<tr><td class="label">Пин-код:<td><input id="tpin" type="password" maxlength="10" />' +
				'</table>',
				dialog = _dialog({
					width:250,
					head:'Подтверждение пин-кода',
					content:html,
					butSubmit:'Подтвердить',
					butCancel:'',
					submit:submit
				});
			$('#tpin').focus().keyEnter(submit);
		}
		function submit() {
			var send = {
				op:'pin_enter',
				pin:$.trim($('#tpin').val())
			};
			if(!send.pin) { err('Не заполнено поле'); $('#tpin').focus(); }
			else if(send.pin.length < 3) { err('Длина пин-кода от 3 до 10 символов'); $('#tpin').focus(); }
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success)
						dialog.close();
					else if(res.max)
						location.reload();
					else {
						dialog.abort();
						err(res.text);
						$('#tpin').val('').focus();
					}
				}, 'json');
			}
		}
		function err(msg) {
			dialog.bottom.vkHint({
				msg:'<span class="red">' + msg + '</span>',
				top:-47,
				left:62,
				indent:50,
				show:1,
				remove:1
			});
		}
	})

	.on('click', '#client ._next', function() {
		var t = $(this),
			send = clientFilter();
		if(t.hasClass('busy'))
			return;
		send.page = t.attr('val');
		t.addClass('busy');
		$.post(AJAX_MAIN, send, function(res) {
			if(res.success)
				t.after(res.spisok).remove();
			else
				t.removeClass('busy');
		}, 'json');
	})
	.on('click', '#client #filter_clear', function() {
		$('#find')._search('clear');
		clientSpisok();
	})

	.on('click', '.zayav_add', function() {
		if(!window.CLIENT)
			CLIENT = {
				id:0,
				fio:''
			};
		var html =
			'<table class="zayav-add">' +
				'<tr><td class="label">Клиент:' +
					'<td><INPUT type="hidden" id="client_id" value="' + CLIENT.id + '">' +
						'<b>' + CLIENT.fio + '</b>' +
				'<tr><td class="label topi">Изделия:<td id="product">' +
				'<tr><td class="label top">Заметка:	<td><textarea id="comm"></textarea>' +
			'</table>',
			dialog = _dialog({
				width:550,
				top:30,
				head:'Внесение новой заявки',
				content:html,
				submit:submit
			});
		if(!CLIENT.id)
			$('#client_id').clientSel({add:1});
		$('#product').productList();
		$('#comm').autosize();
		function submit() {
			var msg,
				send = {
					op:'zayav_add',
					client_id:$('#client_id').val(),
					product:$('#product').productList('get'),
					comm:$('#comm').val()
				};
			if(send.client_id == 0) msg = 'Не выбран клиент';
			else if(!send.product) msg = 'Не выбраны изделия';
			else if(send.product == 'size_error') msg = 'Некорректно введён размер изделия';
			else if(send.product == 'count_error') msg = 'Некорректно введено количество изделий';
			else {
				dialog.process();
				$.post(AJAX_MAIN, send, function(res) {
					if(res.success) {
						dialog.close();
						_msg('Заявка внесена');
						location.href = URL + '&p=zayav&d=info&id=' + res.id;
					} else
						dialog.abort();
				}, 'json');
			}
			if(msg)
				dialog.bottom.vkHint({
					msg:'<SPAN class="red">' + msg + '</SPAN>',
					top:-48,
					left:171,
					indent:50,
					show:1,
					remove:1
				});
		}
	})


	.on('click', '#setup_category .add', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="200" />' +
				'</table>',
			dialog = _dialog({
				width:440,
				head:'Добавление новой категории',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'category_add',
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:120,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_category .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name a').html(),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">Наименование:' +
					'<td><input id="name" type="text" maxlength="200" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				width:440,
				head:'Редактирование категории',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'category_edit',
				id:id,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Сохранено!');
						sortable();
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_category .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление категории',
				content:'<center><b>Подтвердите удаление категории.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'category_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SETUP, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено.');
					sortable();
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.on('click', '#setup_categorysub .add', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="200" />' +
				'</table>',
			dialog = _dialog({
				width:440,
				head:'Добавление новой подкатегории',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'categorysub_add',
				category_id:CATEGORY_ID,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:120,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Внесено.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_categorysub .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'TR')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">Наименование:' +
				'<td><input id="name" type="text" maxlength="200" value="' + name + '" />' +
				'</table>',
			dialog = _dialog({
				width:440,
				head:'Редактирование подкатегории',
				content:html,
				butSubmit:'Сохранить',
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'categorysub_edit',
				id:id,
				name:$('#name').val()
			};
			if(!send.name) {
				dialog.bottom.vkHint({
					msg:'<SPAN class=red>Не указано наименование</SPAN>',
					top:-47,
					left:131,
					indent:50,
					show:1,
					remove:1
				});
				$('#name').focus();
			} else {
				dialog.process();
				$.post(AJAX_SETUP, send, function(res) {
					if(res.success) {
						$('.spisok').html(res.html);
						dialog.close();
						_msg('Сохранено.');
					} else
						dialog.abort();
				}, 'json');
			}
		}
	})
	.on('click', '#setup_categorysub .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление подкатегории',
				content:'<center><b>Подтвердите удаление.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'TR')
				t = t.parent();
			var send = {
				op:'categorysub_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SETUP, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено.');
				} else
					dialog.abort();
			}, 'json');
		}
	})

	.ready(function() {
		if($('#client').length) {
			$('#find')._search({
				width:602,
				focus:1,
				enter:1,
				txt:'Введите текст и нажмите Enter',
				func:clientSpisok
			}).inp(C.find);
			$('#buttonCreate').click(clientAdd);
		}
		if($('#client-info').length) {
			$('.cedit').click(function() {
				var html = '<table class="client-add e">' +
					'<tr><td class="label">Название организации:<td><input type="text" id="ca-org" maxlength="100" value="' + CLIENT.org + '">' +
					'<tr><td class="label">Контактное лицо:<td><input type="text" id="ca-fio" maxlength="100" value="' + CLIENT.fio + '">' +
					'<tr><td class="label">Телефон:<td><input type="text" id="ca-telefon" maxlength="100" value="' + CLIENT.telefon + '">' +
					'<tr><td class="label">Адрес:<td><input type="text" id="ca-adres" maxlength="100" value="' + CLIENT.adres + '">' +
				'</table>';
				var dialog = _dialog({
					head:'Редактирование данных клиента',
					top:30,
					width:450,
					content:html,
					butSubmit:'Сохранить',
					submit:submit
				});
				$('#ca-org,#ca-fio,#ca-telefon,#ca-adres').keyEnter(submit);
				function submit() {
					var send = {
							op:'client_edit',
							client_id:CLIENT.id,
							org:$.trim($('#ca-org').val()),
							fio:$.trim($('#ca-fio').val()),
							telefon:$.trim($('#ca-telefon').val()),
							adres:$.trim($('#ca-adres').val())
						};
					if(!send.fio) {
						err('Обязательно укажите название организации или контактное лицо');
						$('#ca-org').focus();
					} else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							if(res.success) {
								CLIENT = res;
								$('.left:first').html(res.html);
								dialog.close();
								_msg('Данные клиента изменены');
							} else {
								err(res.text);
								dialog.abort();
							}
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<span class="red">' + msg + '</span>',
						top:-47,
						left:110,
						indent:70,
						show:1,
						remove:1
					});
				}
			});
			$('.cdel').click(function() {
				var dialog = _dialog({
					top:90,
					width:300,
					head:'Удаление клиента',
					content:'<center><b>Подтвердите удаление.</b></center>',
					butSubmit:'Удалить',
					submit:submit
				});
				function submit() {
					var send = {
						op:'client_del',
						id:CLIENT.id
					};
					dialog.process();
					$.post(AJAX_MAIN, send, function(res) {
						if(res.success) {
							dialog.close();
							_msg('Клиент удален!');
							location.href = URL + '&p=client';
						} else
							dialog.abort();
					}, 'json');
				}
			});
		}

		if($('#stock').length) {
			$('.vkButton').click(function() {
				var html =
					'<table id="stock-add-tab">' +
						'<tr><td class="label">Категория:' +
							'<td><input type="hidden" id="category_id" />' +
								'<a href="' + URL + '&p=setup&d=stock" class="img_edit' + _tooltip('Настроить категорию комплектующих', -220, 'r') + '</a>' +
						'<tr><td class="label">Наименование:<td><input type="text" id="name" />' +
						'<tr><td class="label">Единица измерения:<td><input type="hidden" id="measure" />' +
						'<tr><td class="label">Расход на м&sup2;:<td><input type="text" id="expense" />' +
					'</table>',
					dialog = _dialog({
						top:70,
						width:450,
						head:'Внесение новой позиции склада',
						content:html,
						submit:submit
					});
				$('#category_id')._select({
					width:270,
					title0:'Не выбрана',
					spisok:STOCK_SPISOK
				});
				$('#measure')._select({
					width:90,
					title0:'Не указана',
					spisok:MEASURE_SPISOK
				});
				$('#name').focus();

				function submit() {
					var send = {
						op:'stock_add',
						category_id:$('#category_id').val(),
						name:$.trim($('#name').val()),
						measure:$('#measure').val() * 1,
						expense:$.trim($('#expense').val())
					};
					if(!send.name) err('Не указано наименование');
					else if(!send.measure) err('Не указана единица измерения');
					else if(send.expense && !REGEXP_NUMERIC.test(send.expense)) err('Некорректно указан расход на м&sup2;');
					else {
						dialog.process();
						$.post(AJAX_MAIN, send, function(res) {
							dialog.abort();
							if(res.success) {
								dialog.close();
								stockSpisok();
							}
						}, 'json');
					}
				}
				function err(msg) {
					dialog.bottom.vkHint({
						msg:'<SPAN class="red">' + msg + '</SPAN>',
						left:130,
						top:-47,
						indent:50,
						show:1,
						remove:1
					});
				}
			});
			$('#find')
				._search({
					width:250,
					focus:1,
					txt:'Быстрый поиск...',
					enter:1,
					func:stockSpisok
				})
				.inp(STOCK.find);
		}
	});
