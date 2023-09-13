<?php
defined( 'ABSPATH' ) || exit;

/* @var array $settings */
/* @var array $models */
/* @var array $errors */

?>
<div class="qcg-chat-wrap">
    <!----- Start Closed Chat ----->
    <div class="qbChat-gpt qbChat-gpt-closed" id="qbChat">
        <header class="qbChat-gpt__header">
            <div>
                <img alt="chat" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/chat.svg"/>
                <h2><?php _e( 'Ask ChatGPT', 'chat-with-gpt' ) ?></h2>
            </div>
        </header>
    </div>
    <!----- End Closed Chat ----->

    <!----- Start Chat ----->
    <div class="qbChat-gpt qbChat-gpt-opened" id="qbChatOpened">
        <div class="qbChat-gpt__close" id="qbChatClose">
            <button>
                <img alt="close" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/close.svg"/>
            </button>
        </div>
        <header class="qbChat-gpt__header">
            <div>
                <img alt="chat" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/chat.svg"/>
                <h2><?php _e( 'Ask ChatGPT...', 'chat-with-gpt' ) ?></h2>
            </div>
            <img alt="logo" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/logo.svg"/>
        </header>

        <!----- Start Enter Email section ----->
        <div class="qbChat-gpt__emailForm" id="qbChatEmail">
            <div class="qbChat-gpt__emailForm-inner">
                <header class="qbChat-gpt__emailForm-header">
                    <h3><?php _e( 'Daily Request Limit Reached', 'chat-with-gpt' ) ?></h3>
                    <p><?php _e( "You've reached the daily limit for requests. To connect with us please enter your email below.", 'chat-with-gpt' ) ?></p>
                </header>
                <div class="qbChat-gpt__emailForm-form">
                    <form>
                        <div class="qbChat-gpt__emailForm-input-container">
                            <img alt="mail" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/mail.svg"/>
                            <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    placeholder="example@email.com"
                                    required
                            />
                        </div>

                        <span class="qbChat-gpt__emailForm-errorMsg"></span>

                        <button type="submit"><?php _e( 'Send request', 'chat-with-gpt' ) ?></button>
                    </form>
                </div>
            </div>
        </div>
        <!----- End Enter Email section ----->

        <!----- Start Chat Content ----->
        <div id="qcg-answer" class="qbChat-gpt__content" style="background: url('<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/pattern.png') #edf1f5;">
            <!----- Start GPT message ----->
            <div class="qbChat-gpt__msg">
                <div class="qbChat-gpt__msg-avatar">
                    <img alt="ava" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/gpt-avatar.svg"/>
                </div>
                <div class="qbChat-gpt__msg-content">
                    <div class="qbChat-gpt__msg-author"><?php _e( 'ChatGPT', 'chat-with-gpt' ) ?></div>
                    <div class="qbChat-gpt__msg-block">
                        <div class="qbChat-gpt__msg-text">
                            <p><?php _e( 'Hello!<br/>How can I assist you?', 'chat-with-gpt' ) ?></p>
                        </div>

                        <div class="qbChat-gpt__msg-time">
                            <span><?php echo date( 'H:i' ) ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <!----- End GPT message ----->
        </div>
        <!----- End Chat Content ----->

        <footer class="qbChat-gpt__footer">
            <div class="qbChat-gpt__footer-sendSection">
            <textarea
                    placeholder="<?php _e( 'Write your message...', 'chat-with-gpt' ) ?>"
                    name="qcg-prompt"
                    id="qcg-prompt"
                    cols="30"
                    rows="3"
                    onblur="qcgBlurHandler()"
            ></textarea>

                <div class="qbChat-gpt__footer_btn">
                    <button id="qcg-prompt-ask" disabled>
						<?php _e( 'Send', 'chat-with-gpt' ) ?>
                    </button>
                    <span>shift+enter</span>
                </div>
            </div>
        </footer>
    </div>
    <!----- End Chat ----->

    <div id="qcg-chat-template" style="display: none;">
        <!----- Start GPT message ----->
        <div class="qbChat-gpt__msg qbChat-gpt__msg-resp">
            <div class="qbChat-gpt__msg-avatar">
                <img alt="ava" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/gpt-avatar.svg"/>
            </div>
            <div class="qbChat-gpt__msg-content">
                <div class="qbChat-gpt__msg-author"><?php _e( 'ChatGPT', 'chat-with-gpt' ) ?></div>
                <div class="qbChat-gpt__msg-block">
                    <div class="qbChat-gpt__msg-text">
                        <p></p>
                    </div>

                    <div class="qbChat-gpt__msg-time">
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
        <!----- End GPT message ----->

        <!----- Start USER message ----->
        <div class="qbChat-gpt__msg qbChat-gpt__msg-user">
            <div class="qbChat-gpt__msg-content">
                <div class="qbChat-gpt__msg-block">
                    <div class="qbChat-gpt__msg-text">
                        <p></p>
                    </div>
                    <div class="qbChat-gpt__msg-time">
                        <span></span>
                        <img alt="seen" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/seen.svg"/>
                    </div>
                </div>
            </div>
        </div>
        <!----- End USER message ----->

        <!----- Start Waiting message ----->
        <div class="qbChat-gpt__msg qbChat-gpt__msg-working">
            <div class="qbChat-gpt__msg-avatar">
                <img alt="ava" src="<?php echo QCG_Common::get_plugin_root_path( 'url' ) ?>asset/images/gpt-avatar.svg"/>
            </div>
            <div class="qbChat-gpt__msg-content">
                <div class="qbChat-gpt__msg-author"><?php _e( 'ChatGPT', 'chat-with-gpt' ) ?></div>
                <div class="qbChat-gpt__msg-block">
                    <div class="qbChat-gpt__msg-text">
                        <p><?php _e( 'Working...', 'chat-with-gpt' ) ?></p>
                    </div>

                    <div class="qbChat-gpt__msg-time">
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
        <!----- End Waiting message ----->
    </div>
</div> <!-- .wrap -->