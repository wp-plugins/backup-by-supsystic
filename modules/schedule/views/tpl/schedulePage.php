<section>
    <div class="supsystic-item supsystic-panel">
        <script type="text/javascript">
        // <!--
        jQuery(document).ready(function(){
            postboxes.add_postbox_toggles(pagenow);
        });
        // -->
        </script>
        <form id="bupAdminScheduleForm">
            <div class="bupWrap">
                <div id="dashboard-widgets" class="metabox-holder">
                    <div id="postbox-container-1" class="postbox-container" style="width: 100%;">
                        <div id="normal-sortables" class="meta-box-sortables ui-sortable">

                                  <div class="postbox bupAdminScheduleRow" style="display: block;">
                                      <div class="handlediv" title="<?php langBup::_e( 'Click to toggle' )?>"><br></div>
                                      <h3 class="hndle"><?php langBup::_e( 'Schedule:' ); ?></h3>
                                      <div class="inside">

                                        <div>
                                        <?php
                                            @ini_set('max_execution_time',0);
                                            $ts = @ini_get('max_execution_time');
                                            if ($ts != 0) {
                                                echo langBup::_e('<span class="bupWarningRed">Warning:</span> The plugin can not increase the standard work time of the script, please contact your hosting provider.<br />
        Detailed description <a class="bupBlueLink" target="_blank" href="http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time">here</a><br />Current max_execution_time = '.$ts);
                                            }
                                        ?>
                                        </div>

                                        <div class="everyScheduleBup">

                                          <table>
                                               <tr>
                                                  <td width="45">enable</td>
                                                  <td><?php echo htmlBup::checkbox('bupEnableShedule', array('attrs'=>'class="bupScheduleEnableCheckbox"', 'value' => 1, 'checked' => frameBup::_()->getModule('options')->get('sch_enable') ? 'checked' : '' )); ?></td>
                                              </tr>
                                          </table>

                                        <fieldset class="bupScheluleFieldset"><legend><strong>Periodicity</strong></legend>
                                          <div class="paddingSchedule">
                                            <table>
                                            <tr>
                                                <td width="120">every hour</td>
                                                <td><?php echo htmlBup::checkbox('sch_values[sch_every_hour]', array('attrs'=>'class="bupScheduleCheckbox"', 'value' => 1, 'checked' => $this->getModel()->everyChecked('sch_every_hour') )); ?></td>
                                            </tr>
                                            <tr>
                                                <td>every day</td>
                                                <td><?php echo htmlBup::checkbox('sch_values[sch_every_day]', array('attrs'=>'class="bupScheduleCheckbox"', 'value' => 1, 'checked' => $this->getModel()->everyChecked('sch_every_day'))); ?></td>
                                            </tr>
                                            <tr>
                                                <td>every day twice</td>
                                                <td><?php echo htmlBup::checkbox('sch_values[sch_every_day_twice]', array('attrs'=>'class="bupScheduleCheckbox"', 'value' => 1, 'checked' => $this->getModel()->everyChecked('sch_every_day_twice'))); ?></td>
                                            </tr>
                                            <tr>
                                                <td>every week</td>
                                                <td><?php echo htmlBup::checkbox('sch_values[sch_every_week]', array('attrs'=>'class="bupScheduleCheckbox"', 'value' => 1, 'checked' => $this->getModel()->everyChecked('sch_every_week'))); ?></td>
                                            </tr>
                                            <tr>
                                                <td>every month</td>
                                                <td><?php echo htmlBup::checkbox('sch_values[sch_every_month]', array('attrs'=>'class="bupScheduleCheckbox"', 'value' => 1, 'checked' => $this->getModel()->everyChecked('sch_every_month'))); ?></td>
                                            </tr>
                                            </table>
                                          </div>
                                        </fieldset>

                                        <fieldset class="bupScheluleFieldset"><legend><strong>Destination</strong></legend>
                                          <div class="paddingSchedule">
                                            <table>
                                            <?php //foreach($this->destination as $key=>$value){ ?>
                                           <!-- <tr>
                                                <td width="120"><?php echo $key; ?></td>
                                                <td><?php echo $value; ?></td>
                                            </tr>-->
                                            <?php //} ?>

                                            <tr>
                                                <td width="120">
                                                    <?php echo htmlBup::radiobutton('sch_dest', array(
                                                        'value' => 'ftp',
                                                        'checked' => ('ftp' === frameBup::_()->getModule('options')->get('sch_dest') ? 'checked' : ''),
                                                    )); langBup::_e('FTP'); ?>
                                                </td>

                                                <?php $backup = frameBup::_()->getModule('backup')->getController()->getModel('backup'); ?>
                                                <?php foreach ($backup->getDestinationHandlers() as $handler => $callback): ?>
                                                    <td width="120">
                                                        <?php echo htmlBup::radiobutton('sch_dest', array(
                                                            'value' => $handler,
                                                            'checked' => ($handler === frameBup::_()->getModule('options')->get('sch_dest') ? 'checked' : ''),
                                                        )); echo ucfirst($handler); ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                            </table>
                                          </div>
                                        </fieldset>

                                        </div>

                                      </div>
                                  </div>
                        </div>
                    </div>
                    <div>
                        <?php echo htmlBup::hidden('reqType', array('value' => 'ajax'))?>
                        <?php echo htmlBup::hidden('page', array('value' => 'schedule'))?>
                        <?php echo htmlBup::hidden('action', array('value' => 'saveGroupEvery'))?>
                        <button class="button button-primary button-large">
                            <i class="fa fa-fw fa-save"></i><?php echo langBup::_(' Save All Changes')?>
                        </button>
                    </div>
                    <div id="bupAdminScheduleMsg"></div>
                </div>
            </div>
        </form>
    </div>
</section>