
<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#InterkassaModal">Выбрать платежную систему</button>

<div id="InterkassaModal" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg">
		<div class="modal-content" id="plans">
			<div class="container">
				<h1>
					1.Выберите удобный способ оплаты<br>
					2.Укажите валюту<br>
					3.Нажмите "Оплатить"
				</h1>
				<div class="row">

					<?php foreach ($payment_systems as $ps => $info ) { ?>

						<div class="col-sm-3 text-center payment_system">
							<div class="panel panel-warning panel-pricing">
								<div class="panel-heading">
									<img src="<?php echo $img_path; ?><?php echo $ps; ?>.png" alt="<?php echo $info['title'] ; ?>">
									<h3><?php echo $info['title'] ; ?></h3>
								</div>
								<div class="form-group">
									<div class="input-group">
										<div id="radioBtn" class="btn-group">
											<?php foreach ($info['currency'] as $currency => $currencyAlias) { ?>
												<?php if ($currency == $shop_cur) { ?>
													<a class="btn btn-primary btn-sm active" data-toggle="fun"
													data-title="<?php echo $currencyAlias; ?>"><?php echo $currency; ?></a>
													<?php } else { ?>
														<a class="btn btn-primary btn-sm notActive" data-toggle="fun"
														data-title="<?php echo $currencyAlias; ?>"><?php echo $currency; ?></a>
														<?php } ?>
														<?php } ?>
													</div>
													<input type="hidden" name="fun" id="fun">
												</div>
											</div>
											<div class="panel-footer">
												<a class="btn btn-block btn-success ik-payment-confirmation" data-title="<?php echo $ps ; ?>"
													href="#">Оплатить с
													<br>
													<strong><?php echo $info['title'] ; ?></strong>
												</a>
											</div>
										</div>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>

					</div>
				</div>



				<script type="text/javascript">

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
				<style>
					#InterkassaModal .input-group,#InterkassaModal h1{
						text-align: center;
					}
					#InterkassaModal{
						overflow-y: scroll;
						max-width: 940px;
						margin-left: -25%;
					}

					.payment_system h3, .payment_system img {
						display: inline-block;
						width: 100%;
						font-size: 18px;
					}
					.ik-payment-confirmation{
						width: 100%
					}
					.payment_system .panel-heading {
						text-align: center;
					}
					.payment_system .btn-primary {
						background-image: none;
					}
					.payment_system .input-group{
						display: flex;
						justify-content: center;
						flex-wrap: wrap;
					}

					.payment_system .btn-primary, .payment_system .btn-secondary, .payment_system .btn-tertiary {
						padding: 3px;
						border-radius: 0;
					}

					.panel-pricing {
						-moz-transition: all .3s ease;
						-o-transition: all .3s ease;
						-webkit-transition: all .3s ease;
					}

					.panel-pricing:hover {
						box-shadow: 0px 0px 30px rgba(0, 0, 0, 0.2);
					}

					.panel-pricing .panel-heading {
						padding: 20px 10px;
					}

					.panel-pricing .panel-heading .fa {
						margin-top: 10px;
						font-size: 58px;
					}

					.panel-pricing .list-group-item {
						color: #777777;
						border-bottom: 1px solid rgba(250, 250, 250, 0.5);
					}

					.panel-pricing .list-group-item:last-child {
						border-bottom-right-radius: 0px;
						border-bottom-left-radius: 0px;
					}

					.panel-pricing .list-group-item:first-child {
						border-top-right-radius: 0px;
						border-top-left-radius: 0px;
					}

					.panel-pricing .panel-body {
						background-color: #f0f0f0;
						font-size: 40px;
						color: #777777;
						padding: 20px;
						margin: 0px;
					}

					#radioBtn .notActive {
						color: #3276b1;
						background-color: #fff;
					}
					#radioBtn .notActive:hover {
						cursor: pointer;
						color: #2894b1;
						background-color: #fff;
					}

					div.modal-dialog.modal-lg div#plans.modal-content div.container .row {
						display: flex;
						flex-wrap: wrap;
						justify-content: center;
					}

					.modal {
						display: none;
						overflow: hidden;
						position: fixed;
						top: 0;
						right: 0;
						bottom: 0;
						left: 0;
						z-index: 1050;
						-webkit-overflow-scrolling: touch;
						outline: 0;
					}
					.modal.fade .modal-dialog {
						-webkit-transform: translate(0, -25%);
						-ms-transform: translate(0, -25%);
						-o-transform: translate(0, -25%);
						transform: translate(0, -25%);
						-webkit-transition: -webkit-transform 0.3s ease-out;
						-o-transition: -o-transform 0.3s ease-out;
						transition: transform 0.3s ease-out;
					}
					.modal.in .modal-dialog {
						-webkit-transform: translate(0, 0);
						-ms-transform: translate(0, 0);
						-o-transform: translate(0, 0);
						transform: translate(0, 0);
					}
					.modal-open .modal {
						overflow-x: hidden;
						overflow-y: auto;
					}
					.modal-dialog {
						/*padding: 15px;*/
						position: relative;
						width: auto;
						/*margin: 10px;*/
					}
					.modal-content {
						position: relative;
						background-color: #ffffff;
						border: 1px solid #999999;
						border: 1px solid rgba(0, 0, 0, 0.2);
						border-radius: 6px;
						-webkit-box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
						box-shadow: 0 3px 9px rgba(0, 0, 0, 0.5);
						-webkit-background-clip: padding-box;
						background-clip: padding-box;
						outline: 0;
					}
					.modal-header .close {
						margin-top: -2px;
					}
					.modal-footer .btn + .btn {
						margin-left: 5px;
						margin-bottom: 0;
					}
					.modal-footer .btn-group .btn + .btn {
						margin-left: -1px;
					}
					.modal-footer .btn-block + .btn-block {
						margin-left: 0;
					}
					@media (min-width: 768px) {
						.modal-dialog {
							width: 600px;
							/*margin: 30px auto;*/
						}
						.modal-content {
							-webkit-box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
							box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
						}
					}
					@media (min-width: 992px) {
						.modal-lg {
							width: 100%
						}
					}
					.col-sm-3, .col-sm-4{
						position: relative;
						width: 100%;
						min-height: 1px;
						padding-right: 15px;
						padding-left: 15px;
					}

					@media (min-width: 576px) {
						.col-sm-3, .col-sm-4{
							padding-right: 15px;
							padding-left: 15px;
						}
					}

					@media (min-width: 768px) {
						.col-sm-3, .col-sm-4{
							padding-right: 15px;
							padding-left: 15px;
						}
					}

					@media (min-width: 992px) {
						.col-sm-3, .col-sm-4{
							padding-right: 15px;
							padding-left: 15px;
						}
					}

					@media (min-width: 1200px) {
						.col-sm-3, .col-sm-4{
							padding-right: 15px;
							padding-left: 15px;
						}
					}




					@media (min-width: 576px) {
						.col-sm {
							-webkit-flex-basis: 0;
							-ms-flex-preferred-size: 0;
							flex-basis: 0;
							-webkit-box-flex: 1;
							-webkit-flex-grow: 1;
							-ms-flex-positive: 1;
							flex-grow: 1;
							max-width: 100%;
						}

						.col-sm-2 {
							-webkit-box-flex: 0;
							-webkit-flex: 0 0 16.666667%;
							-ms-flex: 0 0 16.666667%;
							flex: 0 0 16.666667%;
							max-width: 16.666667%;
						}
						.col-sm-3 {
							-webkit-box-flex: 0;
							-webkit-flex: 0 0 25%;
							-ms-flex: 0 0 25%;
							flex: 0 0 25%;
							max-width: 25%;
						}
						.col-sm-4 {
							-webkit-box-flex: 0;
							-webkit-flex: 0 0 33.333333%;
							-ms-flex: 0 0 33.333333%;
							flex: 0 0 33.333333%;
							max-width: 33.333333%;
						}


					}
					@media (min-width: 992px) {
						.col-sm-3 {
							-webkit-box-flex: 0;
							-webkit-flex: 0 0 25%;
							-ms-flex: 0 0 25%;
							flex: 0 0 25%;
							max-width: 20%;
						}
						.col-sm-4 {
							-webkit-box-flex: 0;
							-webkit-flex: 0 0 33.333333%;
							-ms-flex: 0 0 33.333333%;
							flex: 0 0 33.333333%;
							max-width: 33.333333%;
						}
					}
					@media (min-width: 1200px) {
						.col-sm-3 {
							-webkit-box-flex: 0;
							-webkit-flex: 0 0 25%;
							-ms-flex: 0 0 25%;
							flex: 0 0 25%;
							max-width: 20%;
						}
						.col-sm-4 {
							-webkit-box-flex: 0;
							-webkit-flex: 0 0 33.333333%;
							-ms-flex: 0 0 33.333333%;
							flex: 0 0 33.333333%;
							max-width: 33.333333%;
						}
					}


					.btn {
						display: inline-block;
						font-weight: normal;
						line-height: 1.25;
						text-align: center;
						white-space: nowrap;
						vertical-align: middle;
						-webkit-user-select: none;
						-moz-user-select: none;
						-ms-user-select: none;
						user-select: none;
						border: 1px solid transparent;
						padding: 0.5rem 1rem;
						font-size: 1rem;
						border-radius: 0.25rem;
						-webkit-transition: all 0.2s ease-in-out;
						-o-transition: all 0.2s ease-in-out;
						transition: all 0.2s ease-in-out;
					}

					.btn:focus, .btn:hover {
						text-decoration: none;
					}

					.btn:focus, .btn.focus {
						outline: 0;
						-webkit-box-shadow: 0 0 0 2px rgba(2, 117, 216, 0.25);
						box-shadow: 0 0 0 2px rgba(2, 117, 216, 0.25);
					}

					.btn.disabled, .btn:disabled {
						cursor: not-allowed;
						opacity: .65;
					}

					.btn:active, .btn.active {
						background-image: none;
					}

					a.btn.disabled,
					fieldset[disabled] a.btn {
						pointer-events: none;
					}

					.btn-primary {
						color: #fff;
						background-color: #0275d8;
						border-color: #0275d8;
					}

					.btn-primary:hover {
						color: #fff;
						background-color: #025aa5;
						border-color: #01549b;
					}

					.btn-primary:focus, .btn-primary.focus {
						-webkit-box-shadow: 0 0 0 2px rgba(2, 117, 216, 0.5);
						box-shadow: 0 0 0 2px rgba(2, 117, 216, 0.5);
					}

					.btn-primary.disabled, .btn-primary:disabled {
						background-color: #0275d8;
						border-color: #0275d8;
					}

					.btn-primary:active, .btn-primary.active,
					.show > .btn-primary.dropdown-toggle {
						color: #fff;
						background-color: #025aa5;
						background-image: none;
						border-color: #01549b;
					}




					.btn-success {
						color: #fff;
						background-color: #5cb85c;
						border-color: #5cb85c;
					}

					.btn-success:hover {
						color: #fff;
						background-color: #449d44;
						border-color: #419641;
					}

					.btn-success:focus, .btn-success.focus {
						-webkit-box-shadow: 0 0 0 2px rgba(92, 184, 92, 0.5);
						box-shadow: 0 0 0 2px rgba(92, 184, 92, 0.5);
					}

					.btn-success.disabled, .btn-success:disabled {
						background-color: #5cb85c;
						border-color: #5cb85c;
					}

					.btn-success:active, .btn-success.active,
					.show > .btn-success.dropdown-toggle {
						color: #fff;
						background-color: #449d44;
						background-image: none;
						border-color: #419641;
					}


					.btn-lg, .btn-group-lg > .btn {
						padding: 0.75rem 1.5rem;
						font-size: 1.25rem;
						border-radius: 0.3rem;
					}

					.container {
						position: relative;
						margin-left: auto;
						margin-right: auto;
						padding-right: 15px;
						padding-left: 15px;
					}
					.row {
						display: -webkit-box;
						display: -webkit-flex;
						display: -ms-flexbox;
						display: flex;
						-webkit-flex-wrap: wrap;
						-ms-flex-wrap: wrap;
						flex-wrap: wrap;
						margin-right: -15px;
						margin-left: -15px;
					}



				</style>