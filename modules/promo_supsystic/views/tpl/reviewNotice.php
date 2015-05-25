<style>
    span.toeLikeLink {
        cursor: pointer;
        color: #0073aa;
    }
    span.toeLikeLink:hover {
        color:#00a0d2
    }
</style>
<script>
    jQuery(document).ready(function($) {
        jQuery('.bupSendStatistic').on('click', function ($this) {
            var statisticCode = jQuery($this.currentTarget).data('statistic-code');
            jQuery.sendFormBup({
                data: {
                    page: 'promo_supsystic',
                    action: 'sendStatistic',
                    reqType: 'ajax',
                    statisticCode: statisticCode
                },
                onSuccess: function(responce) {
                    if(!responce.error)
                        document.location.reload();
                }
            });
        });
    });
</script>
<div class="updated">
    <p>
        Hey, I noticed you just use Backup by Supsystic over a week – that’s awesome!<br/>
        Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.
    </p>
    <ul style="list-style: circle; padding-left: 30px">
        <li><a href="//wordpress.org/support/view/plugin-reviews/backup-by-supsystic?rate=5#postform" target="_blank" class="bupSendStatistic" data-statistic-code="will_leave_feedback">Ok, you deserve it</a></li>
        <li><span class="toeLikeLink bupSendStatistic" data-statistic-code="maybe_later_leave_feedback">Nope, maybe later</span></li>
        <li><span class="toeLikeLink bupSendStatistic" data-statistic-code="leaved_feedback">I already did</span></li>
    </ul>
</div>