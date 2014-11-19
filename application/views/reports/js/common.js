var sla_month_error_color    = 'red';
var sla_month_disabled_color = '#cdcdcd';
var sla_month_enabled_color  = '#fafafa';
$(document).ready(function() {
	$(".fancybox").fancybox({
		'overlayOpacity'        :       0.7,
		'overlayColor'          :       '#ffffff',
		'hideOnContentClick' : false,
		'autoScale':true,
		'autoDimensions': true,
		'onComplete': function(obj) { $($(obj).attr('href')).find('.filter-status').each(filter_mapping_mapping); }
	});

	$('.filter-status').on('change', filter_mapping_mapping).each(filter_mapping_mapping);

	var direct_link_visible = false;
	$('#current_report_params').click(function() {
		// make sure we always empty the field
		$('#link_container').html('');
		if (!direct_link_visible) {
			$('#link_container')
				.html('<form>'+_label_direct_link+' <input class="wide" type="text" value="'
					+ document.location.protocol + '//'
					+ document.location.host
					+ $('#current_report_params').attr('href')
					+ '"></form>')
				.css('position', 'absolute')
				.css('top', this.offsetHeight + this.offsetTop + 5)
				.css('right', '0')
				.show();
				direct_link_visible = true;
		} else {
			$('#link_container').hide();
			direct_link_visible = false;
		}
		return false;
	});

	$('#save_report').click(function() {
		if (!direct_link_visible) {
			$('#save_report_form')
				.css('position', 'absolute')
				.css('top', this.offsetHeight + this.offsetTop + 5)
				.css('right', '0')
				.show()
				.find('input[name=report_name]')
					.map(function() {
						var input = this;
						if(input.value == "") {
							input.focus();
						}
					});
				direct_link_visible = true;
		} else {
			$('#save_report_form').hide();
			direct_link_visible = false;
		}
		return false;
	});

	$("#report_id").bind('change', function() {
		$("#saved_report_form").trigger('submit');
	});

	$('.save_report_btn').parents('form').submit(function(ev) {
		ev.preventDefault();
		var form = $(this);
		if (!(check_form_values(this[0]))) {
			return;
		}
		var btn = form.find('.save_report_btn');
		btn.after(loadimg_sml);
		$.ajax({
			url: form[0].action,
			type: form[0].method,
			data: form.serialize(),
			complete: function() {
				btn.parent().find('img:last').remove();
			},
			success: function(data, status_msg, xhr) {
				if (data == null) {
					$.notify(_reports_error + ": " + xhr.responseText, {'sticky': true});
					return;
				}
				jgrowl_message(data.status_msg, _reports_success);
				if (!btn[0].form.report_id)
					$('form').append('<input type="hidden" name="report_id" value="'+data.report_id+'"/>');
				else
					$('#save_report_form').hide();
			},
			error: function(data) {
				var resp;
				try {
					resp = $.parseJSON(data.responseText).error;
				} catch (ex) {
					resp = "Unknown error";
				}
				$.notify(_reports_error + ": " + resp, {'sticky': true});
				btn.parent().find('img:last').remove();
			},
			dataType: 'json'
		});
	});

	$('select#report_type').on('change', function( e ) {

		var filterable = jQuery.fn.filterable.find( $('select[name="objects[]"]') ),
			type = e.target.value.replace( /s$/, "" );

		var url = _site_domain + _index_page;
		url += '/listview/fetch_ajax?query=[' + type + 's] all&columns[]=key&limit=1000000';

		if ( filterable ) {
			$.ajax({
				url: url,
				dataType: 'json',
				error: function( xhr ) {
					console.log( xhr.responseText );
				},
				success: function( data ) {

					var names = [];

					for ( var i = 0; i < data.data.length; i++ ) {
						names.push( data.data[ i ].key );
					}

					filterable.data = new Set( names );
					filterable.reset();

				}
			});
		}

	});

	$('#start_year, #end_year').on('change', function () {
		var start = 0;
		var end = 11;
		if (check_custom_months.start_date == undefined || check_custom_months.end_date == undefined) {
			return;
		}
		if (this.value == check_custom_months.start_date.getFullYear()) {
			start = check_custom_months.start_date.getMonth();
		}
		if (this.value == check_custom_months.end_date.getFullYear()) {
			end = check_custom_months.end_date.getMonth();
		}
		var html = '<option></option>';
		for (i = start; i <= end; i++) {
			html += '<option value="' + (i+1) + '">' + Date.monthNames[i] + '</option>';
		}
		if (this.id == 'start_year')
			$('#start_month').html(html);
		else
			$('#end_month').html(html);
	});

	$('#start_year, #end_year, #start_month, #end_month').on('change', check_custom_months);
	$("#delete_report").click(confirm_delete_report);

	$(".report_form").on('submit', function() {
		$('.filter-status:visible:checked', this).each(function() {
			$('#' + $(this).data('which')).find('input, select').attr('name', '');
		});
		$('.filter-status:not(:visible)', this).each(function() {
			$('#' + $(this).data('which')).find('input, select').attr('value', '-2');
		});
		return check_form_values();
	});
	$('#report_type').on('change', set_selection);
	set_selection();
});

