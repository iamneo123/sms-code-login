<div class="phone-login__form-container">
    <div class="phone-login__form-title">Введите ваш номер телефона</div>
    <form action="" name="login_form" class="phone-login__form">
        <input type="text" name="phone">
        <? if (!empty($arResult['ERROR']['MESSAGE'])) { ?>
            <div class="phone-login__error-message"><?=$arResult['ERROR']['MESSAGE'];?></div>
        <? } ?>
        <button type="submit" data-action="submit">Отправить код</button>
    </form>
</div>