import '../css/main.css';
import '../css/dark.css';

$(function() {
    var $chainInput = $("#chain-address-input");

    if ($chainInput.length > 0) {
        function isValidAddress(address) {
            return address && address.length == 42 && address.startsWith('0x');
        }

        let autoSubmit = undefined

        $chainInput.bind('paste', function (e) {
            if (autoSubmit) {
                clearTimeout(autoSubmit)
            }

            var pastedData = e.originalEvent.clipboardData.getData('text');

            if (isValidAddress(pastedData)) {
                var me = this;

                autoSubmit = setTimeout(function () {
                    $(me).closest("form").submit()
                }, 500)
            }
        })

        $chainInput.bind('keypress', function (e) {
            if (autoSubmit) {
                clearTimeout(autoSubmit)
            }
        })

        let addressValidator = function (input) {
            var me = this;

            if(isValidAddress(input.val())) {
                input.addClass('is-valid');
            } else {
                input.removeClass('is-valid');
            }
        };

        // trigger init
        addressValidator($chainInput);

        $chainInput.bind('input load', function() {
            addressValidator($(this));
        })

        var $dappCLick = $chainInput.closest('form').find('.dapp-wallet')

        $dappCLick.bind('click', async function(e) {
            e.preventDefault();

            const provider = await detectEthereumProvider();
            const accounts = await provider.request({method: 'eth_requestAccounts'});

            if (accounts && accounts.length > 0) {
                $chainInput.val(accounts[0]);
                $chainInput.closest('form').submit();
            } else {
                alert('No wallet address found')
            }
        });

        setTimeout(async function() {
            const provider = await detectEthereumProvider({
                silent: true,
            });
            if (provider) {
                $chainInput.closest('form').find('.dapp-wallet').removeClass('d-none')
            }
        }, 50)
    }

    var $darkMode = $(".theme-toggle");

    if ($darkMode.length > 0) {
        $darkMode.on("click", function(e) {
            e.preventDefault();

            let theme = $(document.body).attr('data-theme');

            let isDark = theme === 'dark'
            let newTheme = isDark ? 'light' : 'dark';

            $(document.body).attr('data-theme', newTheme);
            $.post($(document.body).attr('data-theme-toggle'), {theme: newTheme});
        });
    }

    var exampleModal = document.getElementById('exampleModal')
    if (exampleModal) {
        exampleModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget
            var content = $(exampleModal).find('.modal-body .content');
            content.html('')

            let title = $(button).attr('data-bs-title');
            $(exampleModal).find('.title').html(title);

            $(exampleModal).find('.ajax-spinner').removeClass('d-none');

            let href = $(button).attr('href');

            $.ajax({
                url: href,
            }).done(function( data ) {
                content.html(data)
            }).always(function() {
                $(exampleModal).find('.ajax-spinner').addClass('d-none');
            });
        })
    }


    var $charts = $(".chart-js-token-price");

    if ($charts.length > 0) {
        $charts.each(function() {
            let $1 = $(this);
            new Chart($1, {
                type: 'line',
                data: {
                    labels: $1.data('label'),
                    datasets: [{
                        label: $1.data('title'),
                        data: $1.data('data'),
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.2
                    }]
                }
            });
        });
    }
});