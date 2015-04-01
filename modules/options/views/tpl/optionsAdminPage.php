<div class="wrap">
    <div class="supsystic-plugin">
        <section class="supsystic-content">
            <nav class="supsystic-navigation supsystic-sticky">
                <ul>
                    <?php foreach($this->tabsData as $tabKey => $tab) {
                            if(!empty($tab['faIcon'])){?>
                                <li class="<?php echo ($this->activeTabForCssClass == $tabKey ? 'active' : '')?>">
                                    <a href="<?php echo uriBup::_(array('baseUrl' => get_admin_url(0, 'admin.php?page='. $this->page. '&tab='. $tabKey))); ?>">
                                        <i class="fa <?php echo $tab['faIcon']?>"></i>
                                        <?php echo $tab['title']?>
                                    </a>
                                </li>
                      <?php }?>
                    <?php }?>
                </ul>
            </nav>
            <div class="supsystic-container supsystic-<?php echo $this->activeTab?>">
                <?php echo $this->content?>
                <div class="clear"></div>
            </div>
        </section>
    </div>
</div>
<div id="cspAdminTemplatesSelection"><?php echo !empty($this->presetTemplatesHtml) ? $this->presetTemplatesHtml : ''?></div>
