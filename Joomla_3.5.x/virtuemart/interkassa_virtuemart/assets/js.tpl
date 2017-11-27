<script type="text/javascript">
var selpayIK = {
  actForm : 'https://sci.interkassa.com/',
  req_url : location.href + '&paysys',
  selPaysys : function()
	{
    if($('button.sel-ps-ik').length > 0)
      $('.sel-ps-ik').click()
    else
		{
      var form = $('form[name="payment_interkassa"]')
      form[0].action = selpayIK.actForm
      setTimeout(function(){form[0].submit()},200)
    }
  },
        paystart : function (data) {
            data_array = (this.IsJsonString(data))? JSON.parse(data) : data
            console.log(data_array);
            var form = $('form[name="payment_interkassa"]');
            if (data_array['resultCode'] != 0) {
                $('input[name="ik_act"]').remove();
                $('input[name="ik_int"]').remove();
                $('form[name="payment_interkassa"]').attr('action', selpayIK.actForm).submit()
            }
            else {
                if (data_array['resultData']['paymentForm'] != undefined) {
                    var data_send_form = [];
                    var data_send_inputs = [];
                    data_send_form['url'] = data_array['resultData']['paymentForm']['action'];
                    data_send_form['method'] = data_array['resultData']['paymentForm']['method'];
                    for (var i in data_array['resultData']['paymentForm']['parameters']) {
                        data_send_inputs[i] = data_array['resultData']['paymentForm']['parameters'][i];
                    }
                    $('body').append('<form method="' + data_send_form['method'] + '" id="tempformIK" action="' + data_send_form['url'] + '"></form>');
                    for (var i in data_send_inputs) {
                        $('#tempformIK').append('<input type="hidden" name="' + i + '" value="' + data_send_inputs[i] + '" />');
                    }
                    $('#tempformIK').submit();
                }
                else {
                    if (document.getElementById('tempdivIK') == null)
                        $('form[name="payment_interkassa"]').after('<div id="tempdivIK">' + data_array['resultData']['internalForm'] + '</div>');
                    else
                        $('#tempdivIK').html(data_array['resultData']['internalForm']);
                    $('#internalForm').attr('action', 'javascript:selpayIK.selPaysys2()')
                }
            }
        },
        selPaysys2 : function () {
            var form2 = $('#internalForm');
            var msg2 = form2.serialize();
            $.ajax({
                type: 'POST',
                url: selpayIK.req_url,
                data: msg2,
                success: function (data) {
                    selpayIK.paystart2(data.responseText);
                },
                error: function (xhr, str) {
                    alert('Error: ' + xhr.responseCode);
                }
            });
        },
        paystart2 : function (string) {
            data_array = (this.IsJsonString(data))? JSON.parse(data) : data;
            console.log(data_array);
            var form2 = $('#internalForm');
            if (data_array['resultCode'] != 0) {
                form2[0].action = selpayIK.actForm;
                $('input[name="ik_act"]').remove();
                $('input[name="ik_int"]').remove();
                $('input[name="sci[ik_int]"]').remove();
                setTimeout(function(){form2[0].submit()},200)
            }
            else {
                $('#tempdivIK').html('');
                if (data_array['resultData']['paymentForm'] != undefined) {
                    var data_send_form = [];
                    var data_send_inputs = [];
                    data_send_form['url'] = data_array['resultData']['paymentForm']['action'];
                    data_send_form['method'] = data_array['resultData']['paymentForm']['method'];
                    for (var i in data_array['resultData']['paymentForm']['parameters']) {
                        data_send_inputs[i] = data_array['resultData']['paymentForm']['parameters'][i];
                    }
                    $('#tempdivIK').append('<form method="' + data_send_form['method'] + '" id="tempformIK2" action="' + data_send_form['url'] + '"></form>');
                    for (var i in data_send_inputs) {
                        $('#tempformIK2').append('<input type="hidden" name="' + i + '" value="' + data_send_inputs[i] + '" />');
                    }
                    $('#tempformIK2').submit();
                }
                else {
                    $('#tempdivIK').append(data_array['resultData']['internalForm']);
                }
            }
        },
        IsJsonString : function(str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        }
    }


					(function($) {

						$(document).ready(function () {

							var curtrigger = false;
							var form = $('form[name="vm_interkassa_form"]');

							$('.ik-payment-confirmation').click(function (e) {
								e.preventDefault();
								if(!curtrigger){
									alert('Вы не выбрали валюту');
									return;
								}else{
									form.submit();
								}
							});

							$('#radioBtn a').click(function () {
								curtrigger = true;
								var ik_cur = this.innerText;

								var ik_pw_via = $(this).attr('data-title');

								if($('input[name =  "ik_pw_via"]').length > 0){
									$('input[name =  "ik_pw_via"]').val(ik_pw_via);
								}else{
									form.append(
										$('<input>', {
											type: 'hidden',
											name: 'ik_pw_via',
											val: ik_pw_via
										}));
								}

								$.ajax({

									type: 'POST',
									dataType: 'json',
									url: 'index.php',
									data: {

										option: 'com_virtuemart',
										view: 'plugin',
										type: 'vmpayment',
										name: 'interkassa',
										form: form.serialize(),
									},
									success: function(data, status) {
										console.log('success');
										console.log(data);
										if($('input[name =  "ik_sign"]').length > 0){
											$('input[name =  "ik_sign"]').val(data.sign);
										}
									},
									error: function(data, status) {
										alert('Something wrong');
									}

								});
							});

							$('#radioBtn a').on('click', function () {
								var sel = $(this).data('title');
								var tog = $(this).data('toggle');
								$('#' + tog).prop('value', sel);
								$('a[data-toggle="' + tog + '"]').not('[data-title="' + sel + '"]').removeClass('active').addClass('notActive');
								$('a[data-toggle="' + tog + '"][data-title="' + sel + '"]').removeClass('notActive').addClass('active');
							})
						});

					})(jQuery);
</script>
