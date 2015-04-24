<div class="bupDest">
    <form id="bupMainFormOptions" method="post">
        <div id="bupOptions">
            <div class="bupMsgDest"></div>
            <?php foreach($this->backupPlaces as $key => $bupPlace): ?>

                <div class="bupMargDest">
                    <label>
                        <?php echo htmlBup::radiobutton('dest_opt', array('value'   => $key)); ?> <?php echo $bupPlace['title']?>
                    </label>
                </div>

                <?php if(!empty($bupPlace['content'])): ?>

                    <div class="bupOptions bup-<?php echo $key ?>">
                        <?php echo $bupPlace['content']?>
                    </div>

                <?php endif; ?>

            <?php endforeach; ?>

            <div id="bupMainOption" style="display: none;">
                <hr/>
                <h3><?php _e('Backup Presets:', BUP_LANG_CODE) ?></h3>
                <table class="form-table">
                    <tr>
                        <th class="col-w-30perc"><?php _e('Full backup', BUP_LANG_CODE) ?></th>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Full backup', BUP_LANG_CODE) ?>"></i>
                        </td class="col-w-1perc">
                        <td class="col-w-1perc">
                            <?php echo htmlBup::checkbox('opt_values[full]', array('attrs'=>'class="bupCheckbox bupFull" id="bupFullBackup"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('full') ? 'checked' : '' )); ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-w-30perc"><?php _e('Wordpress Core', BUP_LANG_CODE) ?></th>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('All folders and files backup in the root directory, where the WordPress is installed, except the /wp-content folder.', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc"><?php echo htmlBup::checkbox('opt_values[wp_core]', array('attrs'=>'class="bupCheckbox bupFull"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('wp_core') ? 'checked' : '' )); ?></td>
                    </tr>
                    <tr>
                        <th class="col-w-30perc"><?php _e('Plugins folder', BUP_LANG_CODE) ?></th>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Plugins folder', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc"><?php echo htmlBup::checkbox('opt_values[plugins]', array('attrs'=>'class="bupCheckbox bupFull"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('plugins') ? 'checked' : '')); ?></td>
                    </tr>
                    <tr>
                        <th class="col-w-30perc"><?php _e('Themes folder', BUP_LANG_CODE) ?></th>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Themes folder', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc"><?php echo htmlBup::checkbox('opt_values[themes]', array('attrs'=>'class="bupCheckbox bupFull"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('themes') ? 'checked' : '')); ?></td>
                    </tr>
                    <tr>
                        <th class="col-w-30perc"><?php _e('Uploads folder', BUP_LANG_CODE) ?></th>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Uploads folder', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc"><?php echo htmlBup::checkbox('opt_values[uploads]', array('attrs'=>'class="bupCheckbox bupFull"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('uploads') ? 'checked' : '')); ?></td>
                    </tr>
                    <tr>
                        <th class="col-w-30perc"><?php _e('Any folder inside wp-content', BUP_LANG_CODE) ?></th>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Any folder inside wp-content', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc"><?php echo htmlBup::checkbox('opt_values[any_directories]', array('attrs'=>'class="bupCheckbox bupFull"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('any_directories') ? 'checked' : '')); ?></td>
                    </tr>
                </table>

                <hr/>

                <table style="width: 100%">
                    <tr>
                        <td width="200"><i class="fa fa-question supsystic-tooltip" title="<?php _e('Database backup', BUP_LANG_CODE) ?>"></i><?php _e('Database backup', BUP_LANG_CODE) ?></td>
                        <td><?php echo htmlBup::checkbox('opt_values[database]', array('attrs'=>'class="bupCheckbox bupFull bupDatabaseCheckbox"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('database') ? 'checked' : '')); ?></td>
                    </tr>
                    <tr class="bupSecretKeyDBRow" style="display: none">
                        <td width="200"><i class="fa fa-question supsystic-tooltip" title="<?php _e('Secret key for encrypting DB data', BUP_LANG_CODE) ?>"></i><?php _e('Secret key for DB', BUP_LANG_CODE) ?></td>
                        <?php echo dispatcherBup::applyFilters('getInputForSecretKeyEncryptDb', '') ?>
                    </tr>
                </table>

                <hr/>

                <!-- <div class="excludeOpt"> -->
                <table style="width: 100%">
                    <tr>
                        <td width="200"><i class="fa fa-question supsystic-tooltip" title="<?php _e('Specify and enter files and folders names which must not be backed up.', BUP_LANG_CODE) ?>"></i><?php echo __('Exclude:', BUP_LANG_CODE); ?></td>
                        <td><?php echo htmlBup::text( 'opt_values[exclude]', array('attrs'=>'class="excludeInput" title="' . __(' If entering multiple files/directories, then separate them with commas.', BUP_LANG_CODE) . '"', 'value' => frameBup::_()->getModule('options')->get('exclude')) ); ?></td>
                    </tr>
                </table>
                <!-- </div> -->

                <hr/>

                <!-- <div class="emailOpt"> -->
                <table style="min-height: 45px;">
                    <tr>
                        <td width="200"><i class="fa fa-question supsystic-tooltip" title="<?php _e('Email notification', BUP_LANG_CODE) ?>"></i><?php echo __('Email notification:', BUP_LANG_CODE); ?></td>
                        <td>
                            <?php echo htmlBup::checkbox('__toggleEmailCheckbox', array('attrs'=>'class="bupCheckboxNotUnCheck emailCh"', 'checked' => frameBup::_()->getModule('options')->get('email_ch') == 1 ? 'checked' : '')); ?> <span  class="emailAddress" <?php echo frameBup::_()->getModule('options')->get('email_ch') ? '' : 'style="display:none"' ?>><?php echo htmlBup::text( 'opt_values[email]', array('attrs'=>'class="excludeInput" placeholder="example@mail.com" title=""', 'value' => frameBup::_()->getModule('options')->get('email')) );  ?></span>
                        </td>
                        <input type="hidden" value="<?php echo frameBup::_()->getModule('options')->get('email_ch'); ?>" name="opt_values[email_ch]">
                    </tr>
                </table>

                <hr/>

                <table style="width:100%;">
                    <tr>
                        <td  width="200">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Specify the path where the data is to be backed up. It \'Use relative path\' ckeckbox has been set up, the path will be set against in the root directory, where the WordPress is installed. If \'Use relative path\' checkbox has been of, the full path to the disk root should be specified.', BUP_LANG_CODE) ?>"></i><?php _e('Warehouse:', BUP_LANG_CODE); ?>
                        </td>
                        <td>
                            <?php
                            echo htmlBup::text(
                                'opt_values[warehouse]', array(
                                    'attrs' => 'class="input-regular" id="warehouseInput"',
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
                </table>

                <hr/>

                <table style="width:100%;">
                    <tr>
                        <td  width="200">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('If the checkbox has been set up, then the backup path must be specified in the Warehouse field against the root directory, where the WordPress is installed. if the checkbox has been off, then the backup path must be specified in the Warehouse field against the disk root."', BUP_LANG_CODE) ?>></i><?php _e('Use relative path:', BUP_LANG_CODE); ?>
                        </td>
                        <td>
                            <?php
                            echo htmlBup::checkbox(
                                '__toggleWarehouseAbs',
                                array(
                                    'attrs'   => 'class="bupCheckbox wareabs" id="warehouseToggle"',
                                    'checked' => frameBup::_()->getModule('options')->get('warehouse_abs') == 1 ? 'checked' : '',
                                )
                            );
                            ?>  <span id="abspath"><?php echo ABSPATH; ?></span><span id="realpath"></span>
                        </td>
                        <input type="hidden" value="<?php echo frameBup::_()->getModule('options')->get('warehouse_abs'); ?>" name="opt_values[warehouse_abs]">
                    </tr>
                </table>

                <hr/>
            </div>

            <?php echo htmlBup::hidden('reqType', array('value' => 'ajax'))?>
            <?php echo htmlBup::hidden('page', array('value' => 'options'))?>
            <?php echo htmlBup::hidden('action', array('value' => 'saveMainFromDestGroup'))?>
            <?php echo htmlBup::hidden('backupDest', array('value' => $this->backupDest))?>
            <div id="bupMainFormOptionsMsg"></div>
        </div>
    </form>
</div>