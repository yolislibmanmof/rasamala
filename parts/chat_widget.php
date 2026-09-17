<?php
# @Author: Ade Ismail Siregar <adeismailbox@gmail.com>
# @Based on: SLiMS Bulian 9.8 Default Template by Waris Agung Widodo <ido.alit@gmail.com>
# @Date: 2026-08-06T07:43:00+07:00
# @Filename: chat_widget.php
?>

<?php if ($sysconf['chat_system']['enabled'] && $sysconf['chat_system']['opac']) : ?>
    <div id="show-pchat2" class="shadow rounded floating-chat-trigger">
        <button title="Chat" class="btn btn-primary"><i class="fas fa-comments me-2" aria-hidden="true"></i><?= __('Chat'); ?></button>
    </div>
<?php endif; ?>

<?php
// Chat Engine
if (defined('LIB') && is_file(LIB . 'contents/chat.php')) {
    include LIB . 'contents/chat.php';
}
