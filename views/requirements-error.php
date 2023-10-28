<?php
defined( 'ABSPATH' ) || exit;

/* @var array $errors */
?>
<div class="error">
    <p><?php echo esc_html__( 'Chat with GPT', 'chat-with-gpt' ) . ' ' . esc_html__( 'error: Your environment does not meet all of the system requirements listed below.', 'chat-with-gpt' ) ?> </p>

    <ul class="ul-disc">
		<?php foreach ( $errors as $error ): ?>
            <li>
                <strong><?php echo esc_html($error) ?></strong>
            </li>
		<?php endforeach; ?>
    </ul>

    <p><?php esc_html_e( 'If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to the', 'chat-with-gpt' ) ?>
        <a href="https://wordpress.org/documentation/article/updating-wordpress/">Codex</a>.</p>
</div>
