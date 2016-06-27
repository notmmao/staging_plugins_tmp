<div class="container-fluid">
    <!-- Order footer -->
    <div class="row">
        <div class="col-md-2">
            Upload Link: 
            <a href="<?php echo PROOF_DOMAIN_NAME; ?>/upload/10<?php echo $post->ID ?>" target="_blank">
                <?php echo PROOF_DOMAIN_NAME; ?>/upload/10<?php echo $post->ID ?>
            </a>
            <br><br>
            <a href="<?php echo PROOF_DOMAIN_NAME; ?>/10<?php echo $post->ID ?>/preview/">File Previews</a>
            &nbsp;&nbsp;
            <a class="pseudo history-view">Order history</a>
            <div class="history-list" style="">
                <table class="history">
                <?php foreach ($orderHistory as $historyEntry): ?>
                <tr style="">
                    <td style="color:#999;"><?php echo $historyEntry['created']; ?></td>
                    <td style="color:#333;"><?php echo $historyEntry['status'] ?></td>
                </tr>
                <?php endforeach; ?>
                </table>
            </div>
        </div>
        <div class="col-md-3">
            <span >Set email</a> <br>
            <div>
                <input data-url="<?php echo PROOF_DOMAIN_NAME; ?>/api/set-alt-email/" <?php if ($orderAltEmail) { echo "value='{$orderAltEmail}'"; } ?> data-order="10<?php echo $post->ID ?>" class="custom-email" style="width: 260px; font-size: 0.85em; height: 22px !important; padding: 5px 1px 2px 6px;" type="text" />
                <span style="color:#14BF00; font-size: 1.3em; display:none;" class="check-icon icon">✔</span>
                <span style="color:#BF0000; font-size: 1.3em; display:none;" class="input-group-addon cross-icon icon">✘</span>
            </div>
        </div>
        <div class="col-md-2">
            <button id="unlockform_<?php echo ($post->ID + 100000) ?>" data-action="<?php echo PROOF_DOMAIN_NAME; ?>/api/allowuploads/<?php echo ($post->ID + 100000) ?>" data-order="<?php echo $post->ID ?>" type="submit" class="btn btn-small btn-redirect proof-allow-uploads">Allow uploads</button>
            <span class="allow-uploads loader-box"></span>
        </div>
        <div class="col-md-2">
            <div id="approvalform_<?php echo ($post->ID + 100000) ?>" data-action="<?php echo PROOF_DOMAIN_NAME; ?>/api/approvalmail/<?php echo ($post->ID + 100000) ?>/" data-order="<?php echo $post->ID ?>">
                <div class="pseudo-input nomail-val" data-value="0"></div>
                <button class="btn btn-primary disabled btn-redirect proof-approval-mail">Send Approval Mail</button> &nbsp;<span class="approval-mail loader-box"></span>
                <br>
                <button style="margin-left: -2px;" class="btn btn-link btn-redirect disabled nomail proof-refresh">Refresh (no mail)</button>&nbsp;<span class="approval-nomail loader-box"><img src="<?php echo plugins_url( 'assets/images/ajax-loader.gif', __FILE__ ); ?>"></span>
                <br>
                <a style="margin-left: -2px;" class="btn btn-link disabled proof-issues" data-toggle="modal" href="#issues-modal">Artwork issues</a>
            </div>
            <br>
        </div>
    </div>
</div>