<section>
    <div class="supsystic-item supsystic-panel">
        <div id="bupBackupWrapper">
            <div id="bupAdminStorageTable" style="width: 100%;">
                <?php
                foreach ($backups as $id => $type):
                    $backupType = key($type);
                    $backupStartDateTime = $model->getBackupStartTimeFromLog($logs[$id]['content']);
                    $backupFinishDateTime = $model->getBackupFinishTimeFromLog($logs[$id]['content']);
                    if($backupType == 'ftp'):
                        $backup = $type['ftp'];
                    ?>
                    <div class="backupBlock">
                        <p>
                            Backup to <b>FTP</b> / ID <b><?php echo $id; ?></b><?php echo !empty($backupStartDateTime) ?' / Start: <b>' . $backupStartDateTime . '</b>' : ''?> / Finish: <b><?php echo (isset($backup['zip']) ? $backup['zip']['date'].' '.$backup['zip']['time'] : $backup['sql']['date'].' '.$backup['sql']['time'])?></b>
                        </p>
                        <div align="left" id="MSG_EL_ID_<?php echo $id; ?>"></div>

                        <div id="bupControl-<?php echo $id?>">
                            <!-- Hides "Send to" button if the PRO version isn't activated -->
                            <?php if (null !== frameBup::_()->getModule('license')): ?>
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
                                            <?php echo ($type == 'zip' ? 'Filesystem' : 'Database'); ?>
                                        </td>
                                        <td>
                                            <!-- restoreButton -->
                                            <button class="button button-primary button-small bupRestore" data-id="<?php echo $id; ?>" data-filename="<?php echo $data['name']; ?>" >
                                                <?php langBup::_e('Restore'); ?>
                                            </button>
                                            <!-- /restoreButton -->

                                            <!-- downloadButton -->
                                            <button class="button button-primary button-small bupDownload" data-filename="<?php echo $data['name']; ?>">
                                                <?php langBup::_e('Download'); ?>
                                            </button>
                                            <!-- /downloadButton -->

                                            <!-- deleteButton -->
                                            <button class="button button-primary button-small bupDelete" data-id="<?php echo $id; ?>" data-filename="<?php echo $data['name']; ?>">
                                                <?php langBup::_e('Delete'); ?>
                                            </button>
                                            <!-- /deleteButton -->
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if(!empty($logs[$id]['content'])):?>
                                <span class="bupShowLogDlg" data-log="<?php echo nl2br($logs[$id]['content'])?>">Show Backup Log</span>
                            <?php else: ?>
                                <b>Log is clear.</b>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr/>

                    <?php
                    elseif($backupType == 'gdrive'):
                        $file = $type['gdrive'];
                        ?>
                        <div class="backupBlock">
                            <p>
                                Backup to <b>GoogleDrive</b> / ID <b><?php echo $id; ?></b><?php echo !empty($backupStartDateTime) ?' / Start: <b>' . $backupStartDateTime . '</b>' : ''?><?php echo !empty($backupFinishDateTime) ?' / Finish: <b>' . $backupFinishDateTime . '</b>' : ''?>
                            </p>
                            <div align="left" id="MSG_EL_ID_<?php echo $id; ?>" class="bupSuccessMsg"></div>
                            <div id="bupControl-<?php echo $id?>">
                                <?php if($file['labels']['trashed'] === false): ?>
                                    <table>
                                    <tbody>
                                    <tr id="<?php echo $id; ?>">
                                        <td>
                                            <img src="<?php echo $file['iconLink']; ?>" />
                                        </td>
                                        <td>
                                            <?php echo $file['title']; ?>
                                        </td>
                                        <td>
                                            <button data-row-id="<?php echo $id; ?>"
                                                    data-file-url="<?php echo $file['downloadUrl']; ?>"
                                                    data-file-name="<?php echo $file['title']; ?>"
                                                    class="button button-primary button-small bupGDriveRestore"
                                                >
                                                <?php langBup::_e('Restore'); ?>
                                            </button>
                                            <button data-row-id="<?php echo $id; ?>"
                                                    data-file-id="<?php echo $file['id']; ?>"
                                                    data-filename="<?php echo $file['title']; ?>"
                                                    class="button button-primary button-small bupGDriveDelete"
                                                >
                                                <?php langBup::_e('Delete'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                    </table>
                                <?php endif; ?>
                                <?php if(!empty($logs[$id]['content'])):?>
                                    <span class="bupShowLogDlg" data-log="<?php echo nl2br($logs[$id]['content'])?>">Show Backup Log</span>
                                <?php else: ?>
                                    <b>Log is clear.</b>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr/>

                    <?php
                    elseif($backupType == 'onedrive'):
                        $file = $type['onedrive'];
                        ?>
                        <div class="backupBlock">
                            <p>
                                Backup to <b>OneDrive</b> / ID <b><?php echo $id; ?></b><?php echo !empty($backupStartDateTime) ?' / Start: <b>' . $backupStartDateTime . '</b>' : ''?><?php echo !empty($backupFinishDateTime) ?' / Finish: <b>' . $backupFinishDateTime . '</b>' : ''?>
                            </p>
                            <div align="left" id="MSG_EL_ID_<?php echo $id; ?>" class="bupSuccessMsg"></div>
                            <div id="bupControl-<?php echo $id?>">
                                <table>
                                    <tbody>
                                        <?php if ($file->type === 'file'): ?>
                                            <tr id="backup-<?php echo $file->id; ?>" data-id="<?php echo $id; ?>" data-filename="<?php echo $file->name; ?>">
                                                <td>
                                                    <?php echo $file->name; ?>
                                                </td>
                                                <td>
                                                    <button data-row-id="backup-<?php echo $file->id; ?>"
                                                            data-file-id="<?php echo $file->id; ?>"
                                                            data-file-name="<?php echo $file->name; ?>"
                                                            class="button button-primary button-small bupRestoreOnedrive"
                                                        >
                                                        <?php langBup::_e('Restore'); ?>
                                                    </button>
                                                    <button
                                                        data-file-id="<?php echo $file->id; ?>"
                                                        class="button button-primary button-small onedriveDelete"
                                                        >
                                                        <?php langBup::_e('Delete'); ?>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>


                                <?php if(!empty($logs[$id]['content'])):?>
                                    <span class="bupShowLogDlg" data-log="<?php echo nl2br($logs[$id]['content'])?>">Show Backup Log</span>
                                <?php else: ?>
                                    <b>Log is clear.</b>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr/>


                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <!-- Log modal window start  -->
            <div id="bupShowLogDlg" title="Backup Log:">
                <p id="bupLogText"></p>
            </div>
            <!-- Log modal window end  -->
        </div>
    </div>
</section>