function init_datepicker()
{
	// datePicker Jquery plugin
	var datepicker_enddate = (new Date()).asString();
	$('.date-pick').datePicker({clickInput:true, startDate:_start_date, endDate:datepicker_enddate});
	$('#cal_start').on(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				$('#cal_end').dpSetStartDate(d.asString());
			}
		}
	);
	$('#cal_end').on(
		'dpClosed',
		function(e, selectedDates)
		{
			var d = selectedDates[0];
			if (d) {
				d = new Date(d);
				$('#cal_start').dpSetEndDate(d.asString());
			}
		}
	);
}

function show_calendar(val, update) {
	if (val=='custom') {
		$("#custom_time").show();

		init_datepicker();
		init_timepicker();

		if (update == '') {
			$('input[name=start_time]').attr('value', '');
			$('input[name=end_time]').attr('value', '');
		}
	} else {
		$("#custom_time").hide();
	}
	disable_sla_fields(val);
}

function set_selection() {
	var val = $('#report_type').val();
	if ($.inArray(val, ['servicegroups', 'hostgroups', 'services', 'hosts']) === -1)
		val = 'hostgroups'; // Why? Because I found it like this
	$('.object-list-type').text(val);
	$('*[data-show-for]').hide();
	$('*[data-show-for~='+val+']').show();
}

