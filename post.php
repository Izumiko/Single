<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

    <main>
        <div class="wrap min">
            <section class="post-title">
                <h2><?php $this->title(); ?></h2>
                <?php if ($this->authorId == $this->user->uid): ?>
                    <a class="edit-link" href="<?php $this->options->adminUrl('write-post.php?cid=' . $this->cid); ?>" target="_blank">编辑</a>
                <?php endif; ?>

                <div class="post-meta">
                    <time class="date"><?php $this->date(); ?></time>
                    <?php
                    $postMeta = $this->options->post_meta ?? [];
                    ?>
                    <?php if (in_array('show_category', $postMeta)): ?>
                        <span class="category"><?php $this->category('，'); ?></span>
                    <?php endif; ?>
                    <?php if (in_array('show_comments', $postMeta)): ?>
                        <span class="comments"><?php $this->commentsNum(); ?></span>
                    <?php endif; ?>
                </div>
            </section>

            <article class="post-content">
                <?php
                // 15552000 秒 = 180 天
                $timeDiff = time() - $this->modified;
                if ($timeDiff >= 15552000):
                    ?>
                    <blockquote>这篇文章上次修改于 <?= ceil($timeDiff / 86400) ?> 天前，可能其部分内容已经发生变化，如有疑问可询问作者。</blockquote>
                <?php endif; ?>

                <?php $this->content(); ?>
            </article>

            <section class="post-near">
                <ul>
                    <li>上一篇: <?php $this->thePrev('%s', '看完啦 (つд⊂)'); ?></li>
                    <li>下一篇: <?php $this->theNext('%s', '看完啦 (つд⊂)'); ?></li>
                </ul>
            </section>

            <?php if (!empty($this->tags)): ?>
                <section class="post-tags">
                    <?php $this->tags('', true, '暂无'); ?>
                </section>
            <?php endif; ?>

            <?php if ($this->options->author_text): ?>
                <section class="post-author">
                    <figure class="author-avatar">
                        <?php $this->author->gravatar(200); ?>
                    </figure>
                    <div class="author-info">
                        <h4><?php $this->author(); ?></h4>
                        <p><?= $this->options->author_text() ?></p>
                    </div>
                </section>
            <?php endif; ?>

            <?php $this->need('comments.php'); ?>
        </div>
    </main>

<?php $this->need('footer.php'); ?>