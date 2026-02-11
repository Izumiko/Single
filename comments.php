<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php

/**
 * 自定义评论输出回调
 */
function threadedComments(\Widget\Comments\Archive $comments,\Typecho\Config $options): void
{
    $commentLevelClass = $comments->levels > 0 ? " comment-child" : "";

    // 缓存作者名称，用于回复楼层显示 @作者
    Single::$authorCache[$comments->coid] = $comments->author;

    // 处理时间显示
    $formattedTime = Single::tran_time($comments->created);
    ?>
    <div class="comment-single<?= $commentLevelClass ?>" id="<?php $comments->theId(); ?>">
        <?php
        // QQ 头像逻辑优化
        $mail = strtolower($comments->mail ?? '');
        if ($mail && preg_match('/^\d{4,13}@qq\.com$/', $mail)) {
            // 提取 QQ 号
            $qqNumber = str_replace('@qq.com', '', $mail);
            echo '<img class="avatar" src="https://q.qlogo.cn/g?b=qq&nk=' . $qqNumber . '&s=100" alt="QQ Avatar"/>';
        } else {
            // 默认 Gravatar，使用 robohash 作为后备
            $comments->gravatar('150', 'robohash');
        }
        ?>
        <div class="comment-meta">
        <span class="comment-author">
            <?php if ($comments->url): ?>
                <a href="<?php $comments->url(); ?>" rel="external nofollow" target="_blank"><?php $comments->author(false) ?></a>
            <?php else: ?>
                <?php $comments->author() ?>
            <?php endif; ?>
        </span>
            <time class="comment-time"><?= $formattedTime ?></time>
            <span class="comment-reply">
            <?php $comments->reply('<i class="fa fa-reply" title="回复"></i>'); ?>
        </span>
        </div>
        <div class="comment-content">
            <?php
            // 评论审核状态提示
            if ('waiting' === $comments->status) {
                echo '<em class="waiting">(' . $options->commentStatus . ')</em> ';
            }

            // 回复楼层 @ 逻辑
            if ($comments->parent) {
                $parentAuthor = Single::$authorCache[$comments->parent] ?? 'Unknown';
                echo '<a href="#comment-' . $comments->parent . '">@' . $parentAuthor . '</a> ';
            }

            // 输出内容
            $content = preg_replace('#</?[p][^>]*>#', '', $comments->content);
            echo '<p>' . $content . '</p>';
            ?>
        </div>
    </div>

    <?php if ($comments->children) {
        echo '<div class="comment-children">';
        $comments->threadedComments();
        echo '</div>';
    } ?>

<?php } ?>

<section class="post-comments" id="comments">
    <h3><?php $this->commentsNum(_t('没有评论'), _t('只有一条评论 (QwQ)'), _t('已有 %d 条评论')); ?></h3>

    <?php $comments = $this->comments(); ?>

    <?php if ($this->allow('comment')): ?>
        <div class="comment-form" id="<?php $this->respondId(); ?>">
        <span class="cancel-comment-reply">
            <?php $comments->cancelReply(); ?>
        </span>

            <form method="post" action="<?php $this->commentUrl(); ?>">
                <?php if ($this->user->hasLogin()): ?>
                    <fieldset>
                        <p>欢迎回来，<a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>！不是你？<a href="<?php $this->options->logoutUrl(); ?>">登出</a></p>
                        <textarea rows="2" name="text" id="textarea" placeholder="快来评论支持吧 (*≧ω≦)ﾉ" title="如发布虚假信息或广告，将无法通过审核" required><?php $this->remember('text'); ?></textarea>
                        <button type="submit" class="btn">写好了~</button>
                    </fieldset>
                <?php else: ?>
                    <div class="row">
                        <fieldset class="col-m-6">
                            <input type="text" name="author" placeholder="昵称 *：" value="<?php $this->remember('author'); ?>" required>
                            <input type="email" name="mail" placeholder="电邮 *：" value="<?php $this->remember('mail'); ?>" <?= $this->options->commentsRequireMail ? 'required' : '' ?>>
                            <input type="url" name="url" placeholder="https://" value="<?php $this->remember('url'); ?>" <?= $this->options->commentsRequireURL ? 'required' : '' ?>>
                        </fieldset>
                        <fieldset class="col-m-6">
                            <textarea rows="3" name="text" id="textarea" placeholder="快来评论吧 (*≧ω≦)ﾉ" required><?php $this->remember('text'); ?></textarea>
                            <button type="submit" class="btn">写好了~</button>
                        </fieldset>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    <?php else: ?>
        <p>博主关闭了评论...</p>
    <?php endif; ?>

    <?php if ($comments->have()): ?>
        <?php $comments->listComments([
                'commentStatus' => _t('你的评论正等待审核'),
                'before'        => '<div class="comment-list">',
                'after'         => '</div>'
        ]); ?>

        <?php $comments->pageNav('&laquo;', '&raquo;', 3, "...", ['wrapTag' => 'div', 'itemTag' => 'span']); ?>
    <?php endif; ?>

</section>