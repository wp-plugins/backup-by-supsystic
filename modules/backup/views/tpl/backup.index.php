<section>
    <div class="supsystic-item supsystic-panel">
        <div id="bupBackupWrapper">
            <div id="bupAdminStorageTable" style="width: 50%; display: inline-block;">

                <?php
                if(!empty($backups)):
                    foreach ($backups as $id => $type):
                    $backupType = key($type);
                    $backupStartDateTime  = (!empty($logs[$id]['content'])) ? __(' / Start:', BUP_LANG_CODE) . '<b>' . $model->getBackupStartTimeFromLog($logs[$id]['content']) . '</b>' : '' ;
                    $backupFinishDateTime = (!empty($logs[$id]['content'])) ? __(' / Finish:', BUP_LANG_CODE) . '<b>' . $model->getBackupFinishTimeFromLog($logs[$id]['content']) . '</b>' : '';
                    $backupTimeInfo = $backupStartDateTime . ' ' . $backupFinishDateTime;
                    if($backupType == 'ftp'):
                        $backup = $type['ftp'];
                        $sqlExist = !empty($backup['sql']) ? 'data-sql="sql"' : false; // this attribute used in JS(migration module), if it exist - show inputs for find/replace site url in DB dump
                        $encrypted = !empty($backup['sql']['encrypted']) ? $backup['sql']['encrypted'] : ''; // this class used in JS(migration module), if it exist - show input for decrypt DB dump for find/replace site url
                    ?>
                    <!--  FTP files rendering start    -->
                    <div class="backupBlock">
                        <p>
                            <?php _e('Backup to <b>FTP</b> / ID', BUP_LANG_CODE)?> <b><?php echo $id; ?></b><?php echo $backupTimeInfo ?>
                        </p>
                        <div align="left" id="MSG_EL_ID_<?php echo $id; ?>"></div>

                        <div id="bupControl-<?php echo $id?>">
                            <!-- Hides "Send to" button if the PRO version isn't activated -->
                            <?php if (null !== frameBup::_()->getModule('license') && false): ?>
                            <div>
                                <a href="#" onclick="return false;" class="bupSendTo" style="font-size:.9em">Send to &rarr;</a>
                            </div>
                            <?php endif; ?>

                            <!-- Backup sendTo providers  loop start -->
                            <div class="bupSendToProviders" style="display: none; font-size:.9em;">
                                <?php foreach ($providers as $provider): ?>
                                <a
                                    href="#"
                                    id="<?php echo $id; ?>"
                                    class="bupSendToBtn"
                                    onclick="return false;"
                                    data-provider="<?php echo $provider['provider']; ?>"
                                    data-action="<?php echo $provider['action']; ?>"
                                    title="Send backup to <?php echo $provider['label']; ?>"
                                    <?php foreach ($backup as $data): $backupfiles[] = $data['name']; endforeach;?>
                                    data-files="<?php echo implode(',', $backupfiles); ?><?php unset($backupfiles); ?>"
                                ><?php echo $provider['label']; ?></a>&nbsp;
                                <?php endforeach; ?>
                            </div>
                            <!-- Backup sendTo providers  loop end -->

                            <table>
                                <tbody>
                                    <?php foreach ($backup as $type => $data): ?>

                                    <tr class="tabStr" id="<?php echo $data['name']; ?>">
                                        <td>
                                            <?php echo ($type == 'zip' ? __('Filesystem', BUP_LANG_CODE) : __('Database', BUP_LANG_CODE)); ?>
                                        </td>
                                        <td>
                                            <!-- restoreButton -->
                                            <button class="button button-primary button-small bupRestore" data-id="<?php echo $id; ?>" data-filename="<?php echo $data['name']; ?>" >
                                                <?php _e('Restore', BUP_LANG_CODE); ?>
                                            </button>
                                            <!-- /restoreButton -->

                                            <!-- deleteButton -->
                                            <button class="button button-primary button-small bupDelete" data-id="<?php echo $id; ?>" data-filename="<?php echo $data['name']; ?>">
                                                <?php _e('Delete', BUP_LANG_CODE); ?>
                                            </button>
                                            <!-- /deleteButton -->

                                            <?php if($type == 'sql'){?>
                                                <!-- downloadButton -->
                                                <button class="button button-primary button-small bupDownload" data-filename="<?php echo $data['name']; ?>">
                                                    <?php _e('Download', BUP_LANG_CODE); ?>
                                                </button>
                                                <!-- /downloadButton -->
                                            <?php }?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if(!empty($logs[$id]['content'])):?>
                                <span class="bupShowLogHistory" data-log="<?php echo nl2br($logs[$id]['content'])?>"><?php _e('Show Backup Log', BUP_LANG_CODE) ?></span>
                            <?php else: ?>
                                <b><?php _e('Log is clear.', BUP_LANG_CODE) ?></b>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr/>
                    <!--  FTP files rendering end    -->


                    <!--  GoogleDrive files rendering start    -->
                    <?php
                    elseif($backupType == 'gdrive'):
                        $files = $type['gdrive'];
                        $encrypted = !empty($files['sql']['backupInfo']['encrypted']) ? $files['sql']['backupInfo']['encrypted'] : '';
                        ?>
                        <div class="backupBlock">
                            <p>
                            <?php _e('Backup to <b>GoogleDrive</b> / ID', BUP_LANG_CODE) ?> <b><?php echo $id; ?></b><?php echo $backupTimeInfo ?>
                            </p>
                            <div id="bupGDriveAlerts-<?php echo $id; ?>"></div>
                            <div id="bupControl-<?php echo $id?>">
                                <?php if(isset($files['sql']['labels']['trashed']) && ($files['sql']['labels']['trashed'] === false) || isset($files['zip']['labels']['trashed']) && ($files['zip']['labels']['trashed'] === false)): ?>
                                    <table>
                                    <tbody>
                                    <?php foreach($files as $type=>$file): ?>
                                        <tr id="<?php echo $type.'-'.$id; ?>">
                                            <td>
                                                <?php echo ($type == 'zip') ? __('Filesystem', BUP_LANG_CODE) : __('Database', BUP_LANG_CODE)?>
                                            </td>
                                            <td>
                                                <button data-row-id="<?php echo $id; ?>"
                                                        data-file-url="<?php echo !empty($file['downloadUrl']) ? $file['downloadUrl'] : ''; ?>"
                                                        data-file-name="<?php echo $file['title']; ?>"
                                                        data-file-type="<?php echo $type; ?>"
                                                        class="button button-primary button-small bupGDriveRestore"
                                                    >
                                                    <?php _e('Restore', BUP_LANG_CODE); ?>
                                                </button>
                                                <button data-row-id="<?php echo $id; ?>"
                                                        data-file-id="<?php echo $file['id']; ?>"
                                                        data-filename="<?php echo $file['title']; ?>"
                                                        data-file-type="<?php echo $type; ?>"
                                                        class="button button-primary button-small bupGDriveDelete"
                                                    >
                                                    <?php _e('Delete', BUP_LANG_CODE); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach;?>
                                    </tbody>
                                    </table>

                                <?php endif; ?>
                                <?php if(!empty($logs[$id]['content'])):?>
                                    <span class="bupShowLogHistory" data-log="<?php echo nl2br($logs[$id]['content'])?>"><?php _e('Show Backup Log', BUP_LANG_CODE) ?></span>
                                <?php else: ?>
                                    <b><?php _e('Log is clear.', BUP_LANG_CODE) ?></b>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr/>
                    <!--  GoogleDrive files rendering end    -->

                    <!--  OneDrive files rendering start    -->
                    <?php
                    elseif($backupType == 'onedrive'):
                        $files = $type['onedrive'];
                        $encrypted = !empty($files->sql->backupInfo['encrypted']) ? $files->sql->backupInfo['encrypted'] : '';
                        ?>
                        <div class="backupBlock">
                            <p>
                                <?php _e('Backup to <b>OneDrive</b> / ID', BUP_LANG_CODE) ?><b><?php echo $id; ?></b><?php echo $backupTimeInfo ?>
                            </p>
                            <div id="bupOnedriveAlerts-<?php echo $id; ?>"></div>
                            <div id="bupControl-<?php echo $id?>">
                                <table>
                                    <tbody>
                                        <?php foreach($files as $type=>$file):?>
                                            <?php if ($file->type === 'file' || $file->type === 'folder'): ?>
                                                <tr id="backup-<?php echo $file->id; ?>" data-id="<?php echo $id; ?>" data-filename="<?php echo $file->name; ?>">
                                                    <td>
                                                        <?php echo ($type == 'zip')? __('Filesystem', BUP_LANG_CODE) : __('Database', BUP_LANG_CODE)?>
                                                    </td>
                                                    <td>
                                                        <button data-row-id="<?php echo $id; ?>"
                                                                data-file-id="<?php echo $file->id; ?>"
                                                                data-file-name="<?php echo $file->name; ?>"
                                                                class="button button-primary button-small bupRestoreOnedrive"
                                                            >
                                                            <?php _e('Restore', BUP_LANG_CODE); ?>
                                                        </button>
                                                        <button
                                                            data-file-id="<?php echo $file->id; ?>"
                                                            class="button button-primary button-small onedriveDelete"
                                                            >
                                                            <?php _e('Delete', BUP_LANG_CODE); ?>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php if(!empty($logs[$id]['content'])):?>
                                    <span class="bupShowLogHistory" data-log="<?php echo nl2br($logs[$id]['content'])?>"><?php _e('Show Backup Log', BUP_LANG_CODE) ?></span>
                                <?php else: ?>
                                    <b><?php _e('Log is clear.', BUP_LANG_CODE) ?></b>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr/>
                <!--  OneDrive files rendering end    -->

                <!--  Amazon files rendering start    -->
                    <?php
                    elseif($backupType == 'amazon'):
                        $files = $type['amazon'];
                        $encrypted = !empty($files['sql']['backupInfo']['encrypted']) ? $files['sql']['backupInfo']['encrypted'] : '';
                        ?>
                        <div class="backupBlock">
                            <p>
                                <?php _e('Backup to <b>Amazon S3</b> / ID', BUP_LANG_CODE)?> <b><?php echo $id; ?></b><?php echo $backupTimeInfo ?>
                            </p>
                            <div id="bupAmazonAlerts-<?php echo $id;?>"></div>
                            <div id="bupControl-<?php echo $id?>">
                                <table>
                                    <tbody>
                                        <?php foreach($files as $type => $file):?>
                                            <tr id="<?php echo $id;?>">
                                                <td>
                                                    <?php echo ($type == 'zip')? __('Filesystem', BUP_LANG_CODE) : __('Database', BUP_LANG_CODE)?>
                                                </td>
                                                <td>
                                                    <button class="button button-primary button-small bupAmazonS3Restore" data-row-id="<?php echo $id; ?>" data-filename="<?php echo $file['file']; ?>">
                                                        <?php _e('Restore', BUP_LANG_CODE); ?>
                                                    </button>
                                                    <button class="button button-primary button-small bupAmazonS3Delete" data-row-id="<?php echo $id; ?>" data-filename="<?php echo $file['file']; ?>">
                                                        <?php _e('Delete', BUP_LANG_CODE); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php if(!empty($logs[$id]['content'])):?>
                                    <span class="bupShowLogHistory" data-log="<?php echo nl2br($logs[$id]['content'])?>"><?php _e('Show Backup Log', BUP_LANG_CODE) ?></span>
                                <?php else: ?>
                                    <b><?php _e('Log is clear.', BUP_LANG_CODE)?></b>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr/>
                <!--  Amazon files rendering end    -->

                <!--  DropBox files rendering start    -->
                    <?php
                    elseif($backupType == 'dropbox'):
                        $files = $type['dropbox'];
                        $encrypted = !empty($files['sql']['backupInfo']['encrypted']) ? $files['sql']['backupInfo']['encrypted'] : '';
                        ?>
                        <div class="backupBlock">
                            <p>
                                <?php _e('Backup to <b>DropBox</b> / ID', BUP_LANG_CODE)?> <b><?php echo $id; ?></b><?php echo $backupTimeInfo ?>
                            </p>
                            <div id="bupDropboxAlerts-<?php echo $id; ?>"></div>
                            <div id="bupControl-<?php echo $id?>">
                                <table>
                                    <tbody>
                                    <?php foreach($files as $type=>$file):?>
                                        <tr id="row-<?php echo $type.'-'.$id; ?>">
                                            <td>
                                                <?php echo ($type == 'sql') ? __('Database', BUP_LANG_CODE) : __('Filesystem', BUP_LANG_CODE); ?>
                                            </td>
                                            <td>
                                                <button
                                                    class="button button-primary button-small bupDropboxRestore"
                                                    data-filename="<?php echo basename($file['path']); ?>"
                                                    data-row-id="<?php echo $id; ?>"
                                                    >
                                                    <?php _e('Restore', BUP_LANG_CODE); ?>
                                                </button>
                                                <button
                                                    class="button button-primary button-small bupDropboxDelete"
                                                    data-filepath="<?php echo $file['path']; ?>"
                                                    data-row-id="<?php echo $id; ?>"
                                                    data-file-type="<?php echo $type; ?>"
                                                    >
                                                    <?php _e('Delete', BUP_LANG_CODE); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php if(!empty($logs[$id]['content'])):?>
                                    <span class="bupShowLogHistory" data-log="<?php echo nl2br($logs[$id]['content'])?>"><?php _e('Show Backup Log', BUP_LANG_CODE)?></span>
                                <?php else: ?>
                                    <b><?php _e('Log is clear.', BUP_LANG_CODE) ?></b>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr/>
                        <!--  DropBox files rendering end    -->

                        <!--  Backups from remote server(pro version) files rendering start    -->
                        <?php elseif(false !== strpos($backupType, 'bupRemoteServerType-')):
                            echo dispatcherBup::applyFilters('getRemoteBackupsFileListContent', null, $type, $backupType, $backupTimeInfo, $id, $logs[$id]['content']);
                        ?>
                        <!--  Backups from remote server(pro version) files rendering end    -->

                    <?php endif; ?>
                <?php endforeach;
                else:?>
                    <h3><?php _e('Backups don\'t exist!', BUP_LANG_CODE) ?></h3>
                <?php endif;
                ?>
            </div>


            <div class="bupRestoreSettingBlock" style="width: 45%; display: inline-block; position: fixed; padding-left: 20px">
                <div id="bupRestorePresetsMsg"></div>
                <table class="bup-form-table-restore-presets form-table" style="width: 45% !important;">
                    <tr>
                        <th colspan="3"><?php _e('Restore Presets:', BUP_LANG_CODE); ?></th>
                    </tr>
                    <tr>
                        <th class="col-w-30perc"><?php _e('Safe Update', BUP_LANG_CODE); ?></th>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('If the ckeckbox is set up, the database backup will be performed. This will let the database backup work in the transaction mode, i.e. should there occur any failure during the data base recovery, no data from the data-base backup will be transferred to the data-base. The data-base backup recovery will occur if and only there were no failures during the process. If the ckeckbox is not set up the data-base backup will be performed without transaction mode.', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc"><?php echo htmlBup::checkbox('opt_values[safe_update]', array(
                                'attrs'   => 'class="bupCheckbox bupSaveRestoreSetting" data-setting-key="safe_update"',
                                'value'   => '1',
                                'checked' => frameBup::_()->getModule('options')->get('safe_update') == 1 ? 'checked' : '',
                            )); ?>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-w-30perc"><?php _e('Force Update', BUP_LANG_CODE); ?></th>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('When backup is performed, the labels are usually put at the beginning of the file dump, such as: WordPress version for the backup; WordPress data-base version for the backup; the plugin version for the backup. At recovering, if the force has been off, the backup will not be performed, because it will constantly pop up with the message, that the version is incorrect (the version of WordPress, the version of WordPress data-base or the plugin version). If the force has been on, there will be no such system check and the recovery will be performed.', BUP_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc"><?php echo htmlBup::checkbox('opt_values[force_update]', array(
                                'attrs'   => 'class="bupCheckbox bupSaveRestoreSetting" data-setting-key="force_update"',
                                'value'   => '1',
                                'checked' => frameBup::_()->getModule('options')->get('force_update') == 1 ? 'checked' : '',
                            )); ?>
                        </td>
                    </tr>
                </table>
            </div>
            <!-- Log modal window start  -->
            <div id="bupShowLogDlg" title="<?php _e('Backup Log:', BUP_LANG_CODE); ?>">
                <p id="bupLogText" style="margin-top: 0"></p>
            </div>
            <!-- Log modal window end  -->

            <!-- Migrate promo modal window start  -->
            <div id="bupShowMigratePromoDlg" title="<?php _e('Get PRO Verion!', BUP_LANG_CODE); ?>" style="display: none">
                <p id="bupMigratePromoText" class="supsystic-plugin">
                    <?php _e('Please, be advised, that this option is available only in PRO version. You can', BUP_LANG_CODE)?>
                    <a class="button button-primary button-small" href="<?php echo frameBup::_()->getModule('promo_supsystic')->getMainLink();?>" target="_blank"><?php _e('Get PRO', BUP_LANG_CODE)?></a>
                </p>
            </div>
            <!-- Migrate promo modal window end  -->
            <?php echo dispatcherBup::applyFilters('getInputsForReplaceMigrationData', '')?>

            <?php echo dispatcherBup::applyFilters('getModalWindowForSecretKeyEncryptDB', '');?>
        </div>
    </div>
</section>