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


	.on('click', '#setup_stock .add', function() {
		var t = $(this),
			html = '<table class="setup-tab">' +
				'<tr><td class="label r">Наименование:<td><input id="name" type="text" maxlength="200" />' +
				'</table>',
			dialog = _dialog({
				width:440,
				head:'Добавление нового категории',
				content:html,
				submit:submit
			});
		$('#name').focus().keyEnter(submit);
		function submit() {
			var send = {
				op:'stock_add',
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
	.on('click', '#setup_stock .img_edit', function() {
		var t = $(this);
		while(t[0].tagName != 'DD')
			t = t.parent();
		var id = t.attr('val'),
			name = t.find('.name').html(),
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
				op:'stock_edit',
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
	.on('click', '#setup_stock .img_del', function() {
		var t = $(this),
			dialog = _dialog({
				top:90,
				width:300,
				head:'Удаление вида платежа',
				content:'<center><b>Подтвердите удаление категории.</b></center>',
				butSubmit:'Удалить',
				submit:submit
			});
		function submit() {
			while(t[0].tagName != 'DD')
				t = t.parent();
			var send = {
				op:'stock_del',
				id:t.attr('val')
			};
			dialog.process();
			$.post(AJAX_SETUP, send, function(res) {
				if(res.success) {
					$('.spisok').html(res.html);
					dialog.close();
					_msg('Удалено!');
					sortable();
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
								'<a href="' + URL + '&p=setup&d=stock" class="img_edit' + _tooltip('Настроить категорию комплектующих', -120) + '</a>' +
						'<tr><td class="label">Наименование:<td><input type="text" id="name" />' +
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
					title0:'Категория не выбрана',
					spisok:STOCK_SPISOK
				});
				$('#name').focus();

				function submit() {
					var send = {
						op:'stock_add',
						category_id:$('#category_id').val(),
						name:$.trim($('#name').val())
					};
					if(!send.name) err('Не указано наименование');
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
						left:110,
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
