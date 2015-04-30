<style>
.spacer {width:100%; height:25px;}
	.container {
		position: relative;
		max-width: 1050px;
	}
	.container h1 {text-align:center; font-size: 30px; margin:10px 0; float: left;}
    .container h2 {font-size:24px; margin:10px 0;}
    .container h3 {font-size:20px; margin:10px 0;}
    .container p {line-height:20px; margin-bottom:10px;}
    .container ul {margin-bottom:10px; margin-left:20px;}
    .container ul li {list-style-type:disc;}
	
	.about-message {
		font-size: 21px;
		line-height: 30px;
		float: left;
	}
	
	.plug-icon-shell {
		position: absolute;
		right: 0;
		top: 0;
	}
	.plug-icon-shell a {
		font-size: 14px;
		color: grey;
		text-decoration: none;
	}
	
	.video-wrapper {
		margin:0 auto; 
		width:640px;
		float: left;
	}
    .clear {clear:both;}
    
    .col-3 {
		float:left; 
		padding-right: 20px;
		width:29%;
	}
	
	#bupWelcomePageFindUsForm label {
		line-height: 24px;
		margin-left: 20px;
		font-size: 14px;
		display: block;
	}
</style>

<script type="text/javascript">
// <!--
jQuery(document).ready(function(){
	
	jQuery('#bupWelcomePageFindUsForm input[type=radio][name=where_find_us]').change(function(){
		jQuery('#bupFindUsUrlShell, #bupOtherWayTextShell').hide();
		switch(parseInt(jQuery(this).val())) {
			case 4 :
				jQuery('#bupFindUsUrlShell').show('slow');
				break;
			case 5 :
				jQuery('#bupOtherWayTextShell').show('slow');
				break;
		}
	});	
	
	/*jQuery('#bupWelcomePageFindUsForm').submit(function(event){

        event.preventDefault();

		jQuery(this).sendFormBup({
			msgElID: 'bupWelcomePageFindUsMsg'
		,	onSuccess: function(res) {
				if(!res.error) {
					if(res.data.redirect)
						toeRedirect(res.data.redirect);
				}
			}
		});
		return false;
	});*/
});
// -->
</script>

<div id="bup-first-start">
    <div class="container">
        <form id="bupWelcomePageFindUsForm">
            <h1>
                <?php _e('Welcome to', BUP_LANG_CODE)?>
                <?php echo BUP_S_WP_PLUGIN_NAME?>
                <?php _e('Version', BUP_LANG_CODE)?>
                <?php echo BUP_S_VERSION?>!
            </h1>
            <div class="clear"></div>
            <div class="about-message">
                <?php _e('This is first start up of the ' . BUP_S_WP_PLUGIN_NAME .' plugin.', BUP_LANG_CODE); ?><br />
                <?php _e('If you are newbie - check all features on that page, if you are guru - please correct us.', BUP_LANG_CODE); ?>
            </div>
            <div class="plug-icon-shell">
                <a target="_blank" href="https://supsystic.com/"><img src="<?php echo $this->getModule()->getModPath(). 'img/plug-icon.png'?>" /></a><br />
                <a target="_blank" href="https://supsystic.com/"><?php echo BUP_S_WP_PLUGIN_NAME?></a><br />
                <a target="_blank" href="https://supsystic.com/"><?php echo BUP_S_VERSION?></a><br />
            </div>
            <div class="clear"></div>
            <div class="spacer"></div>
    
            <h2><?php _e('Where did you find us?', BUP_LANG_CODE); ?></h2>
            <?php foreach($this->askOptions as $askId => $askOpt) { ?>
                <label><?php echo htmlBup::radiobutton('where_find_us', array('value' => $askId))?>&nbsp;<?php echo $askOpt['label']?></label>
                <?php if($askId == 4 /*Find on the web*/) { ?>
                    <label id="bupFindUsUrlShell" style="display: none;">Please, post url: <?php echo htmlBup::text('find_on_web_url')?></label>
                <?php } elseif($askId == 5 /*Other way*/) { ?>
                    <label style="display: none;" id="bupOtherWayTextShell"><?php echo htmlBup::textarea('other_way_desc')?></label>
                <?php }?>
            <?php }?>
    
            <div class="spacer"></div>
    

            <div class="clear"></div>
            
            <div class="about-message"><?php _e('What to do next? Check below section:', BUP_LANG_CODE); ?></div>
            <div class="clear"></div>
            
            <div class="col-3">
                <h3><?php _e('Boost us:', BUP_LANG_CODE); ?></h3>
                <p><?php _e('It\'s amazing when you boost development with your feedback and ratings. So we create special <a target="_blank" href="https://supsystic.com/">boost page</a> to help you to help us.', BUP_LANG_CODE); ?></p>
            </div>
    
            <div class="col-3">
                <h3><?php _e('Documentation:', BUP_LANG_CODE); ?></h3>
                <p><?php _e('Check <a target="_blank" href="https://supsystic.com/">documentation</a> and FAQ section. If you can\'t solve your problems - <a target="_blank" href="http://supsystic.com/contacts/">contact us</a>.', BUP_LANG_CODE); ?></p>
            </div>
    
            <div class="col-3">
                <h3><?php _e('Full Features List:', BUP_LANG_CODE); ?></h3>
                <p><?php _e('There are so many features, so we can\'t post it here. Like:', BUP_LANG_CODE); ?></p>
                <ul>
                  <li><?php _e('Files and Database backup', BUP_LANG_CODE); ?></li>
                  <li><?php _e('Backup to the Dropbox', BUP_LANG_CODE); ?></li>
                  <li><?php _e('FTP backup', BUP_LANG_CODE); ?></li>
                  <li><?php _e('Custom backup', BUP_LANG_CODE); ?></li>
                  <li><?php _e('Backup in archive', BUP_LANG_CODE); ?></li>
                  <li><?php _e('Restore backups anywhere', BUP_LANG_CODE); ?></li>
                </ul>
                <p><?php _e('So check full features list <a target="_blank" href="https://supsystic.com/">here</a>.', BUP_LANG_CODE); ?></p>
                
            </div>
            <div class="clear"></div>
            
			<?php echo htmlBup::hidden('pl', array('value' => BUP_CODE))?>
            <?php echo htmlBup::hidden('page', array('value' => 'promo_supsystic'))?>
			<?php echo htmlBup::hidden('action', array('value' => 'bupSendInfo'))?>
            <?php echo htmlBup::submit('gonext', array('value' => __('Thank for check info. Start using plugin.', BUP_LANG_CODE), 'attrs' => 'class="button button-primary button-hero"'))?>
            <?php echo htmlBup::hidden('original_page', array('value' => reqBup::getVar('page')))?>
            
           <!-- <a class="button button-primary button-hero bupSendInfo" href="javascript:void(0)">Thank for check info. Start using plugin.</a>-->
            
            <span id="bupWelcomePageFindUsMsg"></span>
        </form>
    </div>

</div>