function check_form_values(form)
{
	if (!form)
		form = document.documentElement;
	var errors = 0;
	var err_str = '';
	var cur_start = '';
	var cur_end = '';

	if ($("#report_period", form).val() == 'custom') {
		if ($('input[name=type]', form).val() != 'sla') {
			// date validation
			cur_start = Date.fromString($("input[name=cal_start]", form).val());
			var time =  $(".time_start", form).val().split(':');
			cur_start.addHours(time[0]);
			cur_start.addMinutes(time[1]);
			cur_end = Date.fromString($("input[name=cal_end]", form).val());
			time = $(".time_end", form).val().split(':');
			cur_end.addHours(time[0]);
			cur_end.addMinutes(time[1]);
			var now = new Date();
			if (!cur_start || !cur_end) {
				if (!cur_start) {
					errors++;
					err_str += "<li>" + _reports_invalid_startdate + ".</li>";
				}
				if (!cur_end) {
					errors++;
					err_str += "<li>" + _reports_invalid_enddate + ".</li>";
				}
			} else {
				if (cur_end > now) {
					if (!confirm(_reports_enddate_infuture)) {
						return false;
					} else {
						cur_end = now;
					}
				}
			}

			if (cur_end < cur_start) {
				errors++;
				err_str += "<li>" + _reports_enddate_lessthan_startdate + ".</li>";
				$(".datepick-start", form).addClass("time_error");
				$(".datepick-end", form).addClass("time_error");
			} else {
				$(".datepick-start", form).removeClass("time_error");
				$(".datepick-end", form).removeClass("time_error");
			}
		} else {
			// verify that we have years and month fields
			if ($('#start_year', form).val() == '' || $('#start_month', form).val() == ''
			|| $('#end_year', form).val() == '' || $('#end_month', form).val() == '') {
				errors++;
				//@@@Fixme: Add translated string
				err_str += "<li>Please select year and month for both start and end. ";
				err_str += "<br />Please note that SLA reports can only be generated for previous months</li>";
			}
			else {
				// remember: our months are 1-indexed
				cur_start = new Date(0);
				cur_start.setYear($("select[name=start_year]", form).val());
				cur_start.addMonths(Number($("select[name=start_month]", form).val()) - 1);

				cur_end = new Date(0);
				cur_end.setYear($("select[name=end_year]", form).val());
				cur_end.addMonths(Number($("select[name=end_month]", form).val()));
			}

			if (cur_end < cur_start) {
				errors++;
				err_str += "<li>" + _reports_enddate_lessthan_startdate + ".</li>";
				$(".datepick-start", form).addClass("time_error");
				$(".datepick-end", form).addClass("time_error");
			} else {
				$(".datepick-start", form).removeClass("time_error");
				$(".datepick-end", form).removeClass("time_error");
			}
		}
	}

	if ($('input[name=report_mode]:checked', form).val() != 'standard' && !$('#show_all', form).is(':checked') && $("[name=objects\\[\\]]", form).is('select') && $('[name=objects\\[\\]] option', form).length == 0) {
		errors++;
		err_str += "<li>" + _reports_err_str_noobjects + ".</li>";
	}

	if ($("#enter_sla", form).is(":visible")) {
		// check for sane SLA values
		var red_error = false;
		var max_val = 100;
		var nr_of_slas = 0;

		for (i=1;i<=12;i++) {
			var field_name = 'month_' + i;
			var input = $('input[id="' + field_name + '"]', form);
			var value = input.attr('value');
			value = value.replace(',', '.');
			if (value > max_val || isNaN(value)) {
				input.css('background', sla_month_error_color);
				errors++;
				red_error = true;
			} else {
				if (value != '') {
					nr_of_slas++;
				}
				if (input.attr('disabled'))
					input.css('background', sla_month_disabled_color);
				else
					input.css('background', sla_month_enabled_color);
			}
		}
		if (red_error) {
			err_str += '<li>' + _reports_sla_err_str + '</li>';
		}

		if (nr_of_slas == 0 && !red_error) {
			errors++;
			err_str += "<li>" + _reports_no_sla_str + "</li>";
		}
	}

	// display err_str if any
	if (!errors) {
		$('#response', form).html('');

		$('#response', form).hide();
		return true;
	}

	// clear all style info from progress
	var resp = $('#response', form);
	if (!resp.length)
		resp = $('#response');
	resp.attr("style", "");
	resp.html("<ul class='alert error'>" + err_str + "</ul>");
	window.scrollTo(0,0); // make sure user sees the error message
	return false;
}

// init timepicker once it it is shown
function init_timepicker()
{
	$("#time_start, #time_end").timePicker();
}

function disable_sla_fields(report_period)
{
	if (!$('#month_1').length)
		return;
	var now = new Date();
	var this_month = now.getMonth()+1;
	switch (report_period) {
		case 'thisyear':
			// weird as it seems, the following call actually ENABLES
			// all months. If not, we could end up with all months being
			// disabled for 'thisyear'
			disable_months(0, 12);
			for (i=this_month + 1;i<=12;i++)
			{
				$('.report_form #month_' + i).val('').attr('disabled', true).css('background-color', sla_month_disabled_color);
			}
			break;
		case 'custom':
			check_custom_months();
			break;
		case 'lastmonth':
			enable_last_months(1);
			break;
		case 'last3months':
			enable_last_months(3);
			break;
		case 'last6months':
			enable_last_months(6);
			break;
		case 'lastyear':
		case 'last12months':
			disable_months(0, 12);
			break;
		case 'lastquarter':
			if(this_month <= 3){
				from = 10;
				to = 12;
			} else if (this_month <= 6) {
				from = 1;
				to = 3;
			} else if (this_month <= 9){
				from = 4;
				to = 6;
			} else {
				from = 7;
				to = 9;
			}
			disable_months(from, to);
			break;
		default:
			for (i=1;i<=12;i++)
			{
				$('#month_' + i).attr('disabled', false).css('bgcolor', sla_month_enabled_color);
			}
	}
}


