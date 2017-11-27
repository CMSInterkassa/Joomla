<?defined ('_JEXEC') or die();?>
<div class="ik_block">
	<img src="<?=$ik_dir?>assets/ik_logo.png" width="50%"><br>
	<button type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target=".ik_modal"><?=JText::_('VMPAYMENT_INTERKASSA_SELECT_PAYMENT')?></button>
	<div class="modal fade ik_modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog modal-lg" role="document">
	    <div class="modal-content" id="plans">
				<div class="modal-body">
		      <h1>1. <?=JText::_('VMPAYMENT_INTERKASSA_PTS_1')?><br>2. <?=JText::_('VMPAYMENT_INTERKASSA_PTS_2')?><br>3. <?=JText::_('VMPAYMENT_INTERKASSA_PTS_3')?></h1>
					<div class="row"><?foreach($payment_systems as $ps=>$info):?>
						<div class="col-sm-3 text-center payment_system">
							<div class="panel panel-warning panel-pricing">
								<div class="panel-heading">
									<img src="<?=$img_path?><?=$ps?>.png" alt="<?=$info['title']?>">
									<!--<h3><?=$info['title']?></h3>-->
								</div>
								<div class="form-group">
									<div class="input-group">
										<div id="radioBtn" class="btn-group radioBtn">
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
									<a class="btn btn-block btn-success ik-payment-confirmation" data-title="<?=$ps?>" href="#"><?=JText::_('VMPAYMENT_INTERKASSA_PAY_WITH')?>
										<br>
										<strong><?=$info['title']?></strong>
									</a>
								</div>
							</div>
						</div>
					<?endforeach?></div>
				</div>
	    </div>
	  </div>
	</div>
</div>
<script>ik_err_notslctcurr='<?=JText::_('VMPAYMENT_INTERKASSA_ERR_NOT_SLCT_CURR')?>'</script>
