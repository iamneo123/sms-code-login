let PhoneLoginComponent = {
    init: () => {
        let openFormBtn = document.querySelector('button[data-action="open_form"]');
        if (openFormBtn) {
            openFormBtn.addEventListener('click', () => {
                let formData = new FormData();
                formData.append('action', 'openForm');
                PhoneLoginComponent.sendRequest(formData);
            });
        }
    },
    formInit: () => {
        let formBtns = document.querySelectorAll('form button[data-action]');
        formBtns.forEach((formBtn) => {
            formBtn.addEventListener('click', (e) => {
                e.preventDefault();
                let action = e.currentTarget.dataset.action;
                let formData = new FormData();

                switch (action) {
                    case 'open_form':
                        formData.append('action', 'openForm');
                        break;
                    case 'submit':
                        let form = e.currentTarget.closest('form');
                        formData = new FormData(form);
                        break;
                }

                PhoneLoginComponent.sendRequest(formData);
            });
        });
    },

    sendRequest: (formData) => {
        fetch('',{
            method: 'POST',
            body: formData,
        })
            .then(response => response.text())
            .then(text => {
                PhoneLoginComponent.closeForm();

                let form = document.createElement('div');
                form.classList.add('phone-login__container');
                form.dataset.action = 'close';
                form.innerHTML = text;

                let codeResendBtn = form.querySelector('[data-code-resend-time]');
                if (codeResendBtn)
                    CodeResendTimer.timeLeft = codeResendBtn.dataset.codeResendTime;

                document.body.append(form)

                CodeResendTimer.renderTimer();
                PhoneLoginComponent.closeInit();
                PhoneLoginComponent.formInit();

                if (text.includes('success'))
                    setTimeout(() => window.location.href = '', 1000);
            });
    },

    closeInit: () => {
        let closeBtns = document.querySelectorAll('[data-action="close"]');
        closeBtns.forEach((closeBtn) => {
            closeBtn.addEventListener('click', (e) => {
                if (e.currentTarget !== e.target)
                    return;

                PhoneLoginComponent.closeForm();
            });
        });
    },
    closeForm: () => {
        if (CodeResendTimer.id)
            clearInterval(CodeResendTimer.id);

        if (document.querySelector('.phone-login__container'))
            document.querySelector('.phone-login__container').remove();
    }
}

let CodeResendTimer = {
    timeLeft: 300,
    id: 0,
    update: () => {
        CodeResendTimer.timeLeft--;

        let minutes = Math.floor((CodeResendTimer.timeLeft / 60) % 60);
        let seconds = Math.floor(CodeResendTimer.timeLeft % 60);

        CodeResendTimer.minutesEl.innerText = String(minutes).padStart(2, '0');
        CodeResendTimer.secondsEl.innerText = String(seconds).padStart(2, '0');

        if (CodeResendTimer.timerEl)
            CodeResendTimer.timerEl.style.display = 'block';

        if (CodeResendTimer.timeLeft === 0) {
            clearInterval(CodeResendTimer.id);

            if (CodeResendTimer.timerEl)
                CodeResendTimer.timerEl.remove();

            CodeResendTimer.codeResendBtn.disabled = false;
        }
    },
    renderTimer: () => {
        CodeResendTimer.codeResendBtn = document.querySelector('button[data-code-resend-time]');
        if (CodeResendTimer.codeResendBtn) {
            if (CodeResendTimer.codeResendBtn.dataset.codeResendTime > 0) {
                CodeResendTimer.minutesEl = document.createElement('span');
                CodeResendTimer.secondsEl = document.createElement('span');
                CodeResendTimer.separatorEl = document.createElement('span');
                CodeResendTimer.separatorEl.innerText = ':';

                CodeResendTimer.timerEl = document.createElement('div');
                CodeResendTimer.timerEl.append(CodeResendTimer.minutesEl, CodeResendTimer.separatorEl, CodeResendTimer.secondsEl);
                CodeResendTimer.timerEl.style.display = 'none';

                CodeResendTimer.codeResendBtn.append(CodeResendTimer.timerEl);

                clearInterval(CodeResendTimer.id);
                CodeResendTimer.update();
                CodeResendTimer.id = setInterval(CodeResendTimer.update, 1000);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', PhoneLoginComponent.init);