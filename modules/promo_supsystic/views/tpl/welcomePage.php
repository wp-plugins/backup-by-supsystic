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
                This is first start up of the <?php echo BUP_S_WP_PLUGIN_NAME?> plugin.<br />
                If you are newbie - check all features on that page, if you are guru - please correct us.
            </div>
            <div class="plug-icon-shell">
                <a target="_blank" href="https://supsystic.com/"><img src="<?php echo $this->getModule()->getModPath(). 'img/plug-icon.png'?>" /></a><br />
                <a target="_blank" href="https://supsystic.com/"><?php echo BUP_S_WP_PLUGIN_NAME?></a><br />
                <a target="_blank" href="https://supsystic.com/"><?php echo BUP_S_VERSION?></a><br />
            </div>
            <div class="clear"></div>
            <div class="spacer"></div>
    
            <h2>Where did you find us?</h2>
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
            
            <div class="about-message">What to do next? Check below section:</div>
            <div class="clear"></div>
            
            <div class="col-3">
                <h3>Boost us:</h3>
                <p>It's amazing when you boost development with your feedback and ratings. So we create special <a target="_blank" href="https://supsystic.com/">boost page</a> to help you to help us.</p>
            </div>
    
            <div class="col-3">
                <h3>Documentation:</h3>
                <p>Check <a target="_blank" href="https://supsystic.com/">documentation</a> and FAQ section. If you can't solve your problems - <a target="_blank" href="http://supsystic.com/contacts/">contact us</a>.</p>
            </div>
    
            <div class="col-3">
                <h3>Full Features List:</h3>
                <p>There are so many features, so we can't post it here. Like:</p>
                <ul>
                  <li>Files and Database backup</li>
                  <li>Backup to the Dropbox</li>
                  <li>FTP backup</li>
                  <li>Custom backup</li>
                  <li>Backup in archive</li>
                  <li>Restore backups anywhere</li>
                </ul>
                <p>So check full features list <a target="_blank" href="https://supsystic.com/">here</a>.</p>
                
            </div>
            <div class="clear"></div>
            
			<?php echo htmlBup::hidden('pl', array('value' => BUP_CODE))?>
            <?php echo htmlBup::hidden('page', array('value' => 'promo_supsystic'))?>
			<?php echo htmlBup::hidden('action', array('value' => 'bupSendInfo'))?>
            <?php echo htmlBup::submit('gonext', array('value' => 'Thank for check info. Start using plugin.', 'attrs' => 'class="button button-primary button-hero"'))?>
            <?php echo htmlBup::hidden('original_page', array('value' => reqBup::getVar('page')))?>
            
           <!-- <a class="button button-primary button-hero bupSendInfo" href="javascript:void(0)">Thank for check info. Start using plugin.</a>-->
            
            <span id="bupWelcomePageFindUsMsg"></span>
        </form>
    </div>

</div>