function disable_months(start, end)
{
	var disabled_state 		= false;
	var not_disabled_state 	= false;
	var col 				= false;
	start 	= Number(start);
	end 	= Number(end);
	for (i=1;i<=12;i++) {
		var cell = $('.report_form #month_' + i);
		if (start>end) {
			if ( i >= start || i <= end) {
				cell.attr('disabled', false).css('background-color', sla_month_enabled_color);
			} else {
				cell.val('').attr('disabled', true).css('background-color', sla_month_disabled_color);
			}
		} else {
			if ( i>= start && i <= end) {
				cell.attr('disabled', false).css('background-color', sla_month_enabled_color);
			} else {
				cell.val('').attr('disabled', true).css('background-color', sla_month_disabled_color);
			}
		}
	}
}


function check_custom_months()
{
	var f		 	= $('.report_form').get(0);
	// not SLA?
	if (!f['start_month'])
		return;

	if (check_custom_months.start_date == undefined) {
		check_custom_months.start_date = new Date(0);
		check_custom_months.end_date = new Date();
		$.ajax({
			url:  _site_domain + _index_page + '/sla/custom_start/',
			type: 'GET',
			dataType: 'json',
			success: function(data) {
				if (!data.timestamp) {
					$.notify("Unable to fetch oldest report timestamp: " + data.responseText, {'sticky': true});
				}
				check_custom_months.start_date.setTime(data.timestamp * 1000);
				var html = '<option></option>';
				for (i = check_custom_months.start_date.getFullYear(); i <= check_custom_months.end_date.getFullYear(); i++) {
					html += '<option>' + i + '</option>';
				}
				$('#start_year').html(html);
				$('#end_year').html(html);
			}
		});
	}

	var start_year 	= f.start_year.value;
	var start_month = f.start_month.value;
	var end_year 	= f.end_year.value;
	var end_month 	= f.end_month.value;
	if (start_year == '' || end_year == '' || start_month == '' || end_month == '') {
		disable_months(0, 0);
	} else if (start_year == end_year - 1 || start_year == end_year) {
		disable_months(start_month, end_month);
	} else {
		disable_months(0, 0);
	}
	$('#progress').hide();
}

/**
 * Generic function to enable month_ fields
 * depending on if selection is last 1, 3 or 6 months.
 */
function enable_last_months(mnr)
{
	var now = new Date();
	var this_month = now.getMonth()+1;
	var from = this_month - mnr;
	var to = this_month - 1;
	if (from <= 0)
		from += 12;
	if (to <= 0)
		to += 12;
	disable_months(from, to);
}

function confirm_delete_report()
{
	var id = $("#report_id").attr('value')

	var is_scheduled = $('#is_scheduled').text()!='' ? true : false;
	var msg = _reports_confirm_delete + "\n";
	var type = $('input[name=type]').attr('value');
	if (!id)
		return;
	if (is_scheduled) {
		msg += _reports_confirm_delete_warning;
	}
	msg = msg.replace("this saved report", "the saved report '"+$('#report_id option[selected=selected]').text()+"'");
	if (confirm(msg)) {
		$(this).after(loadimg_sml);
		$.ajax({
			url: _site_domain + _index_page + '/' + _controller_name + '/delete/',
			type: 'POST',
			data: {'report_id': id},
			complete: function() {
				$(loadimg_sml).remove();
			},
			success: function(data) {
				var a = document.createElement("a");
				a.href = window.location.href;
				if(a.search && a.search.indexOf("report_id="+id) !== -1) {
					window.location.href = a.search.replace(new RegExp("report_id="+id+"&?"), "");
				}
			},
			error: function(data) {
				var msg;
				try {
					msg = $.parseJSON(data.responseText).error;
				} catch (ex) {
					msg = "Unknown error";
				}
				$.notify("Failed to delete report: " + msg, {'sticky': true});
			},
			dataType: 'json'
		});
	}
}

function filter_mapping_mapping()
{
	if ($(this).is(':checked'))
		$('#' + $(this).data('which')).hide();
	else
		$('#' + $(this).data('which')).show();
	// when checking if the child is visible, the container must be visible
	// or we'd be checking the wrong thing.
	$(this).siblings('.configure_mapping').show();
	if (!$(this).siblings('.configure_mapping').find('.filter_map:visible').length)
		$(this).siblings('.configure_mapping').hide();
}
