<section>
    <div class="supsystic-item supsystic-panel">
        <div id="bupLogWrapper">
            <table id="bupAdminStorageTable" style="width: 100%;">
                <?php foreach ($files as $file): ?>
                <tr class="bupTblRow" style="display: table-row">
                    <td class="name">
                        <div class="backupBlock">

                            <fieldset style="padding-right: 15px;">
                                <legend style="text-align: right;">Backup log #<?php echo $file['backup_id']; ?></legend>
        <!--						<div align="left" id="MSG_EL_ID_--><?php //isset($id) ? echo $id : ''; ?><!--"></div>-->
                                <a href="#" class="bupToggleLog" onclick="return false;">Read Log File</a>
                                <textarea readonly="readonly" style="display: none; width: 70%; height: <?php echo $file['lines']*12; ?>px"><?php echo str_replace('=', ':', $file['content']); ?></textarea>
                            </fieldset>

                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</section>