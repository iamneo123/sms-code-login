<div class="phone-login__form-container">
    <? if (!empty($arResult['DEBUG']['code'])) { ?>
        <div>
            <?=$arResult['DEBUG']['code'];?>
        </div>
    <? } ?>
    <div class="phone-login__form-title">Введите код из sms</div>
    <form action="" name="login_form" class="phone-login__form">
        <input type="text" name="code">
        <? if (!empty($arResult['ERROR']['MESSAGE'])) { ?>
            <div class="phone-login__error-message"><?=$arResult['ERROR']['MESSAGE'];?></div>
        <? } ?>
        <button type="submit" data-action="submit">Отправить</button>
        <button
            data-code-resend-time="<?=$arResult['CODE_RESEND_TIME'];?>"
            data-action="open_form"
            <?if ($arResult['CODE_RESEND_TIME'] > 0){?>disabled<?}?>
        ><span>Отправить новый код</span></button>
    </form>
</div>