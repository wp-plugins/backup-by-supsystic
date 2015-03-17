<section>
    <div class="supsystic-item supsystic-panel">
        <?php
        $not_piad = utilsBup::checkPRO() ? '' : 'bupNotPaid';
        ?>
        <form class="cspNiceStyle" id="bupAdminMainForm">
            <div class="description">
                To restore website backup, be sure that all files and folders in the core directory have writing permissions. Backup restoration can rewrite some of them.
            </div>
            <div id="BUP_MESS_MAIN"></div>
            <table width="100%">
                <tr class="cspAdminOptionRow cspTblRow">
                    <td style="padding-left: 0">
                        <?php echo htmlBup::hidden('reqType', array('value' => 'ajax'))?>
                        <?php echo htmlBup::hidden('page', array('value' => 'backup'))?>
                        <?php echo htmlBup::hidden('action', array('value' => 'createAction'))?>
                        <?php $attrs = array('class="button button-primary button-large" style="margin-right: 10px;"'); $style = ''; ?>
                        <?php if (defined('BUP_LOCK_FIELD') && get_option(BUP_LOCK_FIELD) == 1): ?>
                            <?php $attrs[] = 'style="display:none;"'; ?>
                        <?php else: ?>
                            <?php $style = 'display:none;'; ?>
                        <?php endif; ?>

                        <?php echo htmlBup::submit('backupnow', array('value' => langBup::_('Where to Backup:'), 'attrs' => implode(' ', $attrs))); ?>
                        <div id="bupInfo">
                            <p style="font-size: 15px;">Available space: <br/>
                                <?php if (frameBup::_()->getModule('warehouse')->getWarehouseStatus()): ?>
                                    <?php echo frameBup::_()->humanSize(
                                        disk_free_space(frameBup::_()->getModule('warehouse')->getPath())
                                    );
                                    ?>
                                <?php else: ?>
                                    <span class="bupErrorMsg">
                                    <?php langBup::_e('An errors has been occured while initialize warehouse module.'); ?>
                                </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="main-progress-bar" style="display:none;">
                            <div class="progress-bar devblue shine">
                                <span style="width: 0%;"><b id="bupCompletePercent"></b></span>
                            </div>
                            <span class="bupShowLog bupShowLogDlg">Show Log</span>
                        </div>
                        <div id="BUP_SHOW_LOG" style="display: none;">
                            <p id="inProcessMessage" class="bupErrorMsg" style="<?php echo $style; ?>">
                                Backup already in process.
                            </p>
                    </td>
                </tr>
            </table>


        </form>

        <div id="resBox"></div>

        <div align="left">
            <div id="BUP_MESS_INFO"></div>
        </div>

        <?php echo $this->backupOptions ?>

        <!-- Log modal window start  -->
        <div id="bupShowLogDlg" title="Backup Log:">
            <div id="bupLogText"></div>
        </div>
        <!-- Log modal window end  -->

    </div>
</section>
