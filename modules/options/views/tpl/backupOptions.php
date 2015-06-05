<div class="bupDest">
    <form id="bupMainFormOptions" method="post">
        <div id="bupOptions">

            <div class="toeBupDestination toeBupOptResponsive">
                <div class="bupMsgDest"></div>
                <h3><?php _e('Where to backup', BUP_LANG_CODE)?></h3>
                <hr/>

                <table class="form-table" style="width: 100% !important;">
                    <?php foreach($this->backupPlaces as $key => $bupPlace): ?>

                        <tr class="bupMargDest">
                            <td>
                                <label>
                                    <?php echo htmlBup::radiobutton('dest_opt', array('value'   => $key)); ?> <?php echo $bupPlace['title']?>
                                </label>

                                <?php if(!empty($bupPlace['content'])): ?>
                                    <div class="bupOptions bup-<?php echo $key ?>">
                                        <br/>
                                        <?php echo $bupPlace['content']?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <tr>


                    <?php endforeach; ?>
                </table>
            </div>

            <div class="toeBupOptResponsive" id="bupMainOption">
                <h3><?php _e('What to backup', BUP_LANG_CODE) ?></h3>
                <hr/>

                <table class="form-table" style="width: 100% !important;">
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Full backup', BUP_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Full backup', BUP_LANG_CODE) ?>"></i>
                        </td class="col-w-1perc">
                        <td class="col-w-1perc">
                            <?php echo htmlBup::checkbox('opt_values[full]', array('attrs'=>'class="bupCheckbox bupFull" id="bupFullBackup" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameBup::_()->getModule('options')->get('full') && $this->zipExtExist === true) ? 'checked' : '' )); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Wordpress Core', BUP_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('All folders and files backup in the root directory, where the WordPress is installed, except the /wp-content folder.', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlBup::checkbox('opt_values[wp_core]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameBup::_()->getModule('options')->get('wp_core') && $this->zipExtExist === true) ? 'checked' : '' )); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Plugins folder', BUP_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Plugins folder', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlBup::checkbox('opt_values[plugins]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameBup::_()->getModule('options')->get('plugins') && $this->zipExtExist === true) ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Themes folder', BUP_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Themes folder', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlBup::checkbox('opt_values[themes]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameBup::_()->getModule('options')->get('themes') && $this->zipExtExist === true) ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Uploads folder', BUP_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Uploads folder', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlBup::checkbox('opt_values[uploads]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameBup::_()->getModule('options')->get('uploads') && $this->zipExtExist === true) ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Any folder inside wp-content', BUP_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Any folder inside wp-content', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlBup::checkbox('opt_values[any_directories]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameBup::_()->getModule('options')->get('any_directories') && $this->zipExtExist === true) ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Database backup', BUP_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Database backup', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlBup::checkbox('opt_values[database]', array('attrs'=>'class="bupCheckbox bupFull bupDatabaseCheckbox"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('database') ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr class="bupSecretKeyDBRow" style="display: none">
                        <td>
                            <?php _e('Secret key for DB', BUP_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Secret key for encrypting DB data', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            &nbsp;
                        </td>
                    </tr>
                    <tr class="bupSecretKeyDBRow" style="display: none">
                        <td colspan="3">
                            <?php echo dispatcherBup::applyFilters('getInputForSecretKeyEncryptDb', '') ?>
                        </td>
                    </tr>
                </table>

            </div>

            <div class="toeBupOptResponsive">
                <h3><?php _e('Additional Settings', BUP_LANG_CODE) ?></h3>
                <hr/>

                <table class="form-table" style="width: 100% !important;">
                    <tr>
                        <td class="col-w-60perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Specify and enter files and folders names which must not be backed up.', BUP_LANG_CODE) ?>"></i><?php echo __('Exclude:', BUP_LANG_CODE); ?>
                            <br/><br/>
                            <?php echo htmlBup::text( 'opt_values[exclude]', array('attrs'=>'class="excludeInput" style="width: 100% !important" title="' . __(' If entering multiple files/directories, then separate them with commas.', BUP_LANG_CODE) . '"', 'value' => frameBup::_()->getModule('options')->get('exclude')) ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Email notification', BUP_LANG_CODE) ?>"></i><?php echo __('Email notification:', BUP_LANG_CODE); ?>

                            <div style="float: right">
                                <?php echo htmlBup::checkbox('__toggleEmailCheckbox', array('attrs'=>'class="bupCheckboxNotUnCheck emailCh" style="float: right"', 'checked' => frameBup::_()->getModule('options')->get('email_ch') == 1 ? 'checked' : '')); ?>
                            </div>
                            <input type="hidden" value="<?php echo frameBup::_()->getModule('options')->get('email_ch'); ?>" name="opt_values[email_ch]">

                            <span class="emailAddress" <?php echo frameBup::_()->getModule('options')->get('email_ch') ? '' : 'style="display:none"';?> >
                                <br/><br/>
                                <?php echo htmlBup::text( 'opt_values[email]', array('attrs'=>'class="excludeInput" placeholder="example@mail.com" title="" style="width: 100% !important"', 'value' => frameBup::_()->getModule('options')->get('email')) );?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Specify the path where the data is to be backed up. It \'Use relative path\' ckeckbox has been set up, the path will be set against in the root directory, where the WordPress is installed. If \'Use relative path\' checkbox has been of, the full path to the disk root should be specified.', BUP_LANG_CODE) ?>"></i>
                            <?php _e('Warehouse:', BUP_LANG_CODE); ?>
                            <br/><br/>

                            <?php
                            echo htmlBup::text(
                                'opt_values[warehouse]', array(
                                    'attrs' => 'class="input-regular" id="warehouseInput" style="width: 100% !important"',
                                    'value' => str_replace(
                                        '\\',
                                        '/',
                                        frameBup::_()->getModule('options')->get('warehouse')
                                    ),
                                )
                            );
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('If the checkbox has been set up, then the backup path must be specified in the Warehouse field against the root directory, where the WordPress is installed. if the checkbox has been off, then the backup path must be specified in the Warehouse field against the disk root."', BUP_LANG_CODE) ?>"></i>
                            <?php _e('Use relative path', BUP_LANG_CODE); ?>
                            <div style="float: right">
                                <?php
                                echo htmlBup::checkbox(
                                    '__toggleWarehouseAbs',
                                    array(
                                        'attrs'   => 'class="bupCheckbox wareabs" id="warehouseToggle"',
                                        'checked' => frameBup::_()->getModule('options')->get('warehouse_abs') == 1 ? 'checked' : '',
                                    )
                                );
                                ?>
                            <div
                            <input type="hidden" value="<?php echo frameBup::_()->getModule('options')->get('warehouse_abs'); ?>" name="opt_values[warehouse_abs]">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Path to directory, where will be stored backup files.', BUP_LANG_CODE) ?>"></i>
                            <span id="abspath"><?php echo ABSPATH; ?></span><wbr><span id="realpath"></span>
                        </td>
                    </tr>
                </table>
            </div>

            <?php echo htmlBup::hidden('reqType', array('value' => 'ajax'))?>
            <?php echo htmlBup::hidden('page', array('value' => 'options'))?>
            <?php echo htmlBup::hidden('action', array('value' => 'saveMainFromDestGroup'))?>
            <?php echo htmlBup::hidden('backupDest', array('value' => $this->backupDest))?>
            <div id="bupMainFormOptionsMsg"></div>
        </div>
    </form>
